<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $fillable = [
        'destino',
        'data_inicio',
        'data_fim',
        'dias_cobrados',
        'desconto_grupo_percentual',
        'total_final',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date:Y-m-d',
            'data_fim' => 'date:Y-m-d',
        ];
    }

    public function viajantes(): HasMany
    {
        return $this->hasMany(QuoteTraveler::class);
    }
}
