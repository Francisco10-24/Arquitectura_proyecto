<?php

namespace App\Mail;

use App\Models\SolicitudAdopcion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudAprobadaMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Crear una nueva instancia de solicitud
     */
    public function __construct(
        public SolicitudAdopcion $solicitud
    ) {}

    /**
     * Mandar el mensaje de solicitud aprobada
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Felicidades! Tu Solicitud de Adopción fue Aprobada - PawMatch',
        );
    }

    /**
     * Cargar la vista de correo electrónico
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.solicitud-aprobada',
        );
    }

    /**
     *
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
