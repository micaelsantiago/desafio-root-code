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
        $this->assertCount(1, $resultado['avisos']);
        $this->assertStringContainsString('mínimo de 5 dias', $resultado['avisos'][0]);
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

    public function test_viagem_mesmo_dia_cobra_minimo_de_cinco_dias()
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-01',
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
        $this->assertStringContainsString('1 dia', $resultado['avisos'][0]);
    }

    public function test_viagem_dez_dias_sem_periodo_minimo()
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
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEquals(10, $resultado['dias_cobrados']);
        $this->assertEmpty($resultado['avisos']);
        $this->assertEquals(100.00, $resultado['total_final']);
    }

    public function test_tarifa_americas_dezesseis_reais()
    {
        $data = [
            'destino'     => 'AMERICAS',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                [
                    'nome'            => 'João',
                    'data_nascimento' => '1990-01-01',
                    'adicionais'      => [],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEquals(160.00, $resultado['total_final']);
    }

    public function test_tarifa_europa_vinte_e_dois_reais()
    {
        $data = [
            'destino'     => 'EUROPA',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                [
                    'nome'            => 'João',
                    'data_nascimento' => '1990-01-01',
                    'adicionais'      => [],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEquals(220.00, $resultado['total_final']);
    }

    public function test_multiplicador_meio_para_menor_de_idade()
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                [
                    'nome'            => 'Criança',
                    'data_nascimento' => '2016-07-01',
                    'adicionais'      => [],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEquals(10, $resultado['viajantes'][0]['idade']);
        $this->assertEquals(0.5, $resultado['viajantes'][0]['multiplicador']);
        $this->assertEquals(50.00, $resultado['total_final']);
    }

    public function test_apenas_bagagem_sem_esportes()
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                [
                    'nome'            => 'João',
                    'data_nascimento' => '1990-01-01',
                    'adicionais'      => ['BAGAGEM'],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEquals(130.00, $resultado['total_final']);
        $this->assertEquals(['BAGAGEM'], $resultado['viajantes'][0]['adicionais_aplicados']);
    }

    public function test_apenas_esportes_aventura_dentro_da_faixa()
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                [
                    'nome'            => 'João',
                    'data_nascimento' => '1990-01-01',
                    'adicionais'      => ['ESPORTES_AVENTURA'],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEquals(125.00, $resultado['total_final']);
        $this->assertEquals(['ESPORTES_AVENTURA'], $resultado['viajantes'][0]['adicionais_aplicados']);
    }

    public function test_esportes_aventura_negado_para_menor_de_idade()
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                [
                    'nome'            => 'Jovem',
                    'data_nascimento' => '2016-07-01',
                    'adicionais'      => ['ESPORTES_AVENTURA'],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEmpty($resultado['viajantes'][0]['adicionais_aplicados']);
        $this->assertEquals(50.00, $resultado['total_final']);
        $this->assertStringContainsString('Jovem', $resultado['avisos'][0]);
    }

    public function test_esportes_aventura_valido_para_18_anos()
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                [
                    'nome'            => 'AdultoJovem',
                    'data_nascimento' => '2008-07-01',
                    'adicionais'      => ['ESPORTES_AVENTURA'],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEquals(18, $resultado['viajantes'][0]['idade']);
        $this->assertEquals(125.00, $resultado['total_final']);
        $this->assertContains('ESPORTES_AVENTURA', $resultado['viajantes'][0]['adicionais_aplicados']);
    }

    public function test_esportes_aventura_valido_para_64_anos()
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                [
                    'nome'            => 'AdultoIdoso',
                    'data_nascimento' => '1962-07-01',
                    'adicionais'      => ['ESPORTES_AVENTURA'],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEquals(64, $resultado['viajantes'][0]['idade']);
        $this->assertEquals(125.00, $resultado['total_final']);
        $this->assertContains('ESPORTES_AVENTURA', $resultado['viajantes'][0]['adicionais_aplicados']);
    }

    public function test_esportes_aventura_negado_para_65_anos()
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                [
                    'nome'            => 'Idoso',
                    'data_nascimento' => '1961-07-01',
                    'adicionais'      => ['ESPORTES_AVENTURA'],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEmpty($resultado['viajantes'][0]['adicionais_aplicados']);
        $this->assertEquals(200.00, $resultado['total_final']);
        $this->assertStringContainsString('Idoso', $resultado['avisos'][0]);
    }

    public function test_sem_add_ons_retorna_lista_vazia()
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
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEmpty($resultado['viajantes'][0]['adicionais_aplicados']);
    }

    public function test_quatro_viajantes_sem_desconto_de_grupo()
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                ['nome' => 'A', 'data_nascimento' => '1990-01-01', 'adicionais' => []],
                ['nome' => 'B', 'data_nascimento' => '1990-01-01', 'adicionais' => []],
                ['nome' => 'C', 'data_nascimento' => '1990-01-01', 'adicionais' => []],
                ['nome' => 'D', 'data_nascimento' => '1990-01-01', 'adicionais' => []],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEquals(0, $resultado['desconto_grupo_percentual']);
        $this->assertEquals(400.00, $resultado['total_final']);
    }

    public function test_ordem_de_calculo_esportes_antes_bagagem()
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                [
                    'nome'            => 'João',
                    'data_nascimento' => '1990-01-01',
                    'adicionais'      => ['ESPORTES_AVENTURA', 'BAGAGEM'],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        // base = 10 * 10 = 100
        // subtotal = 100 * 1.0 = 100
        // + ESPORTES = 100 * 0.25 = 25 → subtotal = 125
        // + BAGAGEM = 3 * 10 = 30 → subtotal = 155
        $this->assertEquals(155.00, $resultado['total_final']);
    }

    public function test_idade_antes_do_aniversario_nao_atinge_proxima_faixa()
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                [
                    'nome'            => 'QuaseIdoso',
                    'data_nascimento' => '1961-07-02',
                    'adicionais'      => [],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        // Ainda 64 anos (aniversário é 02/07, data_inicio é 01/07)
        $this->assertEquals(64, $resultado['viajantes'][0]['idade']);
        $this->assertEquals(1.0, $resultado['viajantes'][0]['multiplicador']);
        $this->assertEquals(100.00, $resultado['total_final']);
    }

    public function test_add_ons_nao_solicitados_nao_sao_aplicados()
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
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        $this->assertEmpty($resultado['viajantes'][0]['adicionais_aplicados']);
    }

    public function test_meses_com_diferenca_ultrapassa_um_mes()
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-08-01',
            'viajantes'   => [
                [
                    'nome'            => 'João',
                    'data_nascimento' => '1990-01-01',
                    'adicionais'      => [],
                ],
            ],
        ];

        $resultado = $this->pricingService->calcularViagem($data);

        // 01/07 a 01/08 = (31 - 1) + 1 = 31 dias (julho tem 31 dias)
        $this->assertEquals(32, $resultado['dias_cobrados']);
    }
}
