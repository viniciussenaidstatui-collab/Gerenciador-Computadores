<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuario';

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'cpf',
        'data_nascimento',
        'rfid_uid',

    ];

    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
        ];
    }
}
