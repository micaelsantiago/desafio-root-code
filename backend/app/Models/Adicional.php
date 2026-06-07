<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Adicional extends Model
{
    protected $table = 'adicionais';

    protected $fillable = [
        'slug',
        'nome',
    ];

    public function quoteViajantes(): BelongsToMany
    {
        return $this->belongsToMany(QuoteTraveler::class, 'quote_viajante_adicional', 'adicional_id', 'quote_viajante_id')
            ->withTimestamps();
    }
}
