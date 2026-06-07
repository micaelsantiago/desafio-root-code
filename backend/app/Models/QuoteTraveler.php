<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class QuoteTraveler extends Model
{
    protected $table = 'quote_viajantes';

    protected $fillable = [
        'quote_id',
        'nome',
        'data_nascimento',
        'idade',
        'multiplicador',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date:Y-m-d',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function adicionais(): BelongsToMany
    {
        return $this->belongsToMany(Adicional::class, 'quote_viajante_adicional', 'quote_viajante_id', 'adicional_id')
            ->withTimestamps();
    }
}
