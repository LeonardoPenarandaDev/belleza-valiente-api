<?php

namespace App\Enums;

enum EstadoReserva: string
{
    case Solicitada = 'solicitada';
    case Aceptada = 'aceptada';
    case Rechazada = 'rechazada';
    case Completada = 'completada';
    case Cancelada = 'cancelada';

    /** Estados en los que la cita ocupa la agenda de la profesional. */
    public function ocupaAgenda(): bool
    {
        return $this === self::Aceptada;
    }

    /** Estados desde los que ya no se puede pasar a otro. */
    public function esFinal(): bool
    {
        return in_array($this, [self::Rechazada, self::Completada, self::Cancelada], true);
    }
}
