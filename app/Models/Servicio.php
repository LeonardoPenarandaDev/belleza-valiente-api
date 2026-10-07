<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['categoria_id', 'nombre', 'descripcion', 'duracion_minutos', 'precio_referencia', 'activo'])]
class Servicio extends Model
{
    protected $table = 'servicios';

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function profesionales(): BelongsToMany
    {
        return $this->belongsToMany(Profesional::class, 'servicio_profesional')
            ->withPivot('precio', 'activo')
            ->withTimestamps();
    }

    #[Scope]
    protected function activos(Builder $query): void
    {
        $query->where('activo', true);
    }
}
