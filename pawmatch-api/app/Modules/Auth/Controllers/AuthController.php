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
    public function __construct(
        private RegisterUserUseCase $registerUserUseCase,
        private LoginUserUseCase $loginUserUseCase
    ) {}

    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:8|confirmed',
                'telefono' => 'nullable|string|max:20',
                'direccion' => 'nullable|string',
                'rol' => 'nullable|in:USUARIO,ADMINISTRADOR'
            ]);

            $dto = RegisterUserDTO::fromRequest($validated);
            $result = $this->registerUserUseCase->execute($dto);

            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'data' => $result
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $dto = LoginUserDTO::fromRequest($validated);
            $result = $this->loginUserUseCase->execute($dto);

            return response()->json([
                'message' => 'Inicio de sesión exitoso',
                'data' => $result
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Credenciales incorrectas',
                'errors' => $e->errors()
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al iniciar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    public function me(Request $request): JsonResponse
    {
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
