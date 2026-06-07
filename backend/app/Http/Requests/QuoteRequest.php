<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class QuoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destino'                      => ['required', 'in:NACIONAL,AMERICAS,EUROPA'],
            'data_inicio'                  => ['required', 'date'],
            'data_fim'                     => ['required', 'date', 'after_or_equal:data_inicio'],
            'viajantes'                    => ['required', 'array', 'min:1'],
            'viajantes.*.nome'             => ['required', 'string'],
            'viajantes.*.data_nascimento'  => ['required', 'date'],
            'viajantes.*.adicionais'       => ['nullable', 'array'],
            'viajantes.*.adicionais.*'     => ['in:BAGAGEM,ESPORTES_AVENTURA'],
        ];
    }

    public function messages(): array
    {
        return [
            'destino.required'                    => 'Selecione o destino da viagem.',
            'destino.in'                          => 'O destino selecionado não é válido.',
            'data_inicio.required'                => 'Informe a data de início da viagem.',
            'data_inicio.date'                    => 'A data de início deve ser uma data válida.',
            'data_fim.required'                   => 'Informe a data de fim da viagem.',
            'data_fim.date'                       => 'A data de fim deve ser uma data válida.',
            'data_fim.after_or_equal'             => 'A data de fim deve ser igual ou posterior à data de início.',
            'viajantes.required'                  => 'Adicione pelo menos um viajante para cotar o seguro.',
            'viajantes.array'                     => 'Os dados dos viajantes estão em formato inválido.',
            'viajantes.min'                       => 'Adicione pelo menos um viajante para cotar o seguro.',
            'viajantes.*.nome.required'           => 'Informe o nome do viajante.',
            'viajantes.*.nome.string'             => 'O nome do viajante deve ser um texto válido.',
            'viajantes.*.data_nascimento.required' => 'Informe a data de nascimento do viajante.',
            'viajantes.*.data_nascimento.date'    => 'A data de nascimento deve ser uma data válida.',
            'viajantes.*.adicionais.*.in'         => 'O adicional informado não é válido.',
        ];
    }
}
