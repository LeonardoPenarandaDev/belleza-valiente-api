<?php

namespace App\Http\Resources;

use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Profesional */
class PerfilPropioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->user->name,
            'foto_perfil' => $this->user?->foto_perfil_url,
            'bio' => $this->bio,
            'anios_experiencia' => $this->anios_experiencia,
            'verificada' => (bool) $this->verificada,
            'activa' => (bool) $this->activa,
            'calificacion_promedio' => (float) $this->calificacion_promedio,
            'total_calificaciones' => (int) $this->total_calificaciones,
            'zonas' => ZonaResource::collection($this->zonas),
            'servicios' => $this->servicios->map(function ($s) {
                return [
                    'servicio_id' => $s->id,
                    'nombre' => $s->nombre,
                    'precio' => (int) $s->pivot->precio,
                    'activo' => (bool) $s->pivot->activo,
                ];
            })->values(),
            'fotos' => $this->fotos->map(function ($f) {
                return [
                    'id' => $f->id,
                    'url' => str_starts_with($f->ruta, 'http') ? $f->ruta : asset('storage/' . $f->ruta),
                ];
            })->values(),
        ];
    }
}
