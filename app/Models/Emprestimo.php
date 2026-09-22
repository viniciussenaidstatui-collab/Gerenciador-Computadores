<?php

namespace App\Models;

use Database\Factories\EmprestimoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Emprestimo extends Model
{
    /** @use HasFactory<EmprestimoFactory> */
    use HasFactory;

    protected $fillable = [
        'computador_id',
        'usuario_id',
        'retirado_em',
        'devolvido_em',
    ];

    protected function casts(): array
    {
        return [
            'retirado_em' => 'datetime',
            'devolvido_em' => 'datetime',
        ];
    }

    public function computador(): BelongsTo
    {
        return $this->belongsTo(Computador::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }
}
