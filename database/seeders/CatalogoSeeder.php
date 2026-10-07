<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Servicio;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    /**
     * Portafolio inicial del documento del proyecto.
     *
     * Precios de referencia en pesos: los marcados con [maqueta] vienen de la maqueta
     * de la app; el resto son provisionales y deben confirmarse con la fundación.
     * Duraciones en minutos, también provisionales.
     */
    public function run(): void
    {
        $catalogo = [
            ['Cabello', 'cabello', [
                ['Corte y peinado', 60, 35000],          // [maqueta]
                ['Cepillado', 45, 30000],
                ['Planchado', 45, 30000],
                ['Tinte y mechas', 150, 80000],          // [maqueta]
                ['Tratamiento capilar', 60, 50000],
                ['Peinado para evento', 60, 60000],
            ]],
            ['Uñas', 'unas', [
                ['Manicura tradicional', 45, 25000],     // [maqueta]
                ['Pedicura tradicional', 60, 30000],
                ['Semipermanente', 60, 40000],
                ['Uñas en gel', 90, 45000],              // [maqueta]
                ['Uñas acrílicas', 120, 70000],
                ['Nail art', 30, 10000],
            ]],
            ['Barbería', 'barberia', [
                ['Corte de cabello hombre', 40, 25000],  // [maqueta]
                ['Arreglo de barba', 20, 15000],
                ['Diseño y afeitado', 30, 20000],
                ['Barbería completa', 60, 40000],        // [maqueta]
            ]],
            ['Rostro', 'rostro', [
                ['Diseño de cejas', 30, 20000],
                ['Pestañas', 90, 80000],
                ['Depilación facial', 20, 15000],
                ['Limpieza facial', 60, 60000],
            ]],
            ['Maquillaje', 'maquillaje', [
                ['Maquillaje social', 60, 70000],
                ['Maquillaje para eventos', 75, 90000],
                ['Maquillaje de quinceañera', 90, 120000],
                ['Maquillaje de novia', 120, 200000],
                ['Maquillaje para fotografía', 90, 100000],
            ]],
        ];

        foreach ($catalogo as $orden => [$nombre, $slug, $servicios]) {
            $categoria = Categoria::updateOrCreate(
                ['slug' => $slug],
                ['nombre' => $nombre, 'icono' => $slug, 'orden' => $orden + 1],
            );

            foreach ($servicios as [$servicio, $duracion, $precio]) {
                Servicio::updateOrCreate(
                    ['categoria_id' => $categoria->id, 'nombre' => $servicio],
                    ['duracion_minutos' => $duracion, 'precio_referencia' => $precio, 'activo' => true],
                );
            }
        }
    }
}
