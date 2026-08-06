<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Testes automatizados cobrindo a criacao de usuarios via Artisan CLI
class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_criar_usuario_via_comando_artisan(): void
    {
        $this->artisan('user:create', [
            'email' => 'novo.usuario@teste.com',
            'name' => 'Novo Usuário',
            'password' => 'senha123456'
        ])
        ->expectsOutput('Usuário novo.usuario@teste.com criado com sucesso! (ID: 1)')
        ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'novo.usuario@teste.com',
            'name' => 'Novo Usuário',
        ]);
    }

    public function test_nao_permite_criar_usuario_com_email_duplicado(): void
    {
        User::factory()->create(['email' => 'existente@teste.com']);

        $this->artisan('user:create', [
            'email' => 'existente@teste.com',
            'name' => 'Outro Nome',
            'password' => 'senha123456'
        ])
        ->expectsOutput('The email has already been taken.')
        ->assertExitCode(1);
    }
}
