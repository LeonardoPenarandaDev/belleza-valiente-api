<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'foto_perfil' => $this->foto_perfil_url,
            'rol' => $this->rol instanceof \BackedEnum ? $this->rol->value : (string) $this->rol,
            'profesional_id' => $this->when($this->esProfesional(), fn () => $this->profesional?->id),
            'terminos_aceptados_at' => $this->terminos_aceptados_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
