<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

// ponytail: Direct REST Controller for Produtos with Redis Caching and Sanctum auth checks on mutations
class ProdutoController extends Controller
{
    public function index()
    {
        return Cache::remember('produtos_all_list', 60, function () {
            return Produto::orderBy('id', 'desc')->get();
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'preco' => 'required|numeric|min:0',
            'imagem' => 'nullable|string',
        ]);

        $produto = Produto::create($validated);
        Cache::forget('produtos_all_list');

        return response()->json($produto, 201);
    }

    public function show(Produto $produto)
    {
        return response()->json($produto);
    }

    public function update(Request $request, Produto $produto)
    {
        $validated = $request->validate([
            'nome' => 'sometimes|required|string|max:255',
            'descricao' => 'nullable|string',
            'preco' => 'sometimes|required|numeric|min:0',
            'imagem' => 'nullable|string',
        ]);

        $produto->update($validated);
        Cache::forget('produtos_all_list');

        return response()->json($produto);
    }

    public function destroy(Produto $produto)
    {
        $produto->delete();
        Cache::forget('produtos_all_list');

        return response()->json(null, 204);
    }
}
