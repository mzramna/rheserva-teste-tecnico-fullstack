<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    protected $table = 'checkouts';

    protected $fillable = [
        'pedido_id',
        'nome',
        'email',
        'cep',
        'rua',
        'bairro',
        'cidade',
        'estado',
        'numero',
        'complemento',
        'cartao_final',
        'data_validade',
        'itens_json',
        'total',
    ];

    protected $casts = [
        'itens_json' => 'array',
        'total' => 'float',
    ];
}
