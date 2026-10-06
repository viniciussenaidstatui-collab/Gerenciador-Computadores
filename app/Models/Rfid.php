<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

class Rfid extends Model
{
    protected $fillabe = [

    'rfid_code',
    'usuario_id',
    'retirado',
    'devolvido'


 ];

}
