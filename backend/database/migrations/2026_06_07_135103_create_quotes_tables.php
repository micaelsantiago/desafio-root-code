<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adicionais', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('destino');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->integer('dias_cobrados');
            $table->integer('desconto_grupo_percentual');
            $table->decimal('total_final', 10, 2);
            $table->timestamps();
        });

        Schema::create('quote_viajantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->string('nome');
            $table->date('data_nascimento');
            $table->integer('idade');
            $table->decimal('multiplicador', 4, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });

        Schema::create('quote_viajante_adicional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_viajante_id')->constrained()->cascadeOnDelete();
            $table->foreignId('adicional_id')->constrained('adicionais')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['quote_viajante_id', 'adicional_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_viajante_adicional');
        Schema::dropIfExists('quote_viajantes');
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('adicionais');
    }
};
