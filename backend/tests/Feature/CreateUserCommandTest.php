<?php

namespace Tests\Feature;

require_once __DIR__ . '/../TestCase.php';

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_usuario_via_comando_artisan(): void
    {
        $this->artisan('user:create', [
            'email' => 'novo@teste.com',
            'name' => 'Novo Usuário',
            'password' => 'senha123',
        ])
        ->expectsOutput("Usuário 'Novo Usuário' (novo@teste.com) criado com sucesso!")
        ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'novo@teste.com',
            'name' => 'Novo Usuário',
        ]);
    }
}
