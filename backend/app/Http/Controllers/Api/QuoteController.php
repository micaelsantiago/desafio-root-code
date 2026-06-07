<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Services\PricingService;
use App\Services\QuoteService;
use App\Http\Requests\QuoteRequest;

class QuoteController extends Controller
{
    private PricingService $pricingService;
    private QuoteService $quoteService;

    public function __construct()
    {
        $this->pricingService = new PricingService();
        $this->quoteService = new QuoteService();
    }

    public function store(QuoteRequest $request)
    {
        $data = $request->validated();
        $resultado = $this->pricingService->calcularViagem($data);
        $this->quoteService->salvar($data, $resultado);
        return response()->json($resultado);
    }

    public function index()
    {
        return response()->json($this->quoteService->listar());
    }
}
