<?php

namespace App\Services;

use App\Enums\EstadoReserva;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CuentaService
{
    /**
     * Elimina la cuenta de una clienta o profesional (Ley 1581 y Google Play).
     *
     * La fila de `users` no se borra porque las reservas la referencian: se
     * anonimiza, se cancelan las citas abiertas y se revocan todos los tokens.
     * Las citas se conservan sin datos personales para los indicadores.
     */
    public function eliminar(User $user): void
    {
        $rutasFotos = [];

        DB::transaction(function () use ($user, &$rutasFotos) {
            $this->cancelarAbiertas(Reserva::where('cliente_id', $user->id));

            Reserva::where('cliente_id', $user->id)->update([
                'direccion' => 'Eliminada',
                'notas' => null,
            ]);

            if ($profesional = $user->profesional) {
                $this->cancelarAbiertas($profesional->reservas());

                $rutasFotos = $profesional->fotos()->pluck('ruta')->all();
                $profesional->fotos()->delete();
                $profesional->zonas()->detach();
                $profesional->servicios()->detach();
                $profesional->forceFill(['bio' => null, 'verificada' => false])->save();
            }

            $user->tokens()->delete();

            $user->forceFill([
                'name' => 'Cuenta eliminada',
                'email' => "eliminada-{$user->id}@eliminada.invalid",
                'telefono' => null,
                'password' => Hash::make(Str::random(64)),
                'remember_token' => null,
                'eliminada_at' => now(),
            ])->save();
        });

        // Los archivos se borran después de confirmar la transacción.
        Storage::disk('public')->delete($rutasFotos);
    }

    private function cancelarAbiertas(Builder|HasMany $query): void
    {
        $query->whereIn('estado', [EstadoReserva::Solicitada, EstadoReserva::Aceptada])
            ->update([
                'estado' => EstadoReserva::Cancelada,
                'cancelada_at' => now(),
            ]);
    }
}
