<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_estado_solicitud', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes_adopcion')->onDelete('cascade');
            $table->string('estado_anterior')->nullable();
            $table->string('estado_actual');
            $table->foreignId('cambiado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->text('comentario')->nullable();
            $table->timestamp('fecha_cambio');
            
            $table->index('solicitud_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_estado_solicitud');
    }
};