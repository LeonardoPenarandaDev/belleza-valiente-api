<?php

namespace Database\Seeders;

use App\Models\Zona;
use Illuminate\Database\Seeder;

class ZonaSeeder extends Seeder
{
    /**
     * Zonas provisionales del área metropolitana de Cúcuta.
     * PENDIENTE: reemplazar por la lista definitiva que entregue la fundación.
     */
    public function run(): void
    {
        $zonas = [
            'Centro',
            'Caobos',
            'La Riviera',
            'Prados del Este',
            'Quinta Oriental',
            'La Libertad',
            'Atalaya',
            'Los Patios',
            'Villa del Rosario',
        ];

        foreach ($zonas as $nombre) {
            Zona::updateOrCreate(['nombre' => $nombre], ['activa' => true]);
        }
    }
}
