<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleAndPermissionSeeder::class, UserSeeder::class]);
    }

    public function test_quick_login_as_admin_logs_user_in_and_redirects_to_dashboard(): void
    {
        $response = $this->post(route('demo.login', 'admin'));

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        /** @var User $user */
        $user = auth()->user();
        $this->assertSame('admin@its.ac.id', $user->email);
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_quick_login_as_staf_redirects_to_workspace(): void
    {
        $response = $this->post(route('demo.login', 'staf'));

        $response->assertRedirect(route('admin.tugas.index'));
        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->hasRole('staf'));
    }

    public function test_unknown_panel_returns_not_found(): void
    {
        $this->post(route('demo.login', ['panel' => 'mahasiswa']))
            ->assertNotFound();

        $this->assertGuest();
    }

    public function test_quick_login_can_be_disabled(): void
    {
        config(['demo.enabled' => false]);

        $this->post(route('demo.login', 'admin'))->assertNotFound();

        $this->assertGuest();
    }

    public function test_authenticated_user_cannot_reuse_demo_login_route(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $staf = User::query()->where('email', 'staf@its.ac.id')->firstOrFail();

        // Rute demo ada di dalam grup "guest", sehingga user yang sudah login dialihkan.
        $this->actingAs($staf)
            ->post(route('demo.login', 'admin'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_login_page_shows_quick_login_panel(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Quick Login Demo')
            ->assertSee('Login sebagai Admin')
            ->assertSee('Login sebagai Staf');
    }
}
