<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\CuentaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MeController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /** Sube o actualiza la foto de perfil del usuario (clienta o profesional). */
    public function subirFoto(Request $request): UserResource
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $user = $request->user();

        if ($user->foto_perfil && Storage::disk('public')->exists($user->foto_perfil)) {
            Storage::disk('public')->delete($user->foto_perfil);
        }

        $ruta = $request->file('foto')->store('avatares', 'public');
        $user->update(['foto_perfil' => $ruta]);

        return new UserResource($user->fresh());
    }

    /** Elimina la foto de perfil del usuario. */
    public function borrarFoto(Request $request): UserResource
    {
        $user = $request->user();

        if ($user->foto_perfil && Storage::disk('public')->exists($user->foto_perfil)) {
            Storage::disk('public')->delete($user->foto_perfil);
        }

        $user->update(['foto_perfil' => null]);

        return new UserResource($user->fresh());
    }

    /** Elimina la cuenta propia anonimizándola. */
    public function destroy(Request $request, CuentaService $cuentas): Response
    {
        $request->validate(['password' => ['required', 'string']]);

        $user = $request->user();

        abort_if($user->esAdmin(), Response::HTTP_FORBIDDEN, 'Las cuentas de administrador se gestionan desde el panel.');

        if (!Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages(['password' => 'La contraseña no es correcta.']);
        }

        $cuentas->eliminar($user);

        return response()->noContent();
    }
}
