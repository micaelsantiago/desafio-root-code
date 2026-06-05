<?php

namespace App\Services;

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
    {}

    private function calcularDiasCobrados(string $dataInicio, string $dataFim): int
    {}

    private function buscarTarifa(string $regiao): float
    {}

    private function calcularIdade(string $dataNascimento, string $dataInicio): int
    {}

    private function buscarMultiplicador(int $idade): float
    {}

    private function calcularSubtotalViajante(array $viajante, float $tarifa, int $diasCobrados): array
    {}

    private function calcularDescontoGrupo(int $quantidade): float
    {}

    private function arredondar(float $valor): float
    {}
}
