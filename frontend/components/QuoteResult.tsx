import type { QuoteResponse } from '@/lib/api';

interface QuoteResultProps {
  result: QuoteResponse;
}

const ADICIONAIS_LABELS: Record<string, string> = {
  BAGAGEM: 'Bagagem',
  ESPORTES_AVENTURA: 'Esportes de Aventura',
};

function formatBRL(value: number): string {
  return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function formatAdicionais(lista: string[]): string {
  return lista.map((a) => ADICIONAIS_LABELS[a] ?? a).join(', ');
}

function formatAviso(text: string): string {
  return text.replace('ESPORTES_AVENTURA', 'Esportes de Aventura');
}

export function QuoteResult({ result }: QuoteResultProps) {
  return (
    <div className="space-y-10 animate-fade-in">
      <header className="space-y-3 animate-fade-up">
        <div className="flex items-center gap-3">
          <span className="w-8 h-px bg-[#2d7dd2]" />
          <span className="text-[11px] tracking-[0.15em] text-[#8aa4bc] uppercase">
            {result.dias_cobrados} {result.dias_cobrados === 1 ? 'dia' : 'dias'} cobrados
          </span>
        </div>
        <h2 className="text-2xl leading-tight text-[#0a1929]" style={{ fontFamily: 'var(--font-display)' }}>
          Resultado
        </h2>
        <p className="text-sm text-[#4a6a8a] leading-relaxed">
          Resumo detalhado do cálculo.
        </p>
      </header>

      <section className="space-y-4 animate-fade-up delay-100">
        <div className="flex items-center gap-2 mb-2">
          <span className="w-1 h-1 rounded-full bg-[#2d7dd2]" />
          <h2 className="text-[11px] tracking-[0.2em] text-[#5a7a94] uppercase font-medium">
            Viajantes
          </h2>
        </div>
        {result.viajantes.map((v, i) => (
          <div
            key={i}
            className="rounded-lg border border-[#dee8f2] bg-[#ffffff] p-5 animate-fade-up"
            style={{ animationDelay: `${0.15 + i * 0.1}s` }}
          >
            <div className="flex items-center justify-between mb-4">
              <div className="flex items-center gap-3">
                <span className="flex items-center justify-center w-6 h-6 rounded-full border border-[#2d7dd2]/30 text-[11px] text-[#2d7dd2] font-medium">
                  {i + 1}
                </span>
                <span className="text-sm font-medium text-[#0a1929]">{v.nome}</span>
              </div>
              <span className="text-xs text-[#8aa4bc]">{v.idade} anos</span>
            </div>
            <div className="grid grid-cols-3 gap-6">
              <div>
                <span className="text-[10px] tracking-[0.15em] text-[#8aa4bc] uppercase block mb-1">Multiplicador</span>
                <span className="text-sm text-[#0a1929]">×{v.multiplicador}</span>
              </div>
              <div>
                <span className="text-[10px] tracking-[0.15em] text-[#8aa4bc] uppercase block mb-1">Subtotal</span>
                <span className="text-sm font-medium text-[#0a1929]">{formatBRL(v.subtotal)}</span>
              </div>
              <div>
                <span className="text-[10px] tracking-[0.15em] text-[#8aa4bc] uppercase block mb-1">Adicionais</span>
                <span className="text-sm text-[#0a1929]">
                  {v.adicionais_aplicados.length > 0
                    ? formatAdicionais(v.adicionais_aplicados)
                    : <span className="text-[#8aa4bc]">Nenhum</span>}
                </span>
              </div>
            </div>
          </div>
        ))}
      </section>

      {result.avisos.length > 0 && (
        <section className="space-y-3 animate-fade-up delay-200">
          <div className="flex items-center gap-2 mb-2">
            <span className="w-1 h-1 rounded-full bg-[#d97706]" />
            <h2 className="text-[11px] tracking-[0.2em] text-[#d97706] uppercase font-medium">
              Avisos
            </h2>
          </div>
          {result.avisos.map((aviso, i) => (
            <div
              key={i}
              className="rounded-lg border border-[#d97706]/20 bg-[#fef3c7] px-5 py-3.5 text-sm text-[#d97706]"
            >
              {formatAviso(aviso)}
            </div>
          ))}
        </section>
      )}

      <div className="relative animate-fade-up delay-300">
        <div className="flex items-center gap-3 mb-6">
          <span className="flex-1 h-px bg-[#dee8f2]" />
          <span className="w-2 h-2 rotate-45 border border-[#2d7dd2]/40" />
          <span className="flex-1 h-px bg-[#dee8f2]" />
        </div>

        <div className="rounded-lg border border-[#2d7dd2]/20 bg-[#f0f6fb] p-6">
          <div className="space-y-3">
            <div className="flex justify-between items-center text-sm">
              <span className="text-[#4a6a8a]">Desconto de grupo</span>
              <span className="text-[#0a1929]">{result.desconto_grupo_percentual}%</span>
            </div>
            <div className="h-px bg-[#dee8f2]" />
            <div className="flex justify-between items-center pt-1">
              <span className="text-lg text-[#0a1929]" style={{ fontFamily: 'var(--font-display)' }}>
                Total Final
              </span>
              <span className="text-2xl font-medium text-[#2d7dd2]" style={{ fontFamily: 'var(--font-display)' }}>
                {formatBRL(result.total_final)}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
