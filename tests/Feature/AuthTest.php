<?php

namespace Tests\Feature;

use App\Enums\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function datosRegistro(array $cambios = []): array
    {
        return array_merge([
            'name' => 'Ana Pérez',
            'email' => 'Ana@Ejemplo.com ',
            'telefono' => '300 123 4567',
            'password' => 'secreta123',
            'acepta_terminos' => true,
        ], $cambios);
    }

    public function test_registro_crea_una_clienta_y_devuelve_token(): void
    {
        $respuesta = $this->postJson('/api/v1/auth/register', $this->datosRegistro());

        $respuesta->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'telefono', 'rol']])
            ->assertJsonPath('user.rol', 'cliente')
            ->assertJsonPath('user.email', 'ana@ejemplo.com')
            ->assertJsonPath('user.telefono', '573001234567');

        $user = User::where('email', 'ana@ejemplo.com')->first();
        $this->assertSame(Rol::Cliente, $user->rol);
        $this->assertNotNull($user->terminos_aceptados_at);
    }

    public function test_registro_ignora_el_rol_enviado(): void
    {
        $this->postJson('/api/v1/auth/register', $this->datosRegistro(['rol' => 'admin']))
            ->assertCreated()
            ->assertJsonPath('user.rol', 'cliente');
    }

    public function test_registro_exige_aceptar_terminos(): void
    {
        $this->postJson('/api/v1/auth/register', $this->datosRegistro(['acepta_terminos' => false]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('acepta_terminos');
    }

    public function test_registro_valida_telefono_y_correo_repetido(): void
    {
        User::factory()->create(['email' => 'ana@ejemplo.com']);

        $this->postJson('/api/v1/auth/register', $this->datosRegistro(['telefono' => '12345']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['telefono', 'email']);
    }

    public function test_login_con_credenciales_correctas(): void
    {
        $user = User::factory()->create(['email' => 'ana@ejemplo.com']);

        $this->postJson('/api/v1/auth/login', ['email' => 'ANA@ejemplo.com', 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonStructure(['token']);
    }

    public function test_login_con_contrasena_incorrecta(): void
    {
        User::factory()->create(['email' => 'ana@ejemplo.com']);

        $this->postJson('/api/v1/auth/login', ['email' => 'ana@ejemplo.com', 'password' => 'otra'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_login_se_bloquea_tras_cinco_intentos(): void
    {
        User::factory()->create(['email' => 'ana@ejemplo.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', ['email' => 'ana@ejemplo.com', 'password' => 'otra']);
        }

        $this->postJson('/api/v1/auth/login', ['email' => 'ana@ejemplo.com', 'password' => 'password'])
            ->assertTooManyRequests();
    }

    public function test_me_devuelve_la_usuaria_autenticada(): void
    {
        $token = User::factory()->create()->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.rol', 'cliente');
    }

    public function test_me_sin_token_responde_401(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_logout_revoca_el_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertNoContent();

        $this->assertSame(0, $user->tokens()->count());
    }
}
