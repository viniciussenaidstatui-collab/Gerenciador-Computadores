<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rfid extends Model
{
    protected $table = 'rfid';

    protected $fillable = [
        'rfid_code',
        'usuario_id',
        'retirado',
        'devolvido',
    ];

    protected function casts(): array
    {
        return [
            'retirado' => 'datetime',
            'devolvido' => 'datetime',
        ];
    }
}
