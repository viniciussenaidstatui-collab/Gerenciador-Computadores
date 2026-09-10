<?php

namespace App\Models;

use Database\Factories\ComputadorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Computador extends Model
{
    /** @use HasFactory<ComputadorFactory> */
    use HasFactory;

    protected $table = 'computadores';

    protected $fillable = [
        'numero',
        'usuario_id',
    ];

    protected function casts(): array
    {
        return [
            'numero' => 'integer',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
