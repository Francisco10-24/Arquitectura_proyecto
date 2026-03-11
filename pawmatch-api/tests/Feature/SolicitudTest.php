<?php

namespace Tests\Feature;

use App\Models\Mascota;
use App\Models\SolicitudAdopcion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SolicitudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake(); // Evitar envío real de emails en tests
    }

    public function test_usuario_autenticado_puede_crear_solicitud()
    {
        $user = User::factory()->create();
        $mascota = Mascota::factory()->create(['estado' => 'DISPONIBLE']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/solicitudes', [
                'mascota_id' => $mascota->id,
                'comentarios_adoptante' => 'Me gustaría adoptar a esta mascota.'
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Solicitud de adopción creada exitosamente'
            ]);

        $this->assertDatabaseHas('solicitudes_adopcion', [
            'user_id' => $user->id,
            'mascota_id' => $mascota->id,
            'estado' => 'PENDIENTE'
        ]);

        // Verificar que la mascota cambió a EN_PROCESO
        $this->assertDatabaseHas('mascotas', [
            'id' => $mascota->id,
            'estado' => 'EN_PROCESO'
        ]);
    }

    public function test_no_puede_crear_solicitud_para_mascota_no_disponible()
    {
        $user = User::factory()->create();
        $mascota = Mascota::factory()->create(['estado' => 'ADOPTADA']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/solicitudes', [
                'mascota_id' => $mascota->id
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('mascota_id');
    }

    public function test_no_puede_crear_solicitud_duplicada()
    {
        $user = User::factory()->create();
        $mascota = Mascota::factory()->create(['estado' => 'DISPONIBLE']);

        // Crear primera solicitud
        SolicitudAdopcion::create([
            'user_id' => $user->id,
            'mascota_id' => $mascota->id,
            'estado' => 'PENDIENTE'
        ]);

        // Intentar crear segunda solicitud
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/solicitudes', [
                'mascota_id' => $mascota->id
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('mascota_id');
    }

    public function test_usuario_puede_ver_sus_solicitudes()
    {
        $user = User::factory()->create();
        $otroUser = User::factory()->create();
        $mascota1 = Mascota::factory()->create();
        $mascota2 = Mascota::factory()->create();

        SolicitudAdopcion::factory()->create(['user_id' => $user->id, 'mascota_id' => $mascota1->id]);
        SolicitudAdopcion::factory()->create(['user_id' => $user->id, 'mascota_id' => $mascota2->id]);
        SolicitudAdopcion::factory()->create(['user_id' => $otroUser->id, 'mascota_id' => $mascota1->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/solicitudes/mis-solicitudes');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_puede_ver_todas_las_solicitudes()
    {
        $admin = User::factory()->create(['rol' => 'ADMINISTRADOR']);
        $mascota = Mascota::factory()->create();

        SolicitudAdopcion::factory()->count(5)->create(['mascota_id' => $mascota->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/solicitudes');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_usuario_normal_no_puede_ver_todas_las_solicitudes()
    {
        $user = User::factory()->create(['rol' => 'USUARIO']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/solicitudes');

        $response->assertStatus(403);
    }

    public function test_admin_puede_aprobar_solicitud()
    {
        $admin = User::factory()->create(['rol' => 'ADMINISTRADOR']);
        $mascota = Mascota::factory()->create(['estado' => 'EN_PROCESO']);
        $solicitud = SolicitudAdopcion::factory()->create([
            'mascota_id' => $mascota->id,
            'estado' => 'PENDIENTE'
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/solicitudes/{$solicitud->id}/aprobar");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Solicitud aprobada exitosamente'
            ]);

        $this->assertDatabaseHas('solicitudes_adopcion', [
            'id' => $solicitud->id,
            'estado' => 'APROBADA'
        ]);

        $this->assertDatabaseHas('mascotas', [
            'id' => $mascota->id,
            'estado' => 'ADOPTADA'
        ]);
    }

    public function test_admin_puede_rechazar_solicitud()
    {
        $admin = User::factory()->create(['rol' => 'ADMINISTRADOR']);
        $solicitud = SolicitudAdopcion::factory()->create(['estado' => 'PENDIENTE']);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/solicitudes/{$solicitud->id}/rechazar", [
                'motivo_rechazo' => 'No cumple con los requisitos.'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Solicitud rechazada'
            ]);

        $this->assertDatabaseHas('solicitudes_adopcion', [
            'id' => $solicitud->id,
            'estado' => 'RECHAZADA',
            'motivo_rechazo' => 'No cumple con los requisitos.'
        ]);
    }

    public function test_usuario_normal_no_puede_aprobar_solicitudes()
    {
        $user = User::factory()->create(['rol' => 'USUARIO']);
        $solicitud = SolicitudAdopcion::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/solicitudes/{$solicitud->id}/aprobar");

        $response->assertStatus(403);
    }

    public function test_se_registra_historial_al_crear_solicitud()
    {
        $user = User::factory()->create();
        $mascota = Mascota::factory()->create(['estado' => 'DISPONIBLE']);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/solicitudes', [
                'mascota_id' => $mascota->id
            ]);

        $this->assertDatabaseHas('historial_estado_solicitud', [
            'estado_actual' => 'PENDIENTE',
            'cambiado_por' => $user->id
        ]);
    }
}
