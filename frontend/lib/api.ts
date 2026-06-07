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

export interface QuoteSummary {
  id: number;
  destino: string;
  data_inicio: string;
  data_fim: string;
  dias_cobrados: number;
  desconto_grupo_percentual: number;
  total_final: string;
  created_at: string;
  viajantes: {
    id: number;
    nome: string;
    idade: number;
    multiplicador: string;
    subtotal: string;
    adicionais: { id: number; slug: string }[];
  }[];
}

export interface PaginatedQuotes {
  data: QuoteSummary[];
  current_page: number;
  last_page: number;
  total: number;
}

export function summaryToResponse(summary: QuoteSummary): QuoteResponse {
  return {
    dias_cobrados: summary.dias_cobrados,
    viajantes: summary.viajantes.map((v) => ({
      nome: v.nome,
      idade: v.idade,
      multiplicador: parseFloat(v.multiplicador),
      subtotal: parseFloat(v.subtotal),
      adicionais_aplicados: v.adicionais.map((a) => a.slug),
    })),
    avisos: [],
    desconto_grupo_percentual: summary.desconto_grupo_percentual,
    total_final: parseFloat(summary.total_final),
  };
}

export async function listQuotes(page: number = 1): Promise<PaginatedQuotes> {
  const response = await fetch(`${API_BASE_URL}/api/quotes?page=${page}`);

  if (!response.ok) {
    throw new ApiError(response.status, 'Erro ao carregar histórico');
  }

  return response.json();
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
