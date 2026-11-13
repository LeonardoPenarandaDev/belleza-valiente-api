<?php

namespace App\Http\Resources;

use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Profesional */
class PerfilProfesionalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->user->name,
            'foto_perfil' => $this->user?->foto_perfil_url,
            'bio' => $this->bio,
            'anios_experiencia' => $this->anios_experiencia,
            'calificacion_promedio' => (float) $this->calificacion_promedio,
            'total_calificaciones' => (int) $this->total_calificaciones,
            'zonas' => ZonaResource::collection($this->zonas),
            'servicios' => $this->servicios->where('pivot.activo', true)->map(function ($s) {
                return [
                    'servicio_id' => $s->id,
                    'categoria_id' => $s->categoria_id,
                    'nombre' => $s->nombre,
                    'duracion_minutos' => $s->duracion_minutos,
                    'precio' => (int) $s->pivot->precio,
                ];
            })->values(),
            'fotos' => $this->fotos->map(function ($f) {
                return [
                    'id' => $f->id,
                    'url' => str_starts_with($f->ruta, 'http') ? $f->ruta : asset('storage/' . $f->ruta),
                ];
            })->values(),
            'calificaciones' => $this->calificaciones->map(function ($c) {
                return [
                    'estrellas' => (int) $c->estrellas,
                    'comentario' => $c->comentario,
                    'fecha' => $c->created_at->toIso8601String(),
                ];
            })->values(),
        ];
    }
}
