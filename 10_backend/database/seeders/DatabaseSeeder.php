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
            'password' => 'password123' // Hashed automaticamente pelo cast do User.php
        ]);

        // Seeder de Produtos Exemplo
        Produto::firstOrCreate(
            ['nome' => 'Camiseta RHeserva Cotton'],
            [
                'descricao' => 'Camiseta 100% algodão premium de alta durabilidade.',
                'preco' => 89.90,
                'imagem' => 'https://via.placeholder.com/300x180?text=Camiseta+RHeserva'
            ]
        );
    }
}
