<?php

namespace Tests\Feature;

use App\Enums\EstadoReserva;
use App\Models\Categoria;
use App\Models\Profesional;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\User;
use App\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EliminarCuentaTest extends TestCase
{
    use RefreshDatabase;

    private Servicio $servicio;

    private Zona $zona;

    protected function setUp(): void
    {
        parent::setUp();

        $categoria = Categoria::create(['nombre' => 'Uñas', 'slug' => 'unas']);
        $this->servicio = Servicio::create(['categoria_id' => $categoria->id, 'nombre' => 'Manicure']);
        $this->zona = Zona::create(['nombre' => 'Centro']);
    }

    private function reserva(User $clienta, Profesional $profesional, EstadoReserva $estado): Reserva
    {
        $reserva = Reserva::create([
            'cliente_id' => $clienta->id,
            'profesional_id' => $profesional->id,
            'servicio_id' => $this->servicio->id,
            'zona_id' => $this->zona->id,
            'fecha_hora' => now()->addDay(),
            'duracion_minutos' => 60,
            'precio' => 30000,
            'direccion' => 'Calle 10 # 5-20',
            'notas' => 'Timbre dañado',
        ]);
        $reserva->forceFill(['estado' => $estado])->save();

        return $reserva;
    }

    private function profesional(): Profesional
    {
        $profesional = Profesional::create(['user_id' => User::factory()->profesional()->create()->id, 'bio' => 'Manicurista']);
        $profesional->forceFill(['verificada' => true])->save();

        return $profesional;
    }

    public function test_clienta_elimina_su_cuenta(): void
    {
        $clienta = User::factory()->create(['email' => 'ana@ejemplo.com']);
        $abierta = $this->reserva($clienta, $this->profesional(), EstadoReserva::Solicitada);
        $completada = $this->reserva($clienta, $this->profesional(), EstadoReserva::Completada);
        $token = $clienta->createToken('app')->plainTextToken;

        $this->withToken($token)->deleteJson('/api/v1/me', ['password' => 'password'])->assertNoContent();

        $clienta->refresh();
        $this->assertTrue($clienta->estaEliminada());
        $this->assertSame('Cuenta eliminada', $clienta->name);
        $this->assertNull($clienta->telefono);
        $this->assertNotSame('ana@ejemplo.com', $clienta->email);
        $this->assertSame(0, $clienta->tokens()->count());

        $this->assertSame(EstadoReserva::Cancelada, $abierta->fresh()->estado);
        $this->assertSame(EstadoReserva::Completada, $completada->fresh()->estado);
        $this->assertSame('Eliminada', $completada->fresh()->direccion);
        $this->assertNull($completada->fresh()->notas);

        // Ya no puede iniciar sesión ni con el correo ni con la contraseña anteriores.
        $this->postJson('/api/v1/auth/login', ['email' => 'ana@ejemplo.com', 'password' => 'password'])
            ->assertUnprocessable();
    }

    public function test_el_correo_queda_libre_para_registrarse_de_nuevo(): void
    {
        $clienta = User::factory()->create(['email' => 'ana@ejemplo.com']);

        $this->actingAs($clienta)->deleteJson('/api/v1/me', ['password' => 'password'])->assertNoContent();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Ana',
            'email' => 'ana@ejemplo.com',
            'telefono' => '3001234567',
            'password' => 'secreta123',
            'acepta_terminos' => true,
        ])->assertCreated();
    }

    public function test_exige_la_contrasena_correcta(): void
    {
        $clienta = User::factory()->create();

        $this->actingAs($clienta)->deleteJson('/api/v1/me', ['password' => 'otra'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');

        $this->assertFalse($clienta->fresh()->estaEliminada());
    }

    public function test_profesional_elimina_su_cuenta(): void
    {
        Storage::fake('public');
        $profesional = $this->profesional();
        $ruta = UploadedFile::fake()->image('foto.jpg')->store('portafolio', 'public');
        $profesional->fotos()->create(['ruta' => $ruta]);
        $profesional->zonas()->attach($this->zona);
        $aceptada = $this->reserva(User::factory()->create(), $profesional, EstadoReserva::Aceptada);

        $this->actingAs($profesional->user)->deleteJson('/api/v1/me', ['password' => 'password'])->assertNoContent();

        $profesional->refresh();
        $this->assertFalse($profesional->verificada);
        $this->assertNull($profesional->bio);
        $this->assertSame(0, $profesional->fotos()->count());
        $this->assertSame(0, $profesional->zonas()->count());
        $this->assertSame(EstadoReserva::Cancelada, $aceptada->fresh()->estado);
        Storage::disk('public')->assertMissing($ruta);
    }

    public function test_admin_no_puede_eliminarse_desde_la_app(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->deleteJson('/api/v1/me', ['password' => 'password'])->assertForbidden();

        $this->assertFalse($admin->fresh()->estaEliminada());
    }
}
