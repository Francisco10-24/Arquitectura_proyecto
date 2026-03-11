<?php

namespace Tests\Feature;

use App\Models\Mascota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MascotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_cualquier_usuario_puede_listar_mascotas_disponibles()
    {
        Mascota::factory()->count(5)->create(['estado' => 'DISPONIBLE']);
        Mascota::factory()->count(2)->create(['estado' => 'ADOPTADA']);

        $response = $this->getJson('/api/mascotas');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'pagination'
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_cualquier_usuario_puede_ver_detalle_de_mascota()
    {
        $mascota = Mascota::factory()->create([
            'nombre' => 'Max',
            'especie' => 'PERRO',
            'estado' => 'DISPONIBLE'
        ]);

        $response = $this->getJson("/api/mascotas/{$mascota->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $mascota->id,
                    'nombre' => 'Max',
                    'especie' => 'PERRO'
                ]
            ]);
    }

    public function test_admin_puede_crear_mascota()
    {
        $admin = User::factory()->create(['rol' => 'ADMINISTRADOR']);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/mascotas', [
                'nombre' => 'Luna',
                'especie' => 'GATO',
                'raza' => 'Persa',
                'edad_aproximada' => 18,
                'sexo' => 'HEMBRA',
                'descripcion' => 'Gata hermosa',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Mascota creada exitosamente'
            ]);

        $this->assertDatabaseHas('mascotas', [
            'nombre' => 'Luna',
            'especie' => 'GATO',
            'estado' => 'DISPONIBLE'
        ]);
    }

    public function test_usuario_normal_no_puede_crear_mascotas()
    {
        $user = User::factory()->create(['rol' => 'USUARIO']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/mascotas', [
                'nombre' => 'Luna',
                'especie' => 'GATO'
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'No autorizado. Se requiere rol de administrador.'
            ]);
    }

    public function test_admin_puede_actualizar_mascota()
    {
        $admin = User::factory()->create(['rol' => 'ADMINISTRADOR']);
        $mascota = Mascota::factory()->create(['nombre' => 'Max']);

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/mascotas/{$mascota->id}", [
                'nombre' => 'Max Actualizado',
                'descripcion' => 'Descripción actualizada'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Mascota actualizada exitosamente'
            ]);

        $this->assertDatabaseHas('mascotas', [
            'id' => $mascota->id,
            'nombre' => 'Max Actualizado'
        ]);
    }

    public function test_admin_puede_eliminar_mascota()
    {
        $admin = User::factory()->create(['rol' => 'ADMINISTRADOR']);
        $mascota = Mascota::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/mascotas/{$mascota->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Mascota eliminada exitosamente'
            ]);

        $this->assertSoftDeleted('mascotas', [
            'id' => $mascota->id
        ]);
    }

    public function test_puede_filtrar_mascotas_por_especie()
    {
        Mascota::factory()->count(3)->create(['especie' => 'PERRO', 'estado' => 'DISPONIBLE']);
        Mascota::factory()->count(2)->create(['especie' => 'GATO', 'estado' => 'DISPONIBLE']);

        $response = $this->getJson('/api/mascotas?especie=PERRO');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_puede_filtrar_mascotas_por_sexo()
    {
        Mascota::factory()->count(4)->create(['sexo' => 'MACHO', 'estado' => 'DISPONIBLE']);
        Mascota::factory()->count(3)->create(['sexo' => 'HEMBRA', 'estado' => 'DISPONIBLE']);

        $response = $this->getJson('/api/mascotas?sexo=HEMBRA');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_puede_buscar_mascotas_por_nombre()
    {
        Mascota::factory()->create(['nombre' => 'Max', 'estado' => 'DISPONIBLE']);
        Mascota::factory()->create(['nombre' => 'Luna', 'estado' => 'DISPONIBLE']);
        Mascota::factory()->create(['nombre' => 'Rocky', 'estado' => 'DISPONIBLE']);

        $response = $this->getJson('/api/mascotas?search=Max');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['nombre' => 'Max']);
    }
}
