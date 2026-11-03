<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class AuthenticationAndRoleTest extends TestCase
{
    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Username');
        $response->assertSee('Password');
        $response->assertSee('Ingat Saya');
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['username']);
        $this->assertGuest();
    }

    public function test_admin_can_login_and_access_dashboard_and_admin_routes(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $admin = User::where('username', 'admin')->first();

        // Akses dashboard
        $dashResponse = $this->actingAs($admin)->get('/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Total Unit Aset');
        $dashResponse->assertSee('Overdue Alert');

        // Akses rute admin
        $adminRouteResponse = $this->actingAs($admin)->get('/admin/test-access');
        $adminRouteResponse->assertStatus(200);

        // Akses rute khusus pegawai oleh admin harus ditolak 403
        $pegawaiRouteResponse = $this->actingAs($admin)->get('/pegawai/test-access');
        $pegawaiRouteResponse->assertStatus(403);
    }

    public function test_pegawai_cannot_access_admin_routes(): void
    {
        $pegawai = User::where('username', 'pegawai')->first();

        // Akses dashboard pegawai
        $dashResponse = $this->actingAs($pegawai)->get('/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Pinjaman Aktif Saya');

        // Pegawai dilarang mengakses rute admin (403 Forbidden)
        $adminRouteResponse = $this->actingAs($pegawai)->get('/admin/test-access');
        $adminRouteResponse->assertStatus(403);

        // Pegawai diizinkan mengakses rute pegawai (200 OK)
        $pegawaiRouteResponse = $this->actingAs($pegawai)->get('/pegawai/test-access');
        $pegawaiRouteResponse->assertStatus(200);
    }

    public function test_user_can_logout(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}

