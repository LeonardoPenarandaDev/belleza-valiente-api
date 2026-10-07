<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['profesional_id', 'ruta', 'orden'])]
class FotoPortafolio extends Model
{
    protected $table = 'fotos_portafolio';

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class);
    }
}
