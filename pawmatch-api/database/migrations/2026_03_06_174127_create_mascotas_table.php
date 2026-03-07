<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mascotas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('especie', ['PERRO', 'GATO', 'OTRO']);
            $table->string('raza')->nullable();
            $table->integer('edad_aproximada')->nullable(); // en meses
            $table->enum('sexo', ['MACHO', 'HEMBRA'])->nullable();
            $table->text('descripcion')->nullable();
            $table->string('foto_url')->nullable();
            $table->enum('estado', ['DISPONIBLE', 'EN_PROCESO', 'ADOPTADA', 'INACTIVA'])
                  ->default('DISPONIBLE');
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para búsquedas
            $table->index('especie');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
};