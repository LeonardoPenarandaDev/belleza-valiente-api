<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'slug', 'icono', 'orden'])]
class Categoria extends Model
{
    protected $table = 'categorias';

    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class);
    }
}
