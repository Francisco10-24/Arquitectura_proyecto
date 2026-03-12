<?php

namespace App\Modules\Solicitudes\UseCases;

use App\Modules\Solicitudes\Repositories\SolicitudRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GetSolicitudUseCase
{
    public function __construct(
        private SolicitudRepository $solicitudRepository
    ) {}

    public function execute(int $id): array
    {
        // Verificar que la solicitud existe
        $solicitud = $this->solicitudRepository->findById($id);

        if (!$solicitud) {
            throw new ModelNotFoundException('Solicitud no encontrada');
        }

        // Si la existe, retornar los datos 
        return [
            'id' => $solicitud->id,
            'estado' => $solicitud->estado,
            'comentarios_adoptante' => $solicitud->comentarios_adoptante,
            'motivo_rechazo' => $solicitud->motivo_rechazo,
            'adoptante' => [
                'id' => $solicitud->user->id,
                'nombre' => $solicitud->user->nombre,
                'email' => $solicitud->user->email,
                'telefono' => $solicitud->user->telefono,
                'direccion' => $solicitud->user->direccion,
            ],
            'mascota' => [
                'id' => $solicitud->mascota->id,
                'nombre' => $solicitud->mascota->nombre,
                'especie' => $solicitud->mascota->especie,
                'raza' => $solicitud->mascota->raza,
                'edad_aproximada' => $solicitud->mascota->edad_aproximada,
                'sexo' => $solicitud->mascota->sexo,
                'descripcion' => $solicitud->mascota->descripcion,
                'foto_url' => $solicitud->mascota->foto_url,
                'estado' => $solicitud->mascota->estado,
            ],
            'created_at' => $solicitud->created_at->toISOString(),
            'updated_at' => $solicitud->updated_at->toISOString(),
        ];
    }
}