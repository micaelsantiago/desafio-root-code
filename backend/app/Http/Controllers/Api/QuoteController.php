<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Services\PricingService;
use App\Http\Requests\QuoteRequest;

class QuoteController extends Controller
{
    private PricingService $pricingService;

    public function __construct()
    {
        $this->pricingService = new PricingService();
    }

    public function store(QuoteRequest $request)
    {
        $resultado = $this->pricingService->calcularViagem($request->validated());
        return response()->json($resultado);
    }
}
