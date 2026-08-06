<?php

namespace Tests\Feature;

require_once __DIR__ . '/../TestCase.php';

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

// ponytail: Feature Tests for Checkout API validation boundaries and MySQL database persistence
class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_retorna_erro_422_se_campos_obrigatorios_estiverem_ausentes(): void
    {
        $response = $this->postJson('/api/checkout', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nome', 'email', 'cep', 'rua', 'bairro', 'cidade', 'estado', 'numero', 'cartao', 'data_validade']);
    }

    public function test_retorna_erro_422_para_cartao_ou_validade_invalidos(): void
    {
        $response = $this->postJson('/api/checkout', [
            'nome' => 'Carlos Silva',
            'email' => 'carlos@teste.com',
            'cep' => '01001000',
            'rua' => 'Praça da Sé',
            'bairro' => 'Sé',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'numero' => '100',
            'cartao' => '1234', // Inválido
            'data_validade' => '13/99' // Inválido
        ]);

        $response->assertStatus(422);
    }

    public function test_processa_e_salva_checkout_no_banco_de_dados_com_sucesso(): void
    {
        $payload = [
            'nome' => 'Carlos Silva',
            'email' => 'carlos@teste.com',
            'cep' => '01001-000',
            'rua' => 'Praça da Sé',
            'bairro' => 'Sé',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'numero' => '100',
            'cartao' => '1234 5678 9012 3456',
            'data_validade' => '12/28'
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
