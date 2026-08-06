<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

// ponytail: Direct REST Controller using Redis Cache for read operations
class ProdutoController extends Controller
{
    private string $cacheKey = 'produtos_all_list';

    public function index()
    {
        // Redis Cache: Armazena o resultado da lista de produtos por 1 hora (3600s)
        return response()->json(
            Cache::remember($this->cacheKey, 3600, fn() => Produto::latest()->get())
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'required|string',
            'preco' => 'required|numeric|min:0',
            'imagem' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('imagem')) {
            $path = $request->file('imagem')->store('produtos', 'public');
            $validated['imagem'] = Storage::url($path);
        }

        $produto = Produto::create($validated);
        
        // Invalida a chave de cache do Redis após inserção
        Cache::forget($this->cacheKey);

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
            'descricao' => 'sometimes|required|string',
            'preco' => 'sometimes|required|numeric|min:0',
            'imagem' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('imagem')) {
            if ($produto->imagem) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $produto->imagem));
            }
            $path = $request->file('imagem')->store('produtos', 'public');
            $validated['imagem'] = Storage::url($path);
        }

        $produto->update($validated);
        
        // Invalida a chave de cache do Redis após atualização
        Cache::forget($this->cacheKey);

        return response()->json($produto);
    }

    public function destroy(Produto $produto)
    {
        if ($produto->imagem) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $produto->imagem));
        }
        $produto->delete();

        // Invalida a chave de cache do Redis após exclusão
        Cache::forget($this->cacheKey);

        return response()->json(null, 204);
    }
}
