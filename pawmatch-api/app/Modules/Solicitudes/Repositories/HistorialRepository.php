<?php

namespace App\Modules\Solicitudes\Repositories;

use App\Models\HistorialEstadoSolicitud;

class HistorialRepository
{
    public function registrarCambio(
        int $solicitudId,
        ?string $estadoAnterior,
        string $estadoActual,
        ?int $cambiadoPor = null,
        ?string $comentario = null
    ): HistorialEstadoSolicitud {
        return HistorialEstadoSolicitud::create([
            'solicitud_id' => $solicitudId,
            'estado_anterior' => $estadoAnterior,
            'estado_actual' => $estadoActual,
            'cambiado_por' => $cambiadoPor,
            'comentario' => $comentario,
            'fecha_cambio' => now(),
        ]);
    }

    public function obtenerHistorial(int $solicitudId): array
    {
        return HistorialEstadoSolicitud::with('usuario:id,nombre,email')
            ->where('solicitud_id', $solicitudId)
            ->orderBy('fecha_cambio', 'desc')
            ->get()
            ->map(function ($historial) {
                return [
                    'id' => $historial->id,
                    'estado_anterior' => $historial->estado_anterior,
                    'estado_actual' => $historial->estado_actual,
                    'comentario' => $historial->comentario,
                    'cambiado_por' => $historial->usuario ? [
                        'id' => $historial->usuario->id,
                        'nombre' => $historial->usuario->nombre,
                        'email' => $historial->usuario->email,
                    ] : null,
                    'fecha_cambio' => $historial->fecha_cambio->toISOString(),
                ];
            })
            ->toArray();
    }
}
