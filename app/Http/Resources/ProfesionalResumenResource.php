<?php

namespace App\Http\Resources;

use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Profesional */
class ProfesionalResumenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $fotoUrl = $this->user?->foto_perfil_url;
        if (!$fotoUrl && $this->fotos && $this->fotos->isNotEmpty()) {
            $foto = $this->fotos->first();
            $fotoUrl = str_starts_with($foto->ruta, 'http') ? $foto->ruta : asset('storage/' . $foto->ruta);
        }

        $precio = null;
        if (isset($this->precio_servicio)) {
            $precio = (int) $this->precio_servicio;
        } else {
            $servicioId = $request->query('servicio_id') ?? $request->input('servicio_id') ?? request('servicio_id');
            if ($servicioId) {
                $serv = $this->servicios->firstWhere('id', (int) $servicioId);
                if ($serv && $serv->pivot) {
                    $precio = (int) $serv->pivot->precio;
                }
            }
        }

        return [
            'id' => $this->id,
            'nombre' => $this->user->name,
            'bio' => $this->bio,
            'anios_experiencia' => $this->anios_experiencia,
            'calificacion_promedio' => (float) $this->calificacion_promedio,
            'total_calificaciones' => (int) $this->total_calificaciones,
            'foto_principal' => $fotoUrl,
            'precio' => $precio,
            'zonas' => ZonaResource::collection($this->whenLoaded('zonas')),
        ];
    }
}
