<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\DTOs\LoginUserDTO;
use App\Modules\Auth\DTOs\RegisterUserDTO;
use App\Modules\Auth\UseCases\LoginUserUseCase;
use App\Modules\Auth\UseCases\RegisterUserUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Instancia de los use cases
    public function __construct(
        private RegisterUserUseCase $registerUserUseCase,
        private LoginUserUseCase $loginUserUseCase
    ) {}

    /**
     * Registrar un usuario
     */

    public function register(Request $request): JsonResponse
    {
        try {
            // Crear request validando los datos
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:8|confirmed',
                'telefono' => 'nullable|string|max:20',
                'direccion' => 'nullable|string',
                'rol' => 'nullable|in:USUARIO,ADMINISTRADOR'
            ]);

            // Llamar al dto para encapsular los datos y mandar la request para el use case y transformarlo en un objeto
            $dto = RegisterUserDTO::fromRequest($validated);
            // Llamar al use case para crear el usuario
            $result = $this->registerUserUseCase->execute($dto);

            // Si el usuario se creó correctamente, devolver una respuesta con el mensaje y el objeto creado
            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'data' => $result
            ], 201);
        }
        // Si el usuario no se creó correctamente, devolver una respuesta con el mensaje de error
        catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }// Si ocurre algún error, devolver una respuesta con el mensaje de error
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Iniciar sesión
     */
    public function login(Request $request): JsonResponse
    {
        try {
            // Crear request validando los datos
            $validated = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
            // Llamar al dto para encapsular los datos y mandar la request para el use case y transformarlo en un objeto
            $dto = LoginUserDTO::fromRequest($validated);
            // Llamar al use case para el inicio de sesión
            $result = $this->loginUserUseCase->execute($dto);

            // Devolver un mensaje de inicio de sesión y el objeto creado
            return response()->json([
                'message' => 'Inicio de sesión exitoso',
                'data' => $result
            ], 200);
        } 
        // Si el usuario no inicio sesión correctamente, devolver una respuesta con el mensaje de error
        catch (ValidationException $e) {
            return response()->json([
                'message' => 'Credenciales incorrectas',
                'errors' => $e->errors()
            ], 401);
        }
        // Si ocurre algún error, devolver una respuesta con el mensaje de error
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al iniciar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cerrar sesión
     */

    public function logout(Request $request): JsonResponse
    {
        // Verificar el token actual del usuario y eliminarlo
        if ($request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        // Devolver un mensaje de cierre de sesión
        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    /**
     * Ver información del usuario
     */
    public function me(Request $request): JsonResponse
    {
        // Mandar la respuesta de los datos del usurio
        return response()->json([
            'data' => [
                'id' => $request->user()->id,
                'nombre' => $request->user()->nombre,
                'email' => $request->user()->email,
                'rol' => $request->user()->rol,
            ]
        ], 200);
    }
}
