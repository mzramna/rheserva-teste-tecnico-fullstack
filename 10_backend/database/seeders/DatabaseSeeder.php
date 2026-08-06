<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Produto;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Reseta o usuário admin para garantir o hash correto do Laravel 11
        User::where('email', 'admin@rheserva.com')->delete();

        User::create([
            'name' => 'Administrador',
            'email' => 'admin@rheserva.com',
            'password' => 'password123'
        ]);

        // Seeder de Produtos Exemplo do Backend
        $produtosExemplo = [
            [
                'nome' => 'Camiseta RHeserva Cotton Premium',
                'descricao' => 'Camiseta 100% algodão egípcio de altíssima durabilidade e toque suave.',
                'preco' => 89.90,
                'imagem' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=500&auto=format&fit=crop&q=80'
            ],
            [
                'nome' => 'Calça Jeans Slim Dark Blue',
                'descricao' => 'Jeans com elastano para máximo conforto e caimento sob medida.',
                'preco' => 189.90,
                'imagem' => 'https://images.unsplash.com/photo-1542272604-780c96856592?w=500&auto=format&fit=crop&q=80'
            ],
            [
                'nome' => 'Jaqueta Couro Urban Classic',
                'descricao' => 'Jaqueta de couro sintético premium com forro térmico interno.',
                'preco' => 349.90,
                'imagem' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=500&auto=format&fit=crop&q=80'
            ],
            [
                'nome' => 'Tênis Running Performance Pro',
                'descricao' => 'Tênis com amortecimento responsivo e tecido respirável.',
                'preco' => 299.90,
                'imagem' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&auto=format&fit=crop&q=80'
            ],
            [
                'nome' => 'Relógio Cronógrafo Minimalist',
                'descricao' => 'Caixa em aço inoxidável e pulseira de couro genuíno.',
                'preco' => 450.00,
                'imagem' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&auto=format&fit=crop&q=80'
            ],
            [
                'nome' => 'Óculos de Sol Polarizado Aviador',
                'descricao' => 'Lentes com proteção UV400 e armação ultra leve.',
                'preco' => 149.90,
                'imagem' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=500&auto=format&fit=crop&q=80'
            ]
        ];

        foreach ($produtosExemplo as $p) {
            Produto::firstOrCreate(
                ['nome' => $p['nome']],
                $p
            );
        }
    }
}
