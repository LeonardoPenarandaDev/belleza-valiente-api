<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PerfilPropioResource;
use App\Models\FotoPortafolio;
use App\Models\Profesional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MiPerfilProfesionalController extends Controller
{
    public function show(Request $request): PerfilPropioResource
    {
        $profesional = $this->obtenerProfesional($request);

        return new PerfilPropioResource($profesional);
    }

    public function update(Request $request): PerfilPropioResource
    {
        $profesional = $this->obtenerProfesional($request);

        $request->validate([
            'bio' => 'nullable|string|max:1000',
            'anios_experiencia' => 'nullable|integer|min:0|max:60',
            'zonas' => 'nullable|array',
            'zonas.*' => 'integer|exists:zonas,id',
            'servicios' => 'nullable|array',
            'servicios.*.servicio_id' => 'required_with:servicios|integer|exists:servicios,id',
            'servicios.*.precio' => 'required_with:servicios|integer|min:0',
            'servicios.*.activo' => 'nullable|boolean',
        ]);

        if ($request->has('bio') || $request->has('anios_experiencia')) {
            $profesional->update($request->only(['bio', 'anios_experiencia']));
        }

        if ($request->has('zonas')) {
            $profesional->zonas()->sync($request->input('zonas', []));
        }

        if ($request->has('servicios')) {
            $serviciosData = [];
            foreach ($request->input('servicios', []) as $s) {
                $serviciosData[$s['servicio_id']] = [
                    'precio' => $s['precio'],
                    'activo' => $s['activo'] ?? true,
                ];
            }
            $profesional->servicios()->sync($serviciosData);
        }

        return new PerfilPropioResource($profesional->fresh(['user', 'zonas', 'servicios', 'fotos']));
    }

    public function subirFoto(Request $request): JsonResponse
    {
        $profesional = $this->obtenerProfesional($request);

        if ($profesional->fotos()->count() >= 30) {
            throw ValidationException::withMessages([
                'foto' => 'Ya has alcanzado el límite máximo de 30 fotos.',
            ]);
        }

        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $ruta = $request->file('foto')->store('portafolios', 'public');

        $orden = (int) $profesional->fotos()->max('orden') + 1;
        $foto = $profesional->fotos()->create([
            'ruta' => $ruta,
            'orden' => $orden,
        ]);

        return response()->json([
            'data' => [
                'id' => $foto->id,
                'url' => asset('storage/' . $foto->ruta),
            ],
        ], Response::HTTP_CREATED);
    }

    public function borrarFoto(Request $request, int $fotoId): Response
    {
        $profesional = $this->obtenerProfesional($request);
        $foto = $profesional->fotos()->where('id', $fotoId)->firstOrFail();

        Storage::disk('public')->delete($foto->ruta);
        $foto->delete();

        return response()->noContent();
    }

    private function obtenerProfesional(Request $request): Profesional
    {
        $user = $request->user();
        abort_unless($user->esProfesional(), Response::HTTP_FORBIDDEN, 'Solo profesionales pueden acceder.');

        return $user->profesional()->with(['user', 'zonas', 'servicios', 'fotos'])->firstOrFail();
    }
}
