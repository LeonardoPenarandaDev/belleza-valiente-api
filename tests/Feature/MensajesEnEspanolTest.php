<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** La app muestra los mensajes de la API tal cual: tienen que llegar en español. */
class MensajesEnEspanolTest extends TestCase
{
    use RefreshDatabase;

    public function test_errores_de_validacion_en_espanol_con_nombres_legibles(): void
    {
        $this->postJson('/api/v1/auth/register', ['email' => 'no-es-correo', 'password' => 'corta'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.name.0', 'El campo nombre es obligatorio.')
            ->assertJsonPath('errors.email.0', 'El campo correo debe ser un correo válido.')
            ->assertJsonPath('errors.password.0', 'El campo contraseña debe tener al menos 8 caracteres.')
            ->assertJsonPath('message', fn (string $mensaje) => str_contains($mensaje, 'errores más'));
    }

    public function test_sin_sesion_responde_en_espanol(): void
    {
        $this->getJson('/api/v1/me')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Tu sesión expiró. Inicia sesión de nuevo.');
    }

    public function test_ruta_inexistente_no_revela_detalles(): void
    {
        $this->getJson('/api/v1/no-existe')
            ->assertNotFound()
            ->assertJsonPath('message', 'No encontrado.');
    }
}
