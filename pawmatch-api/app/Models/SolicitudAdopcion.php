<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudAdopcion extends Model
{
    use HasFactory, SoftDeletes;
    // Instancia

    protected $table = 'solicitudes_adopcion';

    // Atributos de la tabla
    protected $fillable = [
        'user_id',
        'mascota_id',
        'estado',
        'motivo_rechazo',
        'comentarios_adoptante'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones

     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function historial()
    {
        return $this->hasMany(HistorialEstadoSolicitud::class, 'solicitud_id');
    }

    public function isPendiente(): bool
    {
        return $this->estado === 'PENDIENTE';
    }

    public function isAprobada(): bool
    {
        return $this->estado === 'APROBADA';
    }

    public function isRechazada(): bool
    {
        return $this->estado === 'RECHAZADA';
    }
}
