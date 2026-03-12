<?php

namespace App\Services;

use App\Mail\SolicitudAprobadaMail;
use App\Mail\SolicitudCreadaMail;
use App\Mail\SolicitudRechazadaMail;
use App\Models\SolicitudAdopcion;
use App\Modules\Solicitudes\Repositories\NotificacionRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function __construct(
        private NotificacionRepository $notificacionRepository
    ) {}

    public function enviarSolicitudCreada(SolicitudAdopcion $solicitud): void
    {
        // Registrar notificación de correo electrónico
        $notificacion = $this->notificacionRepository->registrarEnvio(
            tipo: 'solicitud_creada',
            destinatarioEmail: $solicitud->user->email,
            solicitudId: $solicitud->id,
            estadoEnvio: 'PENDIENTE'
        );

        try {
            // Enviar correo electrónico
            Mail::to($solicitud->user->email)
                ->send(new SolicitudCreadaMail($solicitud));

            $this->notificacionRepository->marcarComoEnviada($notificacion->id);

            Log::info("Email enviado: Solicitud creada", [
                'solicitud_id' => $solicitud->id,
                'destinatario' => $solicitud->user->email
            ]);
        } catch (\Exception $e) {
            $this->notificacionRepository->marcarComoFallida(
                $notificacion->id,
                $e->getMessage()
            );

            Log::error("Error al enviar email: Solicitud creada", [
                'solicitud_id' => $solicitud->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function enviarSolicitudAprobada(SolicitudAdopcion $solicitud): void
    {
        $notificacion = $this->notificacionRepository->registrarEnvio(
            tipo: 'solicitud_aprobada',
            destinatarioEmail: $solicitud->user->email,
            solicitudId: $solicitud->id,
            estadoEnvio: 'PENDIENTE'
        );

        try {
            Mail::to($solicitud->user->email)
                ->send(new SolicitudAprobadaMail($solicitud));

            $this->notificacionRepository->marcarComoEnviada($notificacion->id);

            Log::info("Email enviado: Solicitud aprobada", [
                'solicitud_id' => $solicitud->id,
                'destinatario' => $solicitud->user->email
            ]);
        } catch (\Exception $e) {
            $this->notificacionRepository->marcarComoFallida(
                $notificacion->id,
                $e->getMessage()
            );

            Log::error("Error al enviar email: Solicitud aprobada", [
                'solicitud_id' => $solicitud->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function enviarSolicitudRechazada(SolicitudAdopcion $solicitud): void
    {
        $notificacion = $this->notificacionRepository->registrarEnvio(
            tipo: 'solicitud_rechazada',
            destinatarioEmail: $solicitud->user->email,
            solicitudId: $solicitud->id,
            estadoEnvio: 'PENDIENTE'
        );

        try {
            Mail::to($solicitud->user->email)
                ->send(new SolicitudRechazadaMail($solicitud));

            $this->notificacionRepository->marcarComoEnviada($notificacion->id);

            Log::info("Email enviado: Solicitud rechazada", [
                'solicitud_id' => $solicitud->id,
                'destinatario' => $solicitud->user->email
            ]);
        } catch (\Exception $e) {
            $this->notificacionRepository->marcarComoFallida(
                $notificacion->id,
                $e->getMessage()
            );

            Log::error("Error al enviar email: Solicitud rechazada", [
                'solicitud_id' => $solicitud->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
