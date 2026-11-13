<?php

namespace App\Http\Resources;

use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Servicio */
class ServicioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'categoria_id' => $this->categoria_id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'duracion_minutos' => $this->duracion_minutos,
            'precio_referencia' => $this->precio_referencia,
        ];
    }
}
