<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['reserva_id', 'cliente_id', 'profesional_id', 'estrellas', 'comentario'])]
class Calificacion extends Model
{
    protected $table = 'calificaciones';

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class);
    }
}
