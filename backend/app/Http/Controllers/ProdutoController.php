<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

// ponytail: Direct REST Controller for Produtos with Redis Caching, Pagination & Sanctum auth checks
class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 6);
        
        if ($request->has('paginate') && $request->get('paginate') === 'false') {
            return Cache::remember('produtos_all_list', 60, function () {
                return Produto::orderBy('id', 'desc')->get();
            });
        }

        $page = $request->get('page', 1);
        return Cache::remember("produtos_page_{$page}_per_{$perPage}", 60, function () use ($perPage) {
            return Produto::orderBy('id', 'desc')->paginate($perPage);
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
        Cache::flush();

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
        Cache::flush();

        return response()->json($produto);
    }

    public function destroy(Produto $produto)
    {
        $produto->delete();
        Cache::flush();

        return response()->json(null, 204);
    }
}
