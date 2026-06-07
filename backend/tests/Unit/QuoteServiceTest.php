<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Quote;
use App\Services\QuoteService;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class QuoteServiceTest extends TestCase
{
    use RefreshDatabase;

    private QuoteService $service;
    private PricingService $pricingService;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('adicionais')->insert([
            ['slug' => 'BAGAGEM', 'nome' => 'Bagagem'],
            ['slug' => 'ESPORTES_AVENTURA', 'nome' => 'Esportes de Aventura'],
        ]);

        $this->service = new QuoteService();
        $this->pricingService = new PricingService();
    }

    private function calcularResultado(array $data): array
    {
        return $this->pricingService->calcularViagem($data);
    }

    public function test_salvar_persiste_quote_com_dados_corretos(): void
    {
        $data = [
            'destino'     => 'EUROPA',
            'data_inicio' => '2026-07-10',
            'data_fim'    => '2026-07-20',
            'viajantes'   => [
                [
                    'nome'            => 'Ana',
                    'data_nascimento' => '1990-03-15',
                    'adicionais'      => ['BAGAGEM', 'ESPORTES_AVENTURA'],
                ],
            ],
        ];

        $resultado = $this->calcularResultado($data);
        $quote = $this->service->salvar($data, $resultado);

        $this->assertDatabaseHas('quotes', [
            'id'                         => $quote->id,
            'destino'                    => 'EUROPA',
            'data_inicio'                => '2026-07-10',
            'data_fim'                   => '2026-07-20',
            'dias_cobrados'              => 11,
            'desconto_grupo_percentual'  => 0,
            'total_final'                => 335.50,
        ]);
    }

    public function test_salvar_cria_viajantes_para_cada_pessoa(): void
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
                    'nome'            => 'Maria',
                    'data_nascimento' => '1995-05-10',
                    'adicionais'      => [],
                ],
            ],
        ];

        $resultado = $this->calcularResultado($data);
        $quote = $this->service->salvar($data, $resultado);

        $this->assertCount(2, $quote->viajantes);
        $this->assertEquals('João', $quote->viajantes[0]->nome);
        $this->assertEquals('Maria', $quote->viajantes[1]->nome);
    }

    public function test_salvar_persiste_adicionais_na_tabela_pivot(): void
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                [
                    'nome'            => 'Ana',
                    'data_nascimento' => '1990-03-15',
                    'adicionais'      => ['BAGAGEM', 'ESPORTES_AVENTURA'],
                ],
            ],
        ];

        $resultado = $this->calcularResultado($data);
        $quote = $this->service->salvar($data, $resultado);
        $viajante = $quote->viajantes->first();

        $this->assertCount(2, $viajante->adicionais);
        $this->assertEquals(['BAGAGEM', 'ESPORTES_AVENTURA'], $viajante->adicionais->pluck('slug')->toArray());
    }

    public function test_salvar_nao_aplica_esportes_para_fora_da_faixa_etaria(): void
    {
        $data = [
            'destino'     => 'NACIONAL',
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

        $resultado = $this->calcularResultado($data);
        $quote = $this->service->salvar($data, $resultado);
        $viajante = $quote->viajantes->first();

        $this->assertCount(0, $viajante->adicionais);
    }

    public function test_salvar_aplica_apenas_bagagem_quando_solicitado(): void
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

        $resultado = $this->calcularResultado($data);
        $quote = $this->service->salvar($data, $resultado);
        $viajante = $quote->viajantes->first();

        $this->assertCount(1, $viajante->adicionais);
        $this->assertEquals('BAGAGEM', $viajante->adicionais->first()->slug);
    }

    public function test_listar_retorna_cotacoes_com_viajantes_e_adicionais(): void
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                ['nome' => 'João', 'data_nascimento' => '1990-01-01', 'adicionais' => []],
            ],
        ];

        $resultado = $this->calcularResultado($data);
        $this->service->salvar($data, $resultado);
        $this->service->salvar($data, $resultado);

        $resultado = $this->service->listar();

        $this->assertCount(2, $resultado->items());
        $this->assertInstanceOf(Quote::class, $resultado->first());
        $this->assertTrue($resultado->first()->relationLoaded('viajantes'));
    }

    public function test_listar_retorna_vazio_quando_sem_registros(): void
    {
        $resultado = $this->service->listar();

        $this->assertCount(0, $resultado->items());
    }

    public function test_salvar_com_cinco_viajantes_aplica_desconto_de_grupo(): void
    {
        $data = [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                ['nome' => 'João',  'data_nascimento' => '1990-01-01', 'adicionais' => []],
                ['nome' => 'Maria', 'data_nascimento' => '1990-01-01', 'adicionais' => []],
                ['nome' => 'José',  'data_nascimento' => '1990-01-01', 'adicionais' => []],
                ['nome' => 'Ana',   'data_nascimento' => '1990-01-01', 'adicionais' => []],
                ['nome' => 'Pedro', 'data_nascimento' => '1990-01-01', 'adicionais' => []],
            ],
        ];

        $resultado = $this->calcularResultado($data);
        $quote = $this->service->salvar($data, $resultado);

        $this->assertEquals(10, $quote->desconto_grupo_percentual);
        $this->assertEquals(450.00, $quote->total_final);
    }
}
