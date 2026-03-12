<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialEstadoSolicitud extends Model
{
    use HasFactory;

    //Instancia 
    protected $table = 'historial_estado_solicitud';

    public $timestamps = false; 

    // Atributos de la tabla
    protected $fillable = [
        'solicitud_id',
        'estado_anterior',
        'estado_actual',
        'cambiado_por',
        'comentario',
        'fecha_cambio',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
    ];

    // Relaciones
    public function solicitud()
    {
        return $this->belongsTo(SolicitudAdopcion::class, 'solicitud_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'cambiado_por');
    }
}