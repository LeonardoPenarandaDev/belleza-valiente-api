<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoriaResource;
use App\Http\Resources\ZonaResource;
use App\Models\Categoria;
use App\Models\Zona;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogoController extends Controller
{
    public function categorias(): AnonymousResourceCollection
    {
        $categorias = Categoria::with(['servicios' => function ($q) {
            $q->where('activo', true);
        }])->orderBy('orden')->get();

        return CategoriaResource::collection($categorias);
    }

    public function zonas(): AnonymousResourceCollection
    {
        $zonas = Zona::where('activa', true)->get();

        return ZonaResource::collection($zonas);
    }
}
