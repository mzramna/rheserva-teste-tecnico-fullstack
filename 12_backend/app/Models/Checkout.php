<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// ponytail: Checkout Eloquent Model
class Checkout extends Model
{
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
    ];
}
