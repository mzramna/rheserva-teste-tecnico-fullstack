<?php

namespace Tests\Feature;

use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// Lean feature tests covering essential CRUD behavior & auth boundaries
class ProdutoTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_listar_produtos_sem_autenticacao(): void
    {
        Produto::factory()->create(['nome' => 'Produto Teste']);

        $response = $this->getJson('/api/produtos');

        $response->assertStatus(200)
                 ->assertJsonStructure([['id', 'nome', 'descricao', 'preco']]);
    }

    public function test_usuario_nao_autenticado_nao_pode_criar_produto(): void
    {
        $response = $this->postJson('/api/produtos', [
            'nome' => 'Produto Proibido',
            'descricao' => 'Sem auth',
            'preco' => 99.90
        ]);

        $response->assertStatus(401);
    }

    public function test_usuario_autenticado_pode_criar_produto_com_imagem(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/produtos', [
            'nome' => 'Produto Autorizado',
            'descricao' => 'Com auth',
            'preco' => 150.00,
            'imagem' => UploadedFile::fake()->image('produto.jpg')
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nome' => 'Produto Autorizado']);

        $this->assertDatabaseHas('produtos', ['nome' => 'Produto Autorizado']);
    }

    public function test_usuario_autenticado_pode_deletar_produto(): void
    {
        $user = User::factory()->create();
        $produto = Produto::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->deleteJson('/api/produtos/' . $produto->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('produtos', ['id' => $produto->id]);
    }
}
