<?php

namespace App\Modules\Solicitudes\Repositories;

use App\Models\NotificacionCorreo;

class NotificacionRepository
{
    public function registrarEnvio(
        string $tipo,
        string $destinatarioEmail,
        ?int $solicitudId = null,
        string $estadoEnvio = 'PENDIENTE',
        ?string $mensajeError = null
    ): NotificacionCorreo {
        return NotificacionCorreo::create([
            'tipo' => $tipo,
            'destinatario_email' => $destinatarioEmail,
            'solicitud_id' => $solicitudId,
            'estado_envio' => $estadoEnvio,
            'mensaje_error' => $mensajeError,
            'fecha_envio' => $estadoEnvio === 'ENVIADA' ? now() : null,
        ]);
    }

    public function marcarComoEnviada(int $notificacionId): void
    {
        NotificacionCorreo::where('id', $notificacionId)->update([
            'estado_envio' => 'ENVIADA',
            'fecha_envio' => now(),
        ]);
    }

    public function marcarComoFallida(int $notificacionId, string $mensajeError): void
    {
        NotificacionCorreo::where('id', $notificacionId)->update([
            'estado_envio' => 'FALLIDA',
            'mensaje_error' => $mensajeError,
        ]);
    }
}