<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_adopcion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mascota_id')->constrained('mascotas')->onDelete('cascade');
            $table->enum('estado', ['PENDIENTE', 'EN_REVISION', 'APROBADA', 'RECHAZADA', 'CANCELADA'])
                  ->default('PENDIENTE');
            $table->text('motivo_rechazo')->nullable();
            $table->text('comentarios_adoptante')->nullable();
            $table->timestamps();
            $table->softDeletes();
          
            $table->index('user_id');
            $table->index('mascota_id');
            $table->index('estado');
            
            $table->unique(['user_id', 'mascota_id', 'deleted_at'], 'unique_active_solicitud');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_adopcion');
    }
};