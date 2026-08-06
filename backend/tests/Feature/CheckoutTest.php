<?php

namespace Tests\Feature;

require_once __DIR__ . '/../TestCase.php';

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_valida_campos_obrigatorios_no_checkout(): void
    {
        $response = $this->postJson('/api/checkout', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nome', 'email', 'telefone', 'cep', 'rua', 'bairro', 'cidade', 'estado', 'numero', 'cartao', 'titular_cartao', 'data_validade', 'cvc']);
    }

    public function test_registra_checkout_com_sucesso(): void
    {
        $payload = [
            'nome' => 'Carlos Silva',
            'email' => 'carlos@teste.com',
            'telefone' => '(11) 98765-4321',
            'cep' => '01001-000',
            'rua' => 'Praça da Sé',
            'bairro' => 'Sé',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'numero' => '100',
            'cartao' => '1234 5678 9012 3456',
            'titular_cartao' => 'Carlos Silva',
            'data_validade' => '12/28',
            'cvc' => '123',
            'itens' => [
                ['id' => 1, 'nome' => 'Camiseta RHeserva', 'preco' => 129.90, 'qtd' => 2]
            ],
            'total' => 259.80
        ];

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['status' => 'success']);

        $this->assertDatabaseHas('checkouts', [
            'email' => 'carlos@teste.com',
            'cartao_final' => '**** **** **** 3456'
        ]);
    }
}
