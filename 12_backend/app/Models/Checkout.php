<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
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
        'data_validade',
    ];
}
