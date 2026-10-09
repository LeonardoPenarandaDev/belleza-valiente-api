<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_sin_sesion_redirige_al_login_del_panel(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_el_admin_entra_al_panel(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin')
            ->assertOk();
    }

    public function test_clienta_y_profesional_no_entran_al_panel(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
        $this->actingAs(User::factory()->profesional()->create())->get('/admin')->assertForbidden();
    }
}
