<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PricingService;

class PricingServiceTest extends TestCase
{
    private PricingService $pricingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricingService = new PricingService();
    }
    
    public function test_viagem_curta_cobra_minimo_de_cinco_dias()
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-02',
            'viajantes'   => [
                [
                    'nome'            => 'João',
                    'data_nascimento' => '1990-01-01',
                    'adicionais'      => [],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEquals(5, $resultado['dias_cobrados']);
        $this->assertEquals(50.00, $resultado['total_final']);
    }

    public function test_idade_calculada_na_data_de_inicio()
    {
        // Nascido em 01/07/1961 → completa 65 anos exatamente em 01/07/2026 (data_inicio)
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                [
                    'nome'            => 'Idoso',
                    'data_nascimento' => '1961-07-01',
                    'adicionais'      => [],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEquals(65, $resultado['viajantes'][0]['idade']);
        $this->assertEquals(2.0, $resultado['viajantes'][0]['multiplicador']);
        $this->assertEquals(200.00, $resultado['total_final']);
    }

    public function test_esportes_aventura_negado_com_aviso_para_fora_da_faixa_etaria()
    {
        // Viajante com 75 anos → ESPORTES_AVENTURA não se aplica, mas gera aviso
        $data = [
            'destino'     => 'AMERICAS',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                [
                    'nome'            => 'Idoso',
                    'data_nascimento' => '1950-07-01',
                    'adicionais'      => ['ESPORTES_AVENTURA'],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEquals(320.00, $resultado['total_final']);
        $this->assertNotEmpty($resultado['avisos']);
        $this->assertStringContainsString('Idoso', $resultado['avisos'][0]);
        $this->assertStringContainsString('ESPORTES_AVENTURA', $resultado['avisos'][0]);
    }

    public function test_calculo_completo_com_esportes_negado_e_bagagem()
    {
        // Ana (36 anos): BAGAGEM + ESPORTES_AVENTURA → 305,00
        // João (77 anos): ESPORTES negado (aviso), BAGAGEM → 470,00
        // 2 viajantes → sem desconto de grupo
        $data = [
            'destino'     => 'EUROPA',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                [
                    'nome'            => 'Ana',
                    'data_nascimento' => '1990-03-15',
                    'adicionais'      => ['BAGAGEM', 'ESPORTES_AVENTURA'],
                ],
                [
                    'nome'            => 'João',
                    'data_nascimento' => '1948-11-02',
                    'adicionais'      => ['ESPORTES_AVENTURA', 'BAGAGEM'],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEquals(305.00, $resultado['viajantes'][0]['subtotal']);
        $this->assertEquals(470.00, $resultado['viajantes'][1]['subtotal']);
        $this->assertEquals(775.00, $resultado['total_final']);
        $this->assertNotEmpty($resultado['avisos']);
        $this->assertStringContainsString('João', $resultado['avisos'][0]);
    }

    public function test_desconto_de_grupo()
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                [
                    'nome'            => 'João',
                    'data_nascimento' => '1990-01-01',
                    'adicionais'      => [],
                ],
                [
                    'nome'            => 'Joana',
                    'data_nascimento' => '1990-01-01',
                    'adicionais'      => [],
                ],
                [
                    'nome'            => 'Ricardo',
                    'data_nascimento' => '1990-01-01',
                    'adicionais'      => [],
                ],
                [
                    'nome'            => 'Luan',
                    'data_nascimento' => '1990-01-01',
                    'adicionais'      => [],
                ],
                [
                    'nome'            => 'Micael',
                    'data_nascimento' => '1990-01-01',
                    'adicionais'      => [],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEquals(10, $resultado['desconto_grupo_percentual']);
        $this->assertEquals(450.00, $resultado['total_final']);
    }
}
