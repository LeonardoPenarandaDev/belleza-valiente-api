<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nombre', 'activa'])]
class Zona extends Model
{
    protected $table = 'zonas';

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
        ];
    }

    public function profesionales(): BelongsToMany
    {
        return $this->belongsToMany(Profesional::class, 'profesional_zona');
    }

    #[Scope]
    protected function activas(Builder $query): void
    {
        $query->where('activa', true);
    }
}
