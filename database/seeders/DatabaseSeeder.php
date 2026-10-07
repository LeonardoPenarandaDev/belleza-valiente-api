<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ZonaSeeder::class,
            CatalogoSeeder::class,
        ]);

        // Usuarios de prueba solo en desarrollo, nunca en el VPS.
        if (app()->environment('local')) {
            $this->call(DemoSeeder::class);
        }
    }
}
