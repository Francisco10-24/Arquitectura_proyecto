<?php

namespace App\Modules\Solicitudes\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SolicitudAdopcion;
use App\Modules\Solicitudes\DTOs\CreateSolicitudDTO;
use App\Modules\Solicitudes\UseCases\AprobarSolicitudUseCase;
use App\Modules\Solicitudes\UseCases\CreateSolicitudUseCase;
use App\Modules\Solicitudes\UseCases\GetSolicitudUseCase;
use App\Modules\Solicitudes\UseCases\ListAllSolicitudesUseCase;
use App\Modules\Solicitudes\UseCases\ListMySolicitudesUseCase;
use App\Modules\Solicitudes\UseCases\RechazarSolicitudUseCase;
use App\Modules\Solicitudes\UseCases\GetHistorialSolicitudUseCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SolicitudController extends Controller
{
    use AuthorizesRequests;

    // Constructor de la clase para inyectar dependencias
    public function __construct(
        private CreateSolicitudUseCase $createSolicitudUseCase,
        private ListMySolicitudesUseCase $listMySolicitudesUseCase,
        private ListAllSolicitudesUseCase $listAllSolicitudesUseCase,
        private GetSolicitudUseCase $getSolicitudUseCase,
        private AprobarSolicitudUseCase $aprobarSolicitudUseCase,
        private RechazarSolicitudUseCase $rechazarSolicitudUseCase,
        private GetHistorialSolicitudUseCase $getHistorialSolicitudUseCase
    ) {}

    /**
     * Crear solicitud de adopción
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Autorizar el usuario para crear una solicitud
            $this->authorize('create', SolicitudAdopcion::class);

            // Validar los datos enviados
            $validated = $request->validate([
                'mascota_id' => 'required|integer|exists:mascotas,id',
                'comentarios_adoptante' => 'nullable|string|max:1000',
            ]);

            // Mandar los datos al dto 

            $dto = CreateSolicitudDTO::fromRequest($validated, $request->user()->id);
            $result = $this->createSolicitudUseCase->execute($dto);

            // Devolver el resultado
            return response()->json([
                'message' => 'Solicitud de adopción creada exitosamente',
                'data' => $result
            ], 201);
        } // Error de validación
        catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } // Devolver un error de servidor
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar solicitudes
     */
    public function myIndex(Request $request): JsonResponse
    {
        try {
            // Obtener los datos de la solicitud
            $result = $this->listMySolicitudesUseCase->execute($request->user()->id);

            return response()->json($result, 200);
        } // Devolver un error de servidor si lo hay
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al listar solicitudes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todas las solicitudes
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Autorizar el usuario para ver las solicitudes
            $this->authorize('updateEstado', SolicitudAdopcion::class);

            // Validar las solicitudes (todas)
            $validated = $request->validate([
                'estado' => 'nullable|in:PENDIENTE,EN_REVISION,APROBADA,RECHAZADA,CANCELADA',
            ]);

            // Listar las solicitudes

            $result = $this->listAllSolicitudesUseCase->execute($validated['estado'] ?? null);

            return response()->json($result, 200);
        } // Excepción de autorización
        catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado.'
            ], 403);
        } // Excepción de servidor
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al listar solicitudes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalle de solicitud
     */
    public function show(int $id): JsonResponse
    {
        try {
            // Obtener la solicitud
            $solicitud = SolicitudAdopcion::findOrFail($id);
            // Autorizar el usuario para ver la solicitud
            $this->authorize('view', $solicitud);
            // Obtener los datos de la solicitud
            $result = $this->getSolicitudUseCase->execute($id);

            return response()->json([
                'data' => $result
            ], 200);
        } // Excepción de autorización
        catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado para ver esta solicitud.'
            ], 403);
        } // Excepción de servidor
        catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Solicitud no encontrada'
            ], 404);
        } // Excepción de servidor
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprobar solicitud
     */
    public function aprobar(Request $request, int $id): JsonResponse
    {
        try {
            // Verificar la autorización para aprobar la solicitud
            $this->authorize('updateEstado', SolicitudAdopcion::class);

            // Aprobar la solicitud
            $result = $this->aprobarSolicitudUseCase->execute($id, $request->user()->id);

            return response()->json([
                'message' => 'Solicitud aprobada exitosamente',
                'data' => $result
            ], 200);
        } // Excepción de autorización
        catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado.'
            ], 403);
        } // Excepción de servidor
        catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Solicitud no encontrada'
            ], 404);
        } // Excepción de validación
        catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } // Excepción de servidor
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al aprobar solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechazar solicitud
     */
    public function rechazar(Request $request, int $id): JsonResponse
    {
        try {
            // Verificar la autorización para rechazar la solicitud
            $this->authorize('updateEstado', SolicitudAdopcion::class);

            // Validar los datos enviados (motivo de rechazo)
            $validated = $request->validate([
                'motivo_rechazo' => 'required|string|max:1000',
            ]);
            // Rechazar la solicitud
            $result = $this->rechazarSolicitudUseCase->execute(
                $id,
                $validated['motivo_rechazo'],
                $request->user()->id
            );

            return response()->json([
                'message' => 'Solicitud rechazada',
                'data' => $result
            ], 200);
        } // Excepción de autorización
        catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado.'
            ], 403);
        } // Excepción de servidor
        catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Solicitud no encontrada'
            ], 404);
        } // Excepción de validación
        catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } // Excepción de servidor
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al rechazar solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver historial de cambios de estado
     */
    public function historial(int $id): JsonResponse
    {
        try {
            //Encontrar la solicitud
            $solicitud = SolicitudAdopcion::findOrFail($id);
            // Verificar la autorización para ver el historial de la solicitud
            $this->authorize('view', $solicitud);
            // Obtener los datos del historial de la solicitud
            $result = $this->getHistorialSolicitudUseCase->execute($id);

            return response()->json($result, 200);
        } // Excepción de autorización
        catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado para ver el historial de esta solicitud.'
            ], 403);
        } // Excepción de servidor
        catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Solicitud no encontrada'
            ], 404);
        } // Excepción de servidor
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener historial',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
