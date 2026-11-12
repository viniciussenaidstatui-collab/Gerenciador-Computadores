<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Responsavel extends Model
{
    use HasFactory;

    protected $table = 'responsaveis';

    protected $fillable = ['nome', 'rfid_uid', 'ativo', 'acao_permitida'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    protected function rfidUid(): Attribute
    {
        return Attribute::make(set: fn (string $value): string => strtoupper(trim($value)));
    }

    public function retiradasAutorizadas(): HasMany
    {
        return $this->hasMany(Emprestimo::class, 'retirada_autorizada_por');
    }

    public function devolucoesAutorizadas(): HasMany
    {
        return $this->hasMany(Emprestimo::class, 'devolucao_autorizada_por');
    }
}
