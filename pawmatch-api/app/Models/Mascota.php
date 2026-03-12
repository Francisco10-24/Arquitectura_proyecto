<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mascota extends Model
{
    use HasFactory, SoftDeletes;

    //Atributos de la tabla

    protected $fillable = [
        'nombre',
        'especie',
        'raza',
        'edad_aproximada',
        'sexo',
        'descripcion',
        'foto_url',
        'estado'
    ];
     
    protected $casts = [
        'edad_aproximada' => 'integer',
    ];

    // Relaciones
     public function solicitudes()
    {
        return $this->hasMany(SolicitudAdopcion::class);
    }

    // Métodos helper
    public function isDisponible(): bool
    {
        return $this->estado === 'DISPONIBLE';
    }

    public function marcarComoEnProceso(): void
    {
        $this->update(['estado' => 'EN_PROCESO']);
    }

    public function marcarComoAdoptada(): void
    {
        $this->update(['estado' => 'ADOPTADA']);
    }

}
