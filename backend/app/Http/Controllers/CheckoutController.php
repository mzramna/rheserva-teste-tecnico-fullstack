<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use Illuminate\Http\Request;
use Propaganistas\LaravelPhone\Rules\Phone;

// ponytail: Public Checkout Controller saving order & cart items into MySQL database with pagination
class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 5);

        if ($request->has('paginate') && $request->get('paginate') === 'false') {
            return Checkout::orderBy('id', 'desc')->get();
        }

        return Checkout::orderBy('id', 'desc')->paginate($perPage);
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'nome'          => ['required', 'string', 'min:3'],
            'email'         => ['required', 'email'],
            // regra nacional ANATEL (celular=9 dígitos / fixo=8) validada via libphonenumber (propaganistas/laravel-phone)
            'telefone'      => ['required', 'string', (new Phone)->country('BR')],
            'cep'           => ['required', 'string'],
            'rua'           => ['required', 'string'],
            'bairro'        => ['required', 'string'],
            'cidade'        => ['required', 'string'],
            'estado'        => ['required', 'string', 'size:2'],
            'numero'        => ['required', 'string', 'regex:/^\d+$/'],
            'complemento'   => ['nullable', 'string'],
            'cartao'        => ['required', 'string'],
            'data_validade' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'cvc'           => ['required', 'string', 'regex:/^\d{3,4}$/'], // ponytail: validado mas nunca persistido (PCI-DSS)
            'itens'         => ['nullable', 'array'],
            'total'         => ['nullable', 'numeric'],
        ]);

        $pedidoId = 'PED-' . strtoupper(bin2hex(random_bytes(4)));
        $cartaoClean = preg_replace('/\D/', '', $validated['cartao']);
        $cartaoFinal = '**** **** **** ' . substr($cartaoClean, -4);

        $checkout = Checkout::create([
            'pedido_id'     => $pedidoId,
            'nome'          => $validated['nome'],
            'email'         => $validated['email'],
            'telefone'      => $validated['telefone'],
            'cep'           => $validated['cep'],
            'rua'           => $validated['rua'],
            'bairro'        => $validated['bairro'],
            'cidade'        => $validated['cidade'],
            'estado'        => strtoupper($validated['estado']),
            'numero'        => $validated['numero'],
            'complemento'   => $validated['complemento'] ?? null,
            'cartao_final'  => $cartaoFinal,
            'data_validade' => $validated['data_validade'],
            'itens_json'    => $validated['itens'] ?? [],
            'total'         => $validated['total'] ?? 0.0,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pedido de checkout registrado no banco de dados com sucesso!',
            'pedido_id' => $pedidoId,
            'data' => $checkout
        ], 201);
    }
}
