<?php

namespace Database\Seeders;

use App\Enums\Rol;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\User;
use App\Models\Zona;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Usuarios de prueba para desarrollo local. Contraseña de todos: "password".
     * DatabaseSeeder solo lo ejecuta en el entorno local.
     */
    public function run(): void
    {
        $this->usuario('admin@belleza.test', 'Administradora', Rol::Admin);
        $this->usuario('cliente@belleza.test', 'Clienta de prueba', Rol::Cliente);

        $this->profesional(
            'laura@belleza.test',
            'Laura Gómez',
            'Manicurista con 5 años de experiencia. Trabajo con productos de alta calidad.',
            5,
            ['Centro', 'Caobos', 'La Riviera'],
            ['Manicura tradicional' => 25000, 'Pedicura tradicional' => 30000, 'Uñas en gel' => 45000, 'Semipermanente' => 40000],
        );

        $this->profesional(
            'diana@belleza.test',
            'Diana Pérez',
            'Estilista y maquilladora para eventos, quinceañeras y novias.',
            8,
            ['Prados del Este', 'Quinta Oriental', 'Centro'],
            ['Corte y peinado' => 35000, 'Cepillado' => 30000, 'Maquillaje social' => 70000, 'Maquillaje de novia' => 200000],
        );
    }

    private function usuario(string $email, string $nombre, Rol $rol): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $nombre,
                'telefono' => '3000000000',
                'password' => 'password',
                'terminos_aceptados_at' => now(),
            ],
        );
        $user->forceFill(['rol' => $rol, 'email_verified_at' => now()])->save();

        return $user;
    }

    /**
     * @param  list<string>  $zonas
     * @param  array<string, int>  $servicios  nombre del servicio => precio
     */
    private function profesional(string $email, string $nombre, string $bio, int $anios, array $zonas, array $servicios): void
    {
        $user = $this->usuario($email, $nombre, Rol::Profesional);

        $profesional = Profesional::updateOrCreate(
            ['user_id' => $user->id],
            ['bio' => $bio, 'anios_experiencia' => $anios],
        );
        $profesional->forceFill(['verificada' => true])->save();

        $profesional->zonas()->sync(Zona::whereIn('nombre', $zonas)->pluck('id'));

        $precios = Servicio::whereIn('nombre', array_keys($servicios))->get()
            ->mapWithKeys(fn (Servicio $s) => [$s->id => ['precio' => $servicios[$s->nombre], 'activo' => true]]);
        $profesional->servicios()->sync($precios);
    }
}
