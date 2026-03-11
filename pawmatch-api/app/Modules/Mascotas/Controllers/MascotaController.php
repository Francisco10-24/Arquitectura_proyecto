<?php

namespace App\Modules\Mascotas\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Mascotas\DTOs\CreateMascotaDTO;
use App\Modules\Mascotas\DTOs\FilterMascotasDTO;
use App\Modules\Mascotas\DTOs\UpdateMascotaDTO;
use App\Modules\Mascotas\UseCases\CreateMascotaUseCase;
use App\Modules\Mascotas\UseCases\DeleteMascotaUseCase;
use App\Modules\Mascotas\UseCases\GetMascotaUseCase;
use App\Modules\Mascotas\UseCases\ListMascotasUseCase;
use App\Modules\Mascotas\UseCases\UpdateMascotaUseCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Mascota;
use App\Policies\MascotaPolicy;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Modules\Mascotas\UseCases\RestoreMascotaUseCase;
use App\Modules\Mascotas\UseCases\ListTrashedMascotasUseCase;
use Illuminate\Support\Facades\Storage;

class MascotaController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private ListMascotasUseCase $listMascotasUseCase,
        private GetMascotaUseCase $getMascotaUseCase,
        private CreateMascotaUseCase $createMascotaUseCase,
        private UpdateMascotaUseCase $updateMascotaUseCase,
        private DeleteMascotaUseCase $deleteMascotaUseCase,
        private RestoreMascotaUseCase $restoreMascotaUseCase,
        private ListTrashedMascotasUseCase $listTrashedMascotasUseCase
    ) {}

    /**
     * Listar mascotas
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'especie' => 'nullable|in:PERRO,GATO,OTRO',
                'estado' => 'nullable|in:DISPONIBLE,EN_PROCESO,ADOPTADA,INACTIVA',
                'sexo' => 'nullable|in:MACHO,HEMBRA',
                'search' => 'nullable|string|max:255',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $filters = FilterMascotasDTO::fromRequest($validated);
            $result = $this->listMascotasUseCase->execute($filters);

            return response()->json($result, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al listar mascotas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar mascota
     */
    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->getMascotaUseCase->execute($id);

            return response()->json([
                'data' => $result
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Mascota no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener mascota',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear mascota
     */

    public function store(Request $request): JsonResponse
    {
        try {
            $this->authorize('create', Mascota::class);

            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'especie' => 'required|in:PERRO,GATO,OTRO',
                'raza' => 'nullable|string|max:255',
                'edad_aproximada' => 'nullable|integer|min:0',
                'sexo' => 'nullable|in:MACHO,HEMBRA',
                'descripcion' => 'nullable|string',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'estado' => 'nullable|in:DISPONIBLE,EN_PROCESO,ADOPTADA,INACTIVA',
            ]);
            
            if ($request->hasFile('foto')) {
                $path = $request->file('foto')->store('mascotas', 'public');
                $validated['foto_url'] = Storage::url($path);
            }

            $dto = CreateMascotaDTO::fromRequest($validated);
            $result = $this->createMascotaUseCase->execute($dto);

            return response()->json([
                'message' => 'Mascota creada exitosamente',
                'data' => $result
            ], 201);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado. Se requiere rol de administrador.'
            ], 403);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear mascota',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar mascota 
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $mascota = Mascota::findOrFail($id);
            $this->authorize('update', $mascota);

            $validated = $request->validate([
                'nombre' => 'sometimes|string|max:255',
                'especie' => 'sometimes|in:PERRO,GATO,OTRO',
                'raza' => 'nullable|string|max:255',
                'edad_aproximada' => 'nullable|integer|min:0',
                'sexo' => 'nullable|in:MACHO,HEMBRA',
                'descripcion' => 'nullable|string',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'estado' => 'sometimes|in:DISPONIBLE,EN_PROCESO,ADOPTADA,INACTIVA',
            ]);

            if ($request->hasFile('foto')) {

                if ($mascota->foto_url) {
                    $oldPath = str_replace('/storage', 'public', $mascota->foto_url);
                    Storage::delete($oldPath);
                }

                $path = $request->file('foto')->store('mascotas', 'public');
                $validated['foto_url'] = Storage::url($path);
            }

            $dto = UpdateMascotaDTO::fromRequest($validated);
            $result = $this->updateMascotaUseCase->execute($id, $dto);

            return response()->json([
                'message' => 'Mascota actualizada exitosamente',
                'data' => $result
            ], 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado. Se requiere rol de administrador.'
            ], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Mascota no encontrada'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar mascota',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar mascota 
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $mascota = Mascota::findOrFail($id);
            $this->authorize('delete', $mascota);

            if ($mascota->foto_url) {
                $path = str_replace('/storage/', '', $mascota->foto_url);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            $this->deleteMascotaUseCase->execute($id);

            return response()->json([
                'message' => 'Mascota eliminada exitosamente'
            ], 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado. Se requiere rol de administrador.'
            ], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Mascota no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar mascota',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar mascotas eliminadas
     */
    public function trashed(): JsonResponse
    {
        try {
            $this->authorize('create', Mascota::class); // Solo admins

            $result = $this->listTrashedMascotasUseCase->execute();

            return response()->json($result, 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado.'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al listar mascotas eliminadas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar mascota eliminada
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $this->authorize('create', Mascota::class); // Solo admins

            $result = $this->restoreMascotaUseCase->execute($id);

            return response()->json([
                'message' => 'Mascota restaurada exitosamente',
                'data' => $result
            ], 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado.'
            ], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Mascota eliminada no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al restaurar mascota',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
