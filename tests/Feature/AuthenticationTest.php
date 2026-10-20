<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat user owner pengujian jika belum ada
        User::updateOrCreate(
            ['username' => 'owner'],
            [
                'name' => 'Owner Pangeran Condet',
                'email' => 'owner@pangerancondet.com',
                'password' => Hash::make('owner123'),
                'role' => 'owner',
            ]
        );
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $responseHome = $this->get('/');
        $responseHome->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_when_accessing_raw_materials(): void
    {
        $response = $this->get('/raw-materials');
        $response->assertRedirect('/login');
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Username atau Email');
        $response->assertSee('Kata Sandi');
    }

    public function test_users_can_authenticate_using_username(): void
    {
        $response = $this->post('/login', [
            'username' => 'owner',
            'password' => 'owner123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_users_can_authenticate_using_email(): void
    {
        $response = $this->post('/login', [
            'username' => 'owner@pangerancondet.com',
            'password' => 'owner123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'username' => 'owner',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('username');
    }

    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $user = User::where('username', 'owner')->first();

        $response = $this->actingAs($user)->get('/login');
        $response->assertRedirect('/dashboard');
    }

    public function test_users_can_logout(): void
    {
        $user = User::where('username', 'owner')->first();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'username' => 'owner',
                'password' => 'wrong-password',
            ]);
        }

        // Percobaan ke-6 harus ditolak oleh Rate Limiter
        $response = $this->post('/login', [
            'username' => 'owner',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertStringContainsString('Terlalu banyak percobaan masuk', session('errors')->first('username'));
    }
}
