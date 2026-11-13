<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'telefono' => 'required|string|max:20',
            'password' => 'required|string|min:6',
            'acepta_terminos' => 'required|accepted',
        ]);

        $user = new User([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'telefono' => $request->input('telefono'),
            'password' => $request->input('password'),
            'terminos_aceptados_at' => now(),
        ]);
        $user->rol = Rol::Cliente;
        $user->save();

        return $this->respuestaConToken($user, $request->input('dispositivo'))
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (!$user || $user->estaEliminada() || !Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Correo o contraseña incorrectos.',
            ]);
        }

        return $this->respuestaConToken($user, $request->input('dispositivo'));
    }

    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->noContent();
    }

    private function respuestaConToken(User $user, ?string $dispositivo): JsonResponse
    {
        $token = $user->createToken($dispositivo ?: 'app')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'token' => $token,
            'access_token' => $token,
            'user' => new UserResource($user),
        ]);
    }
}
