<?php

namespace Database\Seeders;

use App\Models\Checkout;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// ponytail: Seeder populando produtos e pedidos de exemplo via Faker (fakerphp/faker, já incluso no Laravel)
class PopulaSistemaSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('pt_BR');

        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@rheserva.com'],
            [
                'name' => 'Administrador RHeserva',
                'password' => Hash::make('password123'),
            ]
        );

        // 30 Produtos gerados via Faker
        $createdProdutos = [];
        for ($i = 0; $i < 30; $i++) {
            $createdProdutos[] = Produto::firstOrCreate(
                ['nome' => ucfirst($faker->unique()->words(3, true))],
                [
                    'descricao' => $faker->sentence(12),
                    'preco' => $faker->randomFloat(2, 39.9, 499.9),
                    'imagem' => "https://picsum.photos/seed/{$i}/600/600",
                ]
            );
        }

        // 10 Pedidos (Checkouts) com clientes gerados via Faker
        for ($idx = 0; $idx < 10; $idx++) {
            $pedidoId = 'PED-' . strtoupper(substr(md5($idx . $faker->uuid()), 0, 8));

            $itensCount = rand(1, 3);
            $itens = [];
            $total = 0.0;

            for ($i = 0; $i < $itensCount; $i++) {
                $p = $createdProdutos[($idx * 3 + $i) % count($createdProdutos)];
                $qtd = rand(1, 2);
                $subtotal = $p->preco * $qtd;
                $total += $subtotal;

                $itens[] = [
                    'id' => $p->id,
                    'nome' => $p->nome,
                    'preco' => (float) $p->preco,
                    'qtd' => $qtd,
                    'imagem' => $p->imagem,
                ];
            }

            Checkout::firstOrCreate(
                ['pedido_id' => $pedidoId],
                [
                    'nome' => $faker->name(),
                    'email' => $faker->unique()->safeEmail(),
                    'cep' => $faker->postcode(),
                    'rua' => $faker->streetName(),
                    'bairro' => $faker->citySuffix(),
                    'cidade' => $faker->city(),
                    'estado' => $faker->stateAbbr(),
                    'numero' => (string) $faker->buildingNumber(),
                    'complemento' => $faker->boolean(40) ? 'Apto ' . $faker->numberBetween(1, 999) : null,
                    'cartao_final' => '**** **** **** ' . $faker->numerify('####'),
                    'data_validade' => $faker->numerify('##') . '/' . $faker->numerify('##'),
                    'itens_json' => $itens,
                    'total' => round($total, 2),
                ]
            );
        }
    }
}
