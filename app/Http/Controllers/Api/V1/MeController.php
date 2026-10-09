<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\CuentaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MeController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /** Elimina la cuenta propia. Pide la contraseña para evitar borrados por error. */
    public function destroy(Request $request, CuentaService $cuentas): Response
    {
        $request->validate(['password' => ['required', 'string']]);

        $user = $request->user();

        abort_if($user->esAdmin(), Response::HTTP_FORBIDDEN, 'Las cuentas de administrador se gestionan desde el panel.');

        if (! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages(['password' => 'La contraseña no es correcta.']);
        }

        $cuentas->eliminar($user);

        return response()->noContent();
    }
}
