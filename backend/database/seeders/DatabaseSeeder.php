<?php

namespace Database\Seeders;

use App\Models\Checkout;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@rheserva.com'],
            [
                'name' => 'Administrador RHeserva',
                'password' => Hash::make('password123'),
            ]
        );

        // Lista de 30 Produtos Diversificados
        $listaProdutosData = [
            ['nome' => 'Camiseta RHeserva Oversized Preto', 'preco' => 129.90, 'img' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600&auto=format&fit=crop&q=80', 'desc' => 'Camiseta 100% algodão egípcio com corte moderno.'],
            ['nome' => 'Tênis Urbano RHeserva Street White', 'preco' => 349.90, 'img' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop&q=80', 'desc' => 'Tênis em couro legítimo vulcanizado.'],
            ['nome' => 'Jaqueta Jeans Classic Vintage', 'preco' => 289.00, 'img' => 'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?w=600&auto=format&fit=crop&q=80', 'desc' => 'Jaqueta jeans premium com acabamento estonado.'],
            ['nome' => 'Mochila Minimalista Executiva 15"', 'preco' => 199.90, 'img' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&auto=format&fit=crop&q=80', 'desc' => 'Mochila impermeável com compartimento acolchoado.'],
            ['nome' => 'Óculos de Sol Aviador UV400 Gold', 'preco' => 159.00, 'img' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=600&auto=format&fit=crop&q=80', 'desc' => 'Lentes polarizadas com proteção UV total.'],
            ['nome' => 'Boné RHeserva Aba Curva Off-White', 'preco' => 89.90, 'img' => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=600&auto=format&fit=crop&q=80', 'desc' => 'Boné estruturado com bordado frontal em alto relevo.'],
            ['nome' => 'Moletom Canguru Heavyweight Cinza', 'preco' => 249.90, 'img' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=600&auto=format&fit=crop&q=80', 'desc' => 'Moletom 3 cabos flanelado de altíssima gramatura.'],
            ['nome' => 'Relógio Analógico Minimalista Couro', 'preco' => 399.00, 'img' => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?w=600&auto=format&fit=crop&q=80', 'desc' => 'Caixa em aço inoxidável e pulseira de couro genuíno.'],
            ['nome' => 'Calça Chino Slim Fit Bege', 'preco' => 179.90, 'img' => 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=600&auto=format&fit=crop&q=80', 'desc' => 'Calça de sarja com elastano para máximo conforto.'],
            ['nome' => 'Camisa Polo Piquet Premium Azul', 'preco' => 139.90, 'img' => 'https://images.unsplash.com/photo-1625910513413-4ec2b123a63b?w=600&auto=format&fit=crop&q=80', 'desc' => 'Polo em malha piquet com gola estruturada.'],
            ['nome' => 'Carteira Slim Couro Café', 'preco' => 99.00, 'img' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=600&auto=format&fit=crop&q=80', 'desc' => 'Carteira compacta com bloqueio RFID de aproximação.'],
            ['nome' => 'Bermuda Cargo Utilitária Oliva', 'preco' => 159.90, 'img' => 'https://images.unsplash.com/photo-1591195853828-11db59a44f6b?w=600&auto=format&fit=crop&q=80', 'desc' => 'Bermuda com múltiplos bolsos e tecido de ripstop reforçado.'],
            ['nome' => 'Cinto de Couro Dupla Face Preto/Marrom', 'preco' => 79.90, 'img' => 'https://images.unsplash.com/photo-1624222247344-550fb60583dc?w=600&auto=format&fit=crop&q=80', 'desc' => 'Fivela giratória em metal escovado.'],
            ['nome' => 'Jaqueta Puffer Térmica Preta', 'preco' => 329.00, 'img' => 'https://images.unsplash.com/photo-1544441893-675973e31985?w=600&auto=format&fit=crop&q=80', 'desc' => 'Jaqueta com enchimento sintético acolchoado e capuz removível.'],
            ['nome' => 'Camiseta Basic Cotton Branca', 'preco' => 79.90, 'img' => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=600&auto=format&fit=crop&q=80', 'desc' => 'Camiseta essencial 100% algodão pima.'],
            ['nome' => 'Tênis Running Performance Pro', 'preco' => 429.90, 'img' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600&auto=format&fit=crop&q=80', 'desc' => 'Amortecimento com tecnologia de alta resposta e mesh respirável.'],
            ['nome' => 'Shorts de Praia Quick Dry Floral', 'preco' => 119.90, 'img' => 'https://images.unsplash.com/photo-1507680434567-5739c80be1ac?w=600&auto=format&fit=crop&q=80', 'desc' => 'Tecido de secagem rápida com cós elástico e cordão.'],
            ['nome' => 'Óculos de Sol Retro Square Black', 'preco' => 149.00, 'img' => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=600&auto=format&fit=crop&q=80', 'desc' => 'Design quadrado clássico com armação reforçada.'],
            ['nome' => 'Mala de Viagem Bordo de Couro', 'preco' => 499.90, 'img' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&auto=format&fit=crop&q=80', 'desc' => 'Mala de mão elegante ideal para viagens de fim de semana.'],
            ['nome' => 'Suéter em Tricô Gola Careca Marinho', 'preco' => 219.90, 'img' => 'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=600&auto=format&fit=crop&q=80', 'desc' => 'Tricô leve e elegante para dias de meia-estação.'],
            ['nome' => 'Calça Jeans Skinny Dark Wash', 'preco' => 189.90, 'img' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=600&auto=format&fit=crop&q=80', 'desc' => 'Jeans com elastano e lavagem escura elegante.'],
            ['nome' => 'Camisa Linho Manga Longa Cru', 'preco' => 239.90, 'img' => 'https://images.unsplash.com/photo-1598033129183-c4f50c736f10?w=600&auto=format&fit=crop&q=80', 'desc' => 'Linho natural respirável perfeito para dias quentes.'],
            ['nome' => 'Chinelo Slide RHeserva Ergônomico', 'preco' => 69.90, 'img' => 'https://images.unsplash.com/photo-1603808033192-082d6919d3e1?w=600&auto=format&fit=crop&q=80', 'desc' => 'Solado em EVA com amortecimento anatômico.'],
            ['nome' => 'Gorro Beanie em Lã Cinza Chumbo', 'preco' => 59.90, 'img' => 'https://images.unsplash.com/photo-1576871337632-b9aef4c17ab9?w=600&auto=format&fit=crop&q=80', 'desc' => 'Gorro de tricot aquecido para os dias frios.'],
            ['nome' => 'Sapatênis Casual em Camurça Caramelo', 'preco' => 279.90, 'img' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=600&auto=format&fit=crop&q=80', 'desc' => 'Acabamento elegante em camurça natural.'],
            ['nome' => 'Regata Esportiva Dry Fit', 'preco' => 59.90, 'img' => 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=600&auto=format&fit=crop&q=80', 'desc' => 'Tecido respirável de alta evaporação do suor.'],
            ['nome' => 'Camisa Xadrez Flanela Vermelha', 'preco' => 199.90, 'img' => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=600&auto=format&fit=crop&q=80', 'desc' => 'Flanela super macia estilo lumberjack.'],
            ['nome' => 'Necessaire de Viagem Couro Impermeável', 'preco' => 89.90, 'img' => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=600&auto=format&fit=crop&q=80', 'desc' => 'Organizador com divisórias internas para produtos pessoais.'],
            ['nome' => 'Meias Cano Alto Pack 3 Pares', 'preco' => 49.90, 'img' => 'https://images.unsplash.com/photo-1586350977771-b3b0abd50c82?w=600&auto=format&fit=crop&q=80', 'desc' => 'Algodão penteado com reforço no calcanhar e pontas.'],
            ['nome' => 'Jaqueta Bomber Corta-Vento', 'preco' => 269.90, 'img' => 'https://images.unsplash.com/photo-1548883354-7622d03aca27?w=600&auto=format&fit=crop&q=80', 'desc' => 'Proteção contra vento e chuva leve com design urbano.']
        ];

        $createdProdutos = [];
        foreach ($listaProdutosData as $p) {
            $createdProdutos[] = Produto::firstOrCreate(
                ['nome' => $p['nome']],
                [
                    'descricao' => $p['desc'],
                    'preco' => $p['preco'],
                    'imagem' => $p['img'],
                ]
            );
        }

        // Criar 10 Pedidos Realizados (Checkouts)
        $clientesData = [
            ['nome' => 'Maria Oliveira', 'email' => 'maria.oliveira@email.com', 'rua' => 'Av. Paulista', 'num' => '1000', 'comp' => 'Apto 101', 'bairro' => 'Bela Vista', 'cidade' => 'São Paulo', 'uf' => 'SP', 'cep' => '01310-100', 'cartao' => '**** **** **** 4321', 'val' => '10/28'],
            ['nome' => 'Pedro Santos', 'email' => 'pedro.santos@email.com', 'rua' => 'Rua das Laranjeiras', 'num' => '450', 'comp' => 'Bloco C', 'bairro' => 'Laranjeiras', 'cidade' => 'Rio de Janeiro', 'uf' => 'RJ', 'cep' => '22240-003', 'cartao' => '**** **** **** 8876', 'val' => '08/27'],
            ['nome' => 'Ana Costa', 'email' => 'ana.costa@email.com', 'rua' => 'Av. Afonso Pena', 'num' => '2500', 'comp' => 'Sala 402', 'bairro' => 'Funcionários', 'cidade' => 'Belo Horizonte', 'uf' => 'MG', 'cep' => '30130-007', 'cartao' => '**** **** **** 1298', 'val' => '12/29'],
            ['nome' => 'Fernando Lima', 'email' => 'fernando.lima@email.com', 'rua' => 'Rua Halfeld', 'num' => '780', 'comp' => 'Apto 205', 'bairro' => 'Centro', 'cidade' => 'Juiz de Fora', 'uf' => 'MG', 'cep' => '36010-000', 'cartao' => '**** **** **** 5543', 'val' => '05/26'],
            ['nome' => 'Juliana Souza', 'email' => 'juliana.souza@email.com', 'rua' => 'Av. Batel', 'num' => '1230', 'comp' => null, 'bairro' => 'Batel', 'cidade' => 'Curitiba', 'uf' => 'PR', 'cep' => '80420-090', 'cartao' => '**** **** **** 9012', 'val' => '11/27'],
            ['nome' => 'Rodrigo Alves', 'email' => 'rodrigo.alves@email.com', 'rua' => 'Av. Oceânica', 'num' => '500', 'comp' => 'Apto 302', 'bairro' => 'Barra', 'cidade' => 'Salvador', 'uf' => 'BA', 'cep' => '40140-130', 'cartao' => '**** **** **** 3344', 'val' => '04/28'],
            ['nome' => 'Camila Pereira', 'email' => 'camila.pereira@email.com', 'rua' => 'Av. Carlos Gomes', 'num' => '1100', 'comp' => 'Conjunto 12', 'bairro' => 'Auxiliadora', 'cidade' => 'Porto Alegre', 'uf' => 'RS', 'cep' => '90480-005', 'cartao' => '**** **** **** 6789', 'val' => '09/29'],
            ['nome' => 'Lucas Martins', 'email' => 'lucas.martins@email.com', 'rua' => 'Av. Boa Viagem', 'num' => '3200', 'comp' => 'Apto 1501', 'bairro' => 'Boa Viagem', 'cidade' => 'Recife', 'uf' => 'PE', 'cep' => '51020-000', 'cartao' => '**** **** **** 2211', 'val' => '03/27'],
            ['nome' => 'Beatriz Rocha', 'email' => 'beatriz.rocha@email.com', 'rua' => 'SQS 308 Bloco F', 'num' => '102', 'comp' => null, 'bairro' => 'Asa Sul', 'cidade' => 'Brasília', 'uf' => 'DF', 'cep' => '70355-060', 'cartao' => '**** **** **** 7788', 'val' => '07/28'],
            ['nome' => 'Gabriel Ferreira', 'email' => 'gabriel.ferreira@email.com', 'rua' => 'Av. Beira Mar Norte', 'num' => '2100', 'comp' => 'Apto 804', 'bairro' => 'Agronômica', 'cidade' => 'Florianópolis', 'uf' => 'SC', 'cep' => '88025-700', 'cartao' => '**** **** **** 9900', 'val' => '01/30'],
        ];

        foreach ($clientesData as $idx => $cli) {
            $pedidoId = 'PED-' . strtoupper(substr(md5($idx . $cli['email']), 0, 8));
            
            // Selecionar 1 a 3 produtos aleatórios para o pedido
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
                    'preco' => (float)$p->preco,
                    'qtd' => $qtd,
                    'imagem' => $p->imagem
                ];
            }

            Checkout::firstOrCreate(
                ['pedido_id' => $pedidoId],
                [
                    'nome' => $cli['nome'],
                    'email' => $cli['email'],
                    'cep' => $cli['cep'],
                    'rua' => $cli['rua'],
                    'bairro' => $cli['bairro'],
                    'cidade' => $cli['cidade'],
                    'estado' => $cli['uf'],
                    'numero' => $cli['num'],
                    'complemento' => $cli['comp'],
                    'cartao_final' => $cli['cartao'],
                    'data_validade' => $cli['val'],
                    'itens_json' => $itens,
                    'total' => round($total, 2),
                ]
            );
        }
    }
}
