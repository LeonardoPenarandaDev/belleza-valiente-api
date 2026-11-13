<?php

namespace App\Services;

use App\Enums\EstadoReserva;
use App\Enums\MetodoPago;
use App\Models\Calificacion;
use App\Models\Profesional;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservaService
{
    /**
     * Clienta solicita una nueva cita.
     */
    public function pedir(User $cliente, array $datos): Reserva
    {
        $profesional = Profesional::with('servicios')->findOrFail($datos['profesional_id']);
        $servicioPivot = $profesional->servicios()->where('servicio_id', $datos['servicio_id'])->first();

        if (!$servicioPivot || !$servicioPivot->pivot->activo) {
            throw ValidationException::withMessages([
                'servicio_id' => 'La profesional no ofrece este servicio actualmente.',
            ]);
        }

        $servicio = Servicio::findOrFail($datos['servicio_id']);
        $fechaHora = Carbon::parse($datos['fecha_hora']);

        if ($fechaHora->isPast()) {
            throw ValidationException::withMessages([
                'fecha_hora' => 'La fecha de la cita debe ser futura.',
            ]);
        }

        return Reserva::create([
            'cliente_id' => $cliente->id,
            'profesional_id' => $profesional->id,
            'servicio_id' => $servicio->id,
            'zona_id' => $datos['zona_id'],
            'fecha_hora' => $fechaHora,
            'duracion_minutos' => $servicio->duracion_minutos,
            'precio' => $servicioPivot->pivot->precio,
            'direccion' => $datos['direccion'],
            'notas' => $datos['notas'] ?? null,
            'estado' => EstadoReserva::Solicitada,
        ]);
    }

    /**
     * Profesional acepta una cita solicitada (valida que no haya cruce de horarios).
     */
    public function aceptar(Reserva $reserva, Profesional $profesional): Reserva
    {
        abort_if($reserva->profesional_id !== $profesional->id, 403, 'No tienes permiso sobre esta reserva.');

        if ($reserva->estado !== EstadoReserva::Solicitada) {
            throw ValidationException::withMessages([
                'reserva' => 'Solo se pueden aceptar citas en estado solicitada.',
            ]);
        }

        if ($reserva->fecha_hora->isPast()) {
            throw ValidationException::withMessages([
                'reserva' => 'No se puede aceptar una cita cuya fecha ya pasó.',
            ]);
        }

        return DB::transaction(function () use ($reserva, $profesional) {
            $inicio = $reserva->fecha_hora;
            $fin = $reserva->fechaHoraFin();

            // Bloqueamos y verificamos si hay cruces con citas aceptadas
            $hayCruce = Reserva::where('profesional_id', $profesional->id)
                ->where('id', '!=', $reserva->id)
                ->where('estado', EstadoReserva::Aceptada)
                ->where(function ($query) use ($inicio, $fin) {
                    $query->where('fecha_hora', '<', $fin)
                        ->whereRaw('DATE_ADD(fecha_hora, INTERVAL duracion_minutos MINUTE) > ?', [$inicio]);
                })
                ->lockForUpdate()
                ->exists();

            if ($hayCruce) {
                throw ValidationException::withMessages([
                    'reserva' => 'Ya tienes otra cita aceptada que se cruza con este horario.',
                ]);
            }

            $reserva->update([
                'estado' => EstadoReserva::Aceptada,
                'aceptada_at' => now(),
            ]);

            return $reserva->fresh();
        });
    }

    /**
     * Profesional rechaza una cita solicitada.
     */
    public function rechazar(Reserva $reserva, Profesional $profesional, ?string $motivo = null): Reserva
    {
        abort_if($reserva->profesional_id !== $profesional->id, 403, 'No tienes permiso sobre esta reserva.');

        if ($reserva->estado !== EstadoReserva::Solicitada) {
            throw ValidationException::withMessages([
                'reserva' => 'Solo se pueden rechazar citas en estado solicitada.',
            ]);
        }

        $reserva->update([
            'estado' => EstadoReserva::Rechazada,
            'motivo_rechazo' => $motivo,
        ]);

        return $reserva->fresh();
    }

    /**
     * Clienta o profesional cancela una cita.
     */
    public function cancelar(Reserva $reserva, User $user, ?string $motivo = null): Reserva
    {
        $esCliente = $user->id === $reserva->cliente_id;
        $esProfesional = $user->profesional && $user->profesional->id === $reserva->profesional_id;

        abort_unless($esCliente || $esProfesional, 403, 'No tienes permiso sobre esta reserva.');

        if (!in_array($reserva->estado, [EstadoReserva::Solicitada, EstadoReserva::Aceptada], true)) {
            throw ValidationException::withMessages([
                'reserva' => 'Esta cita ya no se puede cancelar.',
            ]);
        }

        $reserva->update([
            'estado' => EstadoReserva::Cancelada,
            'cancelada_at' => now(),
            'cancelada_por' => $esCliente ? 'cliente' : 'profesional',
            'motivo_rechazo' => $motivo,
        ]);

        return $reserva->fresh();
    }

    /**
     * Profesional completa la cita y registra método y valor de pago.
     */
    public function completar(Reserva $reserva, Profesional $profesional, MetodoPago $metodo, int $valor): Reserva
    {
        abort_if($reserva->profesional_id !== $profesional->id, 403, 'No tienes permiso sobre esta reserva.');

        if ($reserva->estado !== EstadoReserva::Aceptada) {
            throw ValidationException::withMessages([
                'reserva' => 'Solo se pueden completar citas previamente aceptadas.',
            ]);
        }

        $comisionPorcentaje = config('belleza.comision_porcentaje', 15);
        $comision = (int) round(($valor * $comisionPorcentaje) / 100);

        $reserva->update([
            'estado' => EstadoReserva::Completada,
            'completada_at' => now(),
            'metodo_pago' => $metodo,
            'valor_pagado' => $valor,
            'comision' => $comision,
        ]);

        return $reserva->fresh();
    }

    /**
     * Clienta califica el servicio prestado.
     */
    public function calificar(Reserva $reserva, User $cliente, int $estrellas, ?string $comentario = null): Calificacion
    {
        abort_if($reserva->cliente_id !== $cliente->id, 403, 'No tienes permiso para calificar esta reserva.');

        if ($reserva->estado !== EstadoReserva::Completada) {
            throw ValidationException::withMessages([
                'reserva' => 'Solo se pueden calificar citas completadas.',
            ]);
        }

        if ($reserva->calificacion()->exists()) {
            throw ValidationException::withMessages([
                'reserva' => 'Esta cita ya fue calificada previamente.',
            ]);
        }

        return DB::transaction(function () use ($reserva, $cliente, $estrellas, $comentario) {
            $calificacion = Calificacion::create([
                'reserva_id' => $reserva->id,
                'cliente_id' => $cliente->id,
                'profesional_id' => $reserva->profesional_id,
                'estrellas' => $estrellas,
                'comentario' => $comentario,
            ]);

            $reserva->profesional->recalcularCalificacion();

            return $calificacion;
        });
    }
}
