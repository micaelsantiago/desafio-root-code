<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class QuoteApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('adicionais')->insert([
            ['slug' => 'BAGAGEM', 'nome' => 'Bagagem'],
            ['slug' => 'ESPORTES_AVENTURA', 'nome' => 'Esportes de Aventura'],
        ]);
    }

    public function test_store_persiste_e_retorna_cotacao(): void
    {
        $payload = [
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

        $response = $this->postJson('/api/quotes', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'dias_cobrados',
                'viajantes' => [
                    ['nome', 'idade', 'multiplicador', 'subtotal', 'adicionais_aplicados'],
                ],
                'avisos',
                'desconto_grupo_percentual',
                'total_final',
            ]);

        $this->assertDatabaseHas('quotes', [
            'destino'     => 'EUROPA',
            'total_final' => 335.50,
        ]);

        $this->assertDatabaseHas('quote_viajantes', [
            'nome' => 'Ana',
            'idade' => 36,
        ]);
    }

    public function test_store_retorna_422_para_dados_invalidos(): void
    {
        $payload = [
            'destino'     => 'INVALIDO',
            'data_inicio' => '2026-07-10',
            'data_fim'    => '2026-07-05',
            'viajantes'   => [],
        ];

        $response = $this->postJson('/api/quotes', $payload);

        $response->assertStatus(422);
        $this->assertDatabaseCount('quotes', 0);
    }

    public function test_index_retorna_lista_vazia_quando_sem_cotacoes(): void
    {
        $response = $this->getJson('/api/quotes');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_index_retorna_cotacoes_salvas(): void
    {
        $this->postJson('/api/quotes', [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                ['nome' => 'João', 'data_nascimento' => '1990-01-01', 'adicionais' => []],
            ],
        ]);

        $this->postJson('/api/quotes', [
            'destino'     => 'EUROPA',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-05',
            'viajantes'   => [
                ['nome' => 'Maria', 'data_nascimento' => '1995-05-10', 'adicionais' => ['BAGAGEM']],
            ],
        ]);

        $response = $this->getJson('/api/quotes');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $destinos = collect($response->json('data'))->pluck('destino')->sort()->values();
        $this->assertEquals(['EUROPA', 'NACIONAL'], $destinos->toArray());
    }

    public function test_index_retorna_viajantes_vinculados(): void
    {
        $this->postJson('/api/quotes', [
            'destino'     => 'NACIONAL',
            'data_inicio' => '2026-07-01',
            'data_fim'    => '2026-07-10',
            'viajantes'   => [
                ['nome' => 'João', 'data_nascimento' => '1990-01-01', 'adicionais' => []],
                ['nome' => 'Maria', 'data_nascimento' => '1995-05-10', 'adicionais' => []],
            ],
        ]);

        $response = $this->getJson('/api/quotes');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.0.viajantes'));
    }
}
