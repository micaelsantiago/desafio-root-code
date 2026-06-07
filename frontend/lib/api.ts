const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000';

export interface TravelerInput {
  nome: string;
  data_nascimento: string;
  adicionais: string[];
}

export interface QuoteRequest {
  destino: 'NACIONAL' | 'AMERICAS' | 'EUROPA';
  data_inicio: string;
  data_fim: string;
  viajantes: TravelerInput[];
}

export interface TravelerResult {
  nome: string;
  idade: number;
  multiplicador: number;
  subtotal: number;
  adicionais_aplicados: string[];
}

export interface QuoteResponse {
  dias_cobrados: number;
  viajantes: TravelerResult[];
  avisos: string[];
  desconto_grupo_percentual: number;
  total_final: number;
}

export async function submitQuote(data: QuoteRequest): Promise<QuoteResponse> {
  const response = await fetch(`${API_BASE_URL}/api/quotes`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  });

  if (!response.ok) {
    const errorBody = await response.json().catch(() => null);
    throw new ApiError(
      response.status,
      errorBody?.message ?? 'Erro ao calcular cotação',
      errorBody?.errors,
    );
  }

  return response.json();
}

export class ApiError extends Error {
  status: number;
  errors: Record<string, string[]> | undefined;

  constructor(status: number, message: string, errors?: Record<string, string[]>) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.errors = errors;
  }
}
