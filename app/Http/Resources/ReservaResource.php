<?php

namespace App\Http\Resources;

use App\Enums\EstadoReserva;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Reserva */
class ReservaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $esClienta = $user && $user->id === $this->cliente_id;
        $esProfesional = $user && $user->profesional && $user->profesional->id === $this->profesional_id;

        $estadoStr = $this->estado instanceof \BackedEnum ? $this->estado->value : (string) $this->estado;
        $vencida = ($estadoStr === 'solicitada') && $this->fecha_hora->isPast();
        $verDatosContacto = in_array($estadoStr, ['aceptada', 'completada'], true);

        $calificacion = $this->relationLoaded('calificacion') ? $this->calificacion : $this->calificacion()->first();

        $acciones = [];
        if ($esProfesional) {
            if ($estadoStr === 'solicitada' && !$vencida) {
                $acciones[] = 'aceptar';
                $acciones[] = 'rechazar';
            }
            if ($estadoStr === 'aceptada') {
                $acciones[] = 'cancelar';
                $acciones[] = 'completar';
            }
        }

        if ($esClienta) {
            if (in_array($estadoStr, ['solicitada', 'aceptada'], true)) {
                $acciones[] = 'cancelar';
            }
            if ($estadoStr === 'completada' && !$calificacion) {
                $acciones[] = 'calificar';
            }
        }

        return [
            'id' => $this->id,
            'estado' => $estadoStr,
            'vencida' => $vencida,
            'fecha_hora' => $this->fecha_hora->toIso8601String(),
            'duracion_minutos' => (int) $this->duracion_minutos,
            'precio' => (int) $this->precio,
            'direccion' => ($esClienta || $verDatosContacto) ? $this->direccion : null,
            'notas' => $this->notas,
            'motivo' => $this->motivo_rechazo,
            'cancelada_por' => $this->cancelada_por,
            'servicio' => [
                'nombre' => $this->servicio->nombre,
            ],
            'zona' => [
                'nombre' => $this->zona->nombre,
            ],
            'profesional' => [
                'id' => $this->profesional_id,
                'nombre' => $this->profesional->user->name,
                'telefono' => ($esClienta && $verDatosContacto) ? $this->profesional->user->telefono : null,
                'foto_perfil' => $this->profesional->user->foto_perfil_url,
            ],
            'cliente' => [
                'nombre' => $this->cliente->name,
                'telefono' => ($esProfesional && $verDatosContacto) ? $this->cliente->telefono : null,
                'foto_perfil' => $this->cliente->foto_perfil_url,
            ],
            'metodo_pago' => $this->metodo_pago instanceof \BackedEnum ? $this->metodo_pago->value : $this->metodo_pago,
            'valor_pagado' => $this->valor_pagado !== null ? (int) $this->valor_pagado : null,
            'comision' => ($esProfesional && $this->comision !== null) ? (int) $this->comision : null,
            'calificacion' => $calificacion ? [
                'estrellas' => (int) $calificacion->estrellas,
                'comentario' => $calificacion->comentario,
            ] : null,
            'acciones' => $acciones,
        ];
    }
}
