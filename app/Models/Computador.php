<?php

namespace App\Models;

use Database\Factories\ComputadorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Computador extends Model
{
    /** @use HasFactory<ComputadorFactory> */
    use HasFactory;

    protected $table = 'computadores';

    protected $fillable = [
        'numero_patrimonio',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'numero_patrimonio' => 'string',
        ];
    }

    public function emprestimos(): HasMany
    {
        return $this->hasMany(Emprestimo::class);
    }
}
