<?php

namespace App\Enums;

enum Rol: string
{
    case Cliente = 'cliente';
    case Profesional = 'profesional';
    case Admin = 'admin';
}
