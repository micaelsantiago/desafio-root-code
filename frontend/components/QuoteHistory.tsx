'use client';

import { useState } from 'react';
import { useQuote } from '@/context/QuoteContext';
import { summaryToResponse, type QuoteSummary } from '@/lib/api';
import { QuoteResult } from './QuoteResult';

const DESTINO_LABELS: Record<string, string> = {
  NACIONAL: 'Nacional',
  AMERICAS: 'Américas',
  EUROPA: 'Europa',
};

function formatBRL(value: number): string {
  return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function formatDate(dateStr: string): string {
  const d = new Date(dateStr);
  return d.toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function HistoryCard({
  item,
  onOpen,
}: {
  item: QuoteSummary;
  onOpen: () => void;
}) {
  return (
    <button
      type="button"
      onClick={onOpen}
      className="w-full text-left rounded-lg border border-[#dee8f2] bg-[#ffffff] p-5 hover:border-[#2d7dd2]/40 transition-all duration-200"
    >
      <div className="flex items-center justify-between mb-3">
        <span className="text-sm font-medium text-[#0a1929]">
          {DESTINO_LABELS[item.destino] ?? item.destino}
        </span>
        <span className="text-[11px] text-[#8aa4bc]">
          {item.dias_cobrados} {item.dias_cobrados === 1 ? 'dia' : 'dias'}
        </span>
      </div>
      <div className="flex items-center justify-between">
        <span className="text-xs text-[#4a6a8a]">
          {item.viajantes.map((v) => v.nome).join(', ')}
        </span>
        <span className="text-sm font-medium text-[#2d7dd2]">
          {formatBRL(parseFloat(item.total_final))}
        </span>
      </div>
      <div className="mt-2 text-[10px] text-[#8aa4bc]">
        {formatDate(item.created_at)}
      </div>
    </button>
  );
}

function Modal({ item, onClose }: { item: QuoteSummary; onClose: () => void }) {
  return (
    <div
      className="fixed inset-0 z-50 flex items-start justify-center pt-12 px-4 pb-8"
      onClick={onClose}
    >
      <div className="fixed inset-0 bg-[#0a1929]/40 backdrop-blur-sm" />
      <div
        className="relative w-full max-w-4xl bg-[#ffffff] rounded-xl border border-[#dee8f2] shadow-xl animate-fade-up max-h-[calc(100vh-6rem)] overflow-y-auto"
        onClick={(e) => e.stopPropagation()}
      >
        <div className="sticky top-0 z-10 flex items-center justify-between bg-[#ffffff] border-b border-[#dee8f2] px-6 py-4 rounded-t-xl">
          <div className="flex items-center gap-3">
            <span className="w-1 h-1 rounded-full bg-[#2d7dd2]" />
            <span className="text-[11px] tracking-[0.2em] text-[#5a7a94] uppercase font-medium">
              {DESTINO_LABELS[item.destino] ?? item.destino}
            </span>
            <span className="text-[11px] text-[#8aa4bc]">
              {item.dias_cobrados} {item.dias_cobrados === 1 ? 'dia' : 'dias'}
            </span>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="text-xs tracking-wider text-[#8aa4bc] hover:text-[#2d7dd2] transition-colors uppercase flex items-center gap-1"
          >
            <span className="text-lg leading-none">×</span> Fechar
          </button>
        </div>
        <div className="px-6 py-6">
          <QuoteResult result={summaryToResponse(item)} />
        </div>
      </div>
    </div>
  );
}

export function QuoteHistory() {
  const { state, loadHistory } = useQuote();
  const { history, historyLoading, historyPage, historyTotalPages } = state;
  const [modalItem, setModalItem] = useState<QuoteSummary | null>(null);

  return (
    <div className="space-y-6 animate-fade-in">
      <header className="space-y-3 animate-fade-up">
        <div className="flex items-center gap-3">
          <span className="w-8 h-px bg-[#2d7dd2]" />
          <span className="text-[11px] tracking-[0.15em] text-[#8aa4bc] uppercase">
            Histórico
          </span>
          {history.length > 0 && (
            <span className="text-[11px] text-[#8aa4bc]">
              {history.length} {history.length === 1 ? 'item' : 'itens'}
            </span>
          )}
        </div>
        <h2 className="text-2xl leading-tight text-[#0a1929]" style={{ fontFamily: 'var(--font-display)' }}>
          Cotações Salvas
        </h2>
        <p className="text-sm text-[#4a6a8a] leading-relaxed">
          Cotações calculadas anteriormente.
        </p>
      </header>

      {historyLoading ? (
        <div className="flex items-center justify-center py-12">
          <span className="inline-block w-5 h-5 border-2 border-[#2d7dd2] border-t-transparent rounded-full animate-spin" />
        </div>
      ) : history.length === 0 ? (
        <div className="flex items-center justify-center text-center py-12">
          <div className="space-y-3 max-w-xs">
            <p className="text-sm text-[#8aa4bc] leading-relaxed">
              Nenhuma cotação salva ainda. Calcule uma cotação para aparecer aqui.
            </p>
          </div>
        </div>
      ) : (
        <>
          <section className="space-y-3 animate-fade-up delay-100">
            {history.map((item) => (
              <HistoryCard
                key={item.id}
                item={item}
                onOpen={() => setModalItem(item)}
              />
            ))}
          </section>

          {historyTotalPages > 1 && (
            <div className="flex items-center justify-center gap-2 animate-fade-up delay-200">
              {Array.from({ length: historyTotalPages }, (_, i) => i + 1).map(
                (page) => (
                  <button
                    key={page}
                    type="button"
                    onClick={() => loadHistory(page)}
                    className={`w-8 h-8 rounded-lg text-xs font-medium transition-colors ${
                      page === historyPage
                        ? 'bg-[#2d7dd2] text-[#ffffff]'
                        : 'border border-[#dee8f2] text-[#4a6a8a] hover:border-[#2d7dd2]/40'
                    }`}
                  >
                    {page}
                  </button>
                ),
              )}
            </div>
          )}
        </>
      )}

      {modalItem && (
        <Modal item={modalItem} onClose={() => setModalItem(null)} />
      )}
    </div>
  );
}
