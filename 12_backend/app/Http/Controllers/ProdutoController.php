<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Routing\Controller;

class ProdutoController extends Controller
{
    public function index()
    {
        return response()->json(Produto::all());
    }
}
