<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdicionalSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        DB::table('adicionais')->insert([
            ['slug' => 'BAGAGEM', 'nome' => 'Bagagem'],
            ['slug' => 'ESPORTES_AVENTURA', 'nome' => 'Esportes de Aventura'],
        ]);
    }
}
