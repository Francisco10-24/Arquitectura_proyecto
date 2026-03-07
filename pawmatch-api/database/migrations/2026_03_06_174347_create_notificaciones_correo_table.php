<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones_correo', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // 'solicitud_creada', 'estado_cambio', etc.
            $table->string('destinatario_email');
            $table->foreignId('solicitud_id')->nullable()->constrained('solicitudes_adopcion')->onDelete('cascade');
            $table->enum('estado_envio', ['ENVIADA', 'FALLIDA', 'PENDIENTE'])->default('PENDIENTE');
            $table->text('mensaje_error')->nullable();
            $table->timestamp('fecha_envio')->nullable();
            $table->timestamps();
            
            $table->index('estado_envio');
            $table->index('solicitud_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones_correo');
    }
};