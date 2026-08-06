<?php

namespace Tests\Feature;

require_once __DIR__ . '/../TestCase.php';

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_admin_com_sucesso(): void
    {
        User::create([
            'name' => 'Admin RHeserva',
            'email' => 'admin@rheserva.com',
            'password' => 'password123',
        ]);

        // Referer simula requisição vinda do frontend (exigido pelo EnsureFrontendRequestsAreStateful)
        $response = $this->withHeader('Referer', 'http://localhost:880')
                         ->postJson('/api/login', [
                             'email' => 'admin@rheserva.com',
                             'password' => 'password123',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'user']);
    }

    public function test_login_com_credenciais_invalidas(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalido@rheserva.com',
            'password' => 'errada',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }
}
