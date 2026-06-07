<?php

namespace App\Services;

use DateTime;
use InvalidArgumentException;

class PricingService
{
    const TARIFA_NACIONAL    = 10.00;
    const TARIFA_AMERICAS    = 16.00;
    const TARIFA_EUROPA      = 22.00;

    const ETARIA_0_17        = 0.5;
    const ETARIA_18_64       = 1.0;
    const ETARIA_65_PLUS     = 2.0;

    const DESCONTO_1_4       = 0;
    const DESCONTO_5_PLUS    = 0.10;

    public function calcularViagem(array $data): array
    {
        $diasReais          = $this->calcularDiasReais($data['data_inicio'], $data['data_fim']);
        $diasCobrados       = max($diasReais, 5);
        $tarifa             = $this->buscarTarifa($data['destino']);

        $viajantesResultado = [];
        $avisos             = [];
        $totalGrupo         = 0.0;

        if ($diasReais < 5) {
            $avisos[] = "Período mínimo de 5 dias cobrados (viagem de {$diasReais} " . ($diasReais === 1 ? 'dia' : 'dias') . ').';
        }

        foreach ($data['viajantes'] as $viajante) {
            $resultado      = $this->calcularSubtotalViajante($viajante, $tarifa, $diasCobrados, $data['data_inicio']);
            $totalGrupo    += $resultado['subtotal'];

            if ($resultado['aviso'] !== null) {
                $avisos[]   = $resultado['aviso'];
            }

            unset($resultado['aviso']);
            $viajantesResultado[] = $resultado;
        }

        $desconto   = $this->calcularDescontoGrupo(count($data['viajantes']));
        $totalFinal = $this->arredondar($totalGrupo * (1 - $desconto));

        return [
            'dias_cobrados'             => $diasCobrados,
            'viajantes'                 => $viajantesResultado,
            'avisos'                    => $avisos,
            'desconto_grupo_percentual' => (int) ($desconto * 100),
            'total_final'               => $totalFinal,
        ];
    }

    private function calcularDiasReais(string $dataInicio, string $dataFim): int
    {
        $inicio = new DateTime($dataInicio);
        $fim    = new DateTime($dataFim);
        return $fim->diff($inicio)->days + 1;
    }

    private function buscarTarifa(string $regiao): float
    {
        return match ($regiao) {
            'NACIONAL' => self::TARIFA_NACIONAL,
            'AMERICAS' => self::TARIFA_AMERICAS,
            'EUROPA'   => self::TARIFA_EUROPA,
            default    => throw new InvalidArgumentException("Região desconhecida: $regiao"),
        };
    }

    private function calcularIdade(string $dataNascimento, string $dataInicio): int
    {
        $nascimento = new DateTime($dataNascimento);
        $inicio     = new DateTime($dataInicio);
        $idade      = $nascimento->diff($inicio)->y;

        return $idade;
    }

    private function buscarMultiplicador(int $idade): float
    {
        return match (true) {
            $idade <= 17  => self::ETARIA_0_17,
            $idade <= 64  => self::ETARIA_18_64,
            default       => self::ETARIA_65_PLUS,
        };
    }

    private function calcularSubtotalViajante(array $viajante, float $tarifa, int $diasCobrados, string $dataInicio): array
    {
        $idade                  = $this->calcularIdade($viajante['data_nascimento'], $dataInicio);
        $multiplicador          = $this->buscarMultiplicador($idade);

        $base                   = $tarifa * $diasCobrados;
        $subtotal               = $base * $multiplicador;

        $adicionaisAplicados    = [];
        $aviso                  = null;

        $viajanteAdicionais     = $viajante['adicionais'];

        if (
            in_array('ESPORTES_AVENTURA', $viajanteAdicionais) &&
            $idade >= 18 &&
            $idade <= 64
        ) {
            $subtotal               += $subtotal * 0.25;
            $adicionaisAplicados[]   = 'ESPORTES_AVENTURA';
        } elseif (in_array('ESPORTES_AVENTURA', $viajanteAdicionais)) {
            $aviso = "ESPORTES_AVENTURA não aplicado para {$viajante['nome']}: fora da faixa etária permitida (18-64).";
        }

        if (in_array('BAGAGEM', $viajanteAdicionais)) {
            $subtotal               += 3.00 * $diasCobrados;
            $adicionaisAplicados[]   = 'BAGAGEM';
        }

        return [
            'nome'                 => $viajante['nome'],
            'idade'                => $idade,
            'multiplicador'        => $multiplicador,
            'subtotal'             => $subtotal,
            'adicionais_aplicados' => $adicionaisAplicados,
            'aviso'                => $aviso,
        ];
    }

    private function calcularDescontoGrupo(int $quantidade): float
    {
        return $quantidade >= 5 ? self::DESCONTO_5_PLUS : self::DESCONTO_1_4;
    }

    private function arredondar(float $valor): float
    {
        return round($valor, 2, PHP_ROUND_HALF_UP);
    }
}
