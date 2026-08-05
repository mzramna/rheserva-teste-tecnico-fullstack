<?php

namespace Tests\Feature;

require_once __DIR__ . '/../TestCase.php';

use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProdutoTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_produtos_publicamente(): void
    {
        Cache::forget('produtos_all_list');

        Produto::create([
            'nome' => 'Produto Teste',
            'descricao' => 'Descrição',
            'preco' => 99.90,
        ]);

        $response = $this->getJson('/api/produtos');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json()));
    }

    public function test_impede_criacao_de_produto_sem_autenticacao(): void
    {
        $response = $this->postJson('/api/produtos', [
            'nome' => 'Produto Proibido',
            'preco' => 150.00,
        ]);

        $response->assertStatus(401);
    }

    public function test_permite_criacao_de_produto_quando_autenticado(): void
    {
        $user = User::create([
            'name' => 'Admin Teste',
            'email' => 'admintest@teste.com',
            'password' => 'password123',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/produtos', [
                             'nome' => 'Novo Produto Admin',
                             'descricao' => 'Criado por admin',
                             'preco' => 199.90,
                         ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nome' => 'Novo Produto Admin']);
    }

    public function test_permite_exclusao_de_produto_quando_autenticado(): void
    {
        $produto = Produto::create([
            'nome' => 'Produto Para Excluir',
            'preco' => 50.00,
        ]);

        $user = User::create([
            'name' => 'Admin Teste 2',
            'email' => 'admin2@teste.com',
            'password' => 'password123',
        ]);

        $token = $user->createToken('test-token-2')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->deleteJson("/api/produtos/{$produto->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('produtos', ['id' => $produto->id]);
    }
}
