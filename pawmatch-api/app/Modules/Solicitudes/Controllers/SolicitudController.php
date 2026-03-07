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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SolicitudController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private CreateSolicitudUseCase $createSolicitudUseCase,
        private ListMySolicitudesUseCase $listMySolicitudesUseCase,
        private ListAllSolicitudesUseCase $listAllSolicitudesUseCase,
        private GetSolicitudUseCase $getSolicitudUseCase,
        private AprobarSolicitudUseCase $aprobarSolicitudUseCase,
        private RechazarSolicitudUseCase $rechazarSolicitudUseCase
    ) {}

    /**
     * Crear solicitud de adopción
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->authorize('create', SolicitudAdopcion::class);

            $validated = $request->validate([
                'mascota_id' => 'required|integer|exists:mascotas,id',
                'comentarios_adoptante' => 'nullable|string|max:1000',
            ]);

            $dto = CreateSolicitudDTO::fromRequest($validated, $request->user()->id);
            $result = $this->createSolicitudUseCase->execute($dto);

            return response()->json([
                'message' => 'Solicitud de adopción creada exitosamente',
                'data' => $result
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar mis solicitudes
     */
    public function myIndex(Request $request): JsonResponse
    {
        try {
            $result = $this->listMySolicitudesUseCase->execute($request->user()->id);

            return response()->json($result, 200);

        } catch (\Exception $e) {
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
            $this->authorize('updateEstado', SolicitudAdopcion::class);

            $validated = $request->validate([
                'estado' => 'nullable|in:PENDIENTE,EN_REVISION,APROBADA,RECHAZADA,CANCELADA',
            ]);

            $result = $this->listAllSolicitudesUseCase->execute($validated['estado'] ?? null);

            return response()->json($result, 200);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado.'
            ], 403);
        } catch (\Exception $e) {
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
            $solicitud = SolicitudAdopcion::findOrFail($id);
            $this->authorize('view', $solicitud);

            $result = $this->getSolicitudUseCase->execute($id);

            return response()->json([
                'data' => $result
            ], 200);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado para ver esta solicitud.'
            ], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Solicitud no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprobar solicitud
     */
    public function aprobar(int $id): JsonResponse
    {
        try {
            $this->authorize('updateEstado', SolicitudAdopcion::class);

            $result = $this->aprobarSolicitudUseCase->execute($id);

            return response()->json([
                'message' => 'Solicitud aprobada exitosamente',
                'data' => $result
            ], 200);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado.'
            ], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Solicitud no encontrada'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
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
            $this->authorize('updateEstado', SolicitudAdopcion::class);

            $validated = $request->validate([
                'motivo_rechazo' => 'required|string|max:1000',
            ]);

            $result = $this->rechazarSolicitudUseCase->execute($id, $validated['motivo_rechazo']);

            return response()->json([
                'message' => 'Solicitud rechazada',
                'data' => $result
            ], 200);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado.'
            ], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Solicitud no encontrada'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al rechazar solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}