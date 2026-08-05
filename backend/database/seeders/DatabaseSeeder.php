<?php

namespace Database\Seeders;

use App\Models\Produto;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User (Single Hash compatible)
        User::firstOrCreate(
            ['email' => 'admin@rheserva.com'],
            [
                'name' => 'Administrador RHeserva',
                'password' => 'password123',
            ]
        );

        // Initial E-Commerce Catalog Products
        $produtos = [
            [
                'nome' => 'Camiseta RHeserva Oversized',
                'descricao' => 'Camiseta 100% algodão egípcio com caimento moderno e toque macio.',
                'preco' => 129.90,
                'imagem' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'nome' => 'Tênis Urbano RHeserva Street',
                'descricao' => 'Tênis em couro legítimo com solado de borracha vulcanizada e palmilha conforto.',
                'preco' => 349.90,
                'imagem' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'nome' => 'Jaqueta Jeans Classic Premium',
                'descricao' => 'Jaqueta jeans com lavagem especial vintage e botões reforçados.',
                'preco' => 289.00,
                'imagem' => 'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'nome' => 'Mochila Minimalista Executiva',
                'descricao' => 'Mochila impermeável para notebook de até 15.6 polegadas com saída USB.',
                'preco' => 199.90,
                'imagem' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'nome' => 'Óculos de Sol Aviador UV400',
                'descricao' => 'Óculos de sol polarizados com armação leve e proteção total UV400.',
                'preco' => 159.00,
                'imagem' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=600&auto=format&fit=crop&q=80',
            ],
        ];

        foreach ($produtos as $p) {
            Produto::firstOrCreate(['nome' => $p['nome']], $p);
        }
    }
}
