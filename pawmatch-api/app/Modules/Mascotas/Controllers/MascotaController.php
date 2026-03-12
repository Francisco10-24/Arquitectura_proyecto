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

    // Instancia de los use cases
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
            // Validar los datos de la request
            $validated = $request->validate([
                'especie' => 'nullable|in:PERRO,GATO,OTRO',
                'estado' => 'nullable|in:DISPONIBLE,EN_PROCESO,ADOPTADA,INACTIVA',
                'sexo' => 'nullable|in:MACHO,HEMBRA',
                'search' => 'nullable|string|max:255',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);
            // Convertir los datos de la request a un objeto
            $filters = FilterMascotasDTO::fromRequest($validated);
            $result = $this->listMascotasUseCase->execute($filters);

            return response()->json($result, 200);
        } // Verificar si hay errores de validación
        catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } // Verificar si hay algún error en el servidor
        catch (\Exception $e) {
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
            // Obtener el caso de uso para obtener la mascota
            $result = $this->getMascotaUseCase->execute($id);

            // Devolver la respuesta si existe
            return response()->json([
                'data' => $result
            ], 200);
        } // Devolver error si no existe
        catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Mascota no encontrada'
            ], 404);
        } // Devolver error en caso de error del servidor
        catch (\Exception $e) {
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
            // Autorizar el caso de uso para crear una mascota
            $this->authorize('create', Mascota::class);

            // Validar los datos enviados
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

            // Si el caso de uso recibe un archivo, guardarlo en la base de datos
            if ($request->hasFile('foto')) {
                $path = $request->file('foto')->store('mascotas', 'public');
                $validated['foto_url'] = Storage::url($path);
            }
            // Crear objeto de DTO a partir de los datos validados
            $dto = CreateMascotaDTO::fromRequest($validated);
            $result = $this->createMascotaUseCase->execute($dto);

            // Devolver respuesta con el resultado
            return response()->json([
                'message' => 'Mascota creada exitosamente',
                'data' => $result
            ], 201);
        } // Devolver error si no está autorizado
        catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado. Se requiere rol de administrador.'
            ], 403);
        } // Devolver error de validación
        catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } // Devolver error en caso de error del servidor
        catch (\Exception $e) {
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
            // Obtener la mascota
            $mascota = Mascota::findOrFail($id);
            // Verificar si el usuario está autorizado para actualizar la mascota
            $this->authorize('update', $mascota);

            // Validar los datos enviados
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

            // Si el caso de uso recibe un archivo, guardarlo en la base de datos
            if ($request->hasFile('foto')) {

                // Si la mascota tiene una foto, borrarla
                if ($mascota->foto_url) {
                    $oldPath = str_replace('/storage', 'public', $mascota->foto_url);
                    Storage::delete($oldPath);
                }
                // Guardar el nuevo archivo en la base de datos

                $path = $request->file('foto')->store('mascotas', 'public');
                $validated['foto_url'] = Storage::url($path);
            }

            // Crear objeto de DTO a partir de los datos validados
            $dto = UpdateMascotaDTO::fromRequest($validated);
            $result = $this->updateMascotaUseCase->execute($id, $dto);

            return response()->json([
                'message' => 'Mascota actualizada exitosamente',
                'data' => $result
            ], 200);
        } // Devolver error si no está autorizado
        catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado. Se requiere rol de administrador.'
            ], 403);
        } // Devolver error si no existe
        catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Mascota no encontrada'
            ], 404);
        } // Devolver error de validación
        catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } // Devolver error en caso de error del servidor
        catch (\Exception $e) {
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
            // Obtener la mascota
            $mascota = Mascota::findOrFail($id);
            // Verificar si el usuario está autorizado para eliminar la mascota
            $this->authorize('delete', $mascota);

            // Si la mascota tiene una foto, borrarla
            if ($mascota->foto_url) {
                $path = str_replace('/storage/', '', $mascota->foto_url);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
            // Eliminar la mascota llamando al caso de uso
            $this->deleteMascotaUseCase->execute($id);

            return response()->json([
                'message' => 'Mascota eliminada exitosamente'
            ], 200);
        } // Devolver error si no está autorizado
        catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado. Se requiere rol de administrador.'
            ], 403);
        } // Devolver error si no existe
        catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Mascota no encontrada'
            ], 404);
        } // Devolver error en caso de error del servidor
        catch (\Exception $e) {
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
            // Verificar si el usuario está autorizado para listar las mascotas eliminadas
            $this->authorize('create', Mascota::class);

            // Obtener los datos de la lista de mascotas eliminadas
            $result = $this->listTrashedMascotasUseCase->execute();

            return response()->json($result, 200);
        } // Devolver error si no está autorizado
        catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado.'
            ], 403);
        } // Devolver error en caso de error del servidor
        catch (\Exception $e) {
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
            // Verificar si el usuario está autorizado para restaurar la mascota
            $this->authorize('create', Mascota::class);

            // Restaurar la mascota llamando al caso de uso
            $result = $this->restoreMascotaUseCase->execute($id);

            return response()->json([
                'message' => 'Mascota restaurada exitosamente',
                'data' => $result
            ], 200);
        } // Devolver error si no está autorizado
        catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'No autorizado.'
            ], 403);
        } // Devolver error si no existe
        catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Mascota eliminada no encontrada'
            ], 404);
        } // Devolver error en caso de error del servidor
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al restaurar mascota',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
