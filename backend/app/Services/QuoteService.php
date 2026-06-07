<?php

namespace App\Services;

use App\Models\Adicional;
use App\Models\Quote;
use App\Models\QuoteTraveler;
use Illuminate\Pagination\LengthAwarePaginator;

class QuoteService
{
    public function salvar(array $requestData, array $resultado): Quote
    {
        $quote = Quote::create([
            'destino'                    => $requestData['destino'],
            'data_inicio'                => $requestData['data_inicio'],
            'data_fim'                   => $requestData['data_fim'],
            'dias_cobrados'              => $resultado['dias_cobrados'],
            'desconto_grupo_percentual'  => $resultado['desconto_grupo_percentual'],
            'total_final'                => $resultado['total_final'],
        ]);

        $adicionaisPorSlug = Adicional::whereIn('slug', ['BAGAGEM', 'ESPORTES_AVENTURA'])
            ->get()
            ->keyBy('slug');

        foreach ($requestData['viajantes'] as $i => $viajanteInput) {
            $viajanteResultado = $resultado['viajantes'][$i];

            $traveler = QuoteTraveler::create([
                'quote_id'        => $quote->id,
                'nome'            => $viajanteInput['nome'],
                'data_nascimento' => $viajanteInput['data_nascimento'],
                'idade'           => $viajanteResultado['idade'],
                'multiplicador'   => $viajanteResultado['multiplicador'],
                'subtotal'        => $viajanteResultado['subtotal'],
            ]);

            $adicionais = collect($viajanteResultado['adicionais_aplicados'])
                ->map(fn (string $slug) => $adicionaisPorSlug->get($slug))
                ->filter();

            if ($adicionais->isNotEmpty()) {
                $traveler->adicionais()->attach($adicionais->pluck('id'));
            }
        }

        return $quote->load('viajantes.adicionais');
    }

    public function listar(): LengthAwarePaginator
    {
        return Quote::with('viajantes.adicionais')
            ->paginate();
    }
}
