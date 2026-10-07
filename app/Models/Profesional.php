<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// 'verificada' y los campos de calificación no son asignables en masa:
// los cambia solo el administrador o el sistema.
#[Fillable(['user_id', 'bio', 'anios_experiencia'])]
class Profesional extends Model
{
    protected $table = 'profesionales';

    protected function casts(): array
    {
        return [
            'verificada' => 'boolean',
            'calificacion_promedio' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function zonas(): BelongsToMany
    {
        return $this->belongsToMany(Zona::class, 'profesional_zona');
    }

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'servicio_profesional')
            ->withPivot('precio', 'activo')
            ->withTimestamps();
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(FotoPortafolio::class)->orderBy('orden');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class);
    }

    #[Scope]
    protected function verificadas(Builder $query): void
    {
        $query->where('verificada', true);
    }

    /** Recalcula el promedio a partir de las calificaciones guardadas. */
    public function recalcularCalificacion(): void
    {
        $this->forceFill([
            'calificacion_promedio' => round((float) $this->calificaciones()->avg('estrellas'), 2),
            'total_calificaciones' => $this->calificaciones()->count(),
        ])->save();
    }
}
