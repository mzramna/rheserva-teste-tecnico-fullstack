<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// ponytail: Lean model for Product CRUD without unrequested relations or complex scopes
class Produto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
        'preco',
        'imagem'
    ];

    protected $casts = [
        'preco' => 'float'
    ];
}
