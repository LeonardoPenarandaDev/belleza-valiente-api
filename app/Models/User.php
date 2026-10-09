<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Rol;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// 'rol' no es asignable en masa: lo fija el registro o el administrador, nunca la petición.
#[Fillable(['name', 'email', 'telefono', 'password', 'terminos_aceptados_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'terminos_aceptados_at' => 'datetime',
            'eliminada_at' => 'datetime',
            'password' => 'hashed',
            'rol' => Rol::class,
        ];
    }

    public function profesional(): HasOne
    {
        return $this->hasOne(Profesional::class);
    }

    /** Citas que pidió como clienta. */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'cliente_id');
    }

    public function esCliente(): bool
    {
        return $this->rol === Rol::Cliente;
    }

    public function esProfesional(): bool
    {
        return $this->rol === Rol::Profesional;
    }

    public function esAdmin(): bool
    {
        return $this->rol === Rol::Admin;
    }

    public function estaEliminada(): bool
    {
        return $this->eliminada_at !== null;
    }

    /** Solo el admin entra al panel de Filament. */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->esAdmin() && ! $this->estaEliminada();
    }
}
