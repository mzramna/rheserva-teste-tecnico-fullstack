<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        $validated = $request->validate([
            'nome'          => ['required', 'string', 'min:3'],
            'email'         => ['required', 'email'],
            'telefone'      => ['nullable', 'string'],
            'cep'           => ['required', 'string'],
            'rua'           => ['required', 'string'],
            'bairro'        => ['required', 'string'],
            'cidade'        => ['required', 'string'],
            'estado'        => ['required', 'string', 'size:2'],
            'numero'        => ['required', 'string', 'regex:/^\d+$/'],
            'complemento'   => ['nullable', 'string'],
            'cartao'        => ['required', 'string'],
            'data_validade' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
        ]);

        $pedidoId = 'PED-' . strtoupper(bin2hex(random_bytes(4)));
        $cartaoClean = preg_replace('/\D/', '', $validated['cartao']);
        $cartaoFinal = '**** **** **** ' . substr($cartaoClean, -4);

        $checkout = Checkout::create([
            'pedido_id'     => $pedidoId,
            'nome'          => $validated['nome'],
            'email'         => $validated['email'],
            'telefone'      => $validated['telefone'] ?? null,
            'cep'           => $validated['cep'],
            'rua'           => $validated['rua'],
            'bairro'        => $validated['bairro'],
            'cidade'        => $validated['cidade'],
            'estado'        => strtoupper($validated['estado']),
            'numero'        => $validated['numero'],
            'complemento'   => $validated['complemento'] ?? null,
            'cartao_final'  => $cartaoFinal,
            'data_validade' => $validated['data_validade'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pedido de checkout registrado no banco de dados com sucesso!',
            'pedido_id' => $pedidoId,
            'data' => $checkout
        ], 201);
    }
}
