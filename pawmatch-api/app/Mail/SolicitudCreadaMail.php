<?php

namespace App\Mail;

use App\Models\SolicitudAdopcion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudCreadaMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Crear instancia de solicitud
     */
    public function __construct(
        public SolicitudAdopcion $solicitud
    )
    {} 

    /**
     * Obtener el mensaje de envío
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Solicitud de Adopción - PawMatch',
        );
    }

    /**
     * Cargar la vista de correo electrónico
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.solicitud-creada',
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
