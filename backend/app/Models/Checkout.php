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
        'telefone',
        'cep',
        'rua',
        'bairro',
        'cidade',
        'estado',
        'numero',
        'complemento',
        'cartao_final',
        'titular_cartao',
        'data_validade',
        'itens_json',
        'total',
    ];

    protected $casts = [
        'itens_json' => 'array',
        'total' => 'float',
    ];
}
