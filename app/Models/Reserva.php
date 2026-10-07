<?php

namespace App\Models;

use App\Enums\EstadoReserva;
use App\Enums\MetodoPago;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

// Estado, pago, comisión y fechas de cambio no son asignables en masa:
// solo se modifican desde la lógica de cambios de estado en el servidor.
#[Fillable([
    'cliente_id', 'profesional_id', 'servicio_id', 'zona_id',
    'fecha_hora', 'duracion_minutos', 'precio', 'direccion', 'notas',
])]
class Reserva extends Model
{
    protected $table = 'reservas';

    protected $attributes = [
        'estado' => 'solicitada',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'estado' => EstadoReserva::class,
            'metodo_pago' => MetodoPago::class,
            'aceptada_at' => 'datetime',
            'completada_at' => 'datetime',
            'cancelada_at' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }

    public function calificacion(): HasOne
    {
        return $this->hasOne(Calificacion::class);
    }

    /** Hora en que termina la cita. */
    public function fechaHoraFin(): \Carbon\CarbonInterface
    {
        return $this->fecha_hora->copy()->addMinutes($this->duracion_minutos);
    }
}
