<?php

namespace App\Enums;

/** En el MVP el pago se hace por fuera de la app; la profesional registra cuál se usó. */
enum MetodoPago: string
{
    case Efectivo = 'efectivo';
    case Nequi = 'nequi';
    case Transferencia = 'transferencia';
    case Otro = 'otro';
}
