<?php

namespace App\Models;

use App\Enums\Rol;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'telefono', 'foto_perfil', 'password', 'terminos_aceptados_at', 'eliminada_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public function getFotoPerfilUrlAttribute(): ?string
    {
        if (!$this->foto_perfil) {
            return null;
        }
        return str_starts_with($this->foto_perfil, 'http')
            ? $this->foto_perfil
            : asset('storage/' . $this->foto_perfil);
    }

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
}
