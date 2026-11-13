<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PerfilProfesionalResource;
use App\Http\Resources\ProfesionalResumenResource;
use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProfesionalController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Profesional::with(['user', 'zonas', 'servicios', 'fotos'])
            ->where('verificada', true)
            ->where('activa', true);

        if ($request->filled('zona_id')) {
            $zonaId = (int) $request->input('zona_id');
            $query->whereHas('zonas', fn ($q) => $q->where('zonas.id', $zonaId));
        }

        if ($request->filled('servicio_id')) {
            $servicioId = (int) $request->input('servicio_id');
            $query->whereHas('servicios', function ($q) use ($servicioId) {
                $q->where('servicios.id', $servicioId)
                  ->where('servicio_profesional.activo', true);
            });
        }

        $profesionales = $query->orderByDesc('calificacion_promedio')
            ->paginate(20);

        return ProfesionalResumenResource::collection($profesionales);
    }

    public function show(int $id): PerfilProfesionalResource
    {
        $profesional = Profesional::with(['user', 'zonas', 'servicios', 'fotos', 'calificaciones'])
            ->findOrFail($id);

        return new PerfilProfesionalResource($profesional);
    }
}
