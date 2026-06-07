'use client';

import { useState, useEffect } from 'react';
import { useQuote, type FormData, type TravelerFormData } from '@/context/QuoteContext';
import { TravelerFields } from './TravelerFields';
import { QuoteResult } from './QuoteResult';
import { QuoteHistory } from './QuoteHistory';

const DESTINOS = [
  { value: 'NACIONAL', label: 'Nacional' },
  { value: 'AMERICAS', label: 'Américas' },
  { value: 'EUROPA', label: 'Europa' },
] as const;

function Fields({ children }: { children: React.ReactNode }) {
  return <div className="space-y-5">{children}</div>;
}

function Field({ label, children, error }: { label: string; children: React.ReactNode; error?: React.ReactNode }) {
  return (
    <div>
      <label className="block text-xs tracking-wider text-[#4a6a8a] uppercase mb-2">{label}</label>
      {children}
      {error}
    </div>
  );
}

function FormPanel({ form, loading, error, fieldErrors, onFieldChange, onAddTraveler, onRemoveTraveler, onSubmit }: {
  form: FormData;
  loading: boolean;
  error: string | null;
  fieldErrors: Record<string, string[]> | null;
  onFieldChange: (field: keyof FormData, value: string) => void;
  onAddTraveler: () => void;
  onRemoveTraveler: (index: number) => void;
  onSubmit: () => void;
}) {
  const fieldError = (key: string) =>
    fieldErrors?.[key]?.map((msg, i) => (
      <p key={i} className="text-xs text-[#dc2626] mt-1.5">{msg}</p>
    ));

  return (
    <div className="space-y-10">
      <header className="space-y-3 animate-fade-up">
        <div className="flex items-center gap-3">
          <span className="w-8 h-px bg-[#2d7dd2]" />
          <span className="text-[11px] tracking-[0.15em] text-[#8aa4bc] uppercase">Cotação</span>
        </div>
        <h1 className="text-4xl leading-tight text-[#0a1929]" style={{ fontFamily: 'var(--font-display)' }}>
          Seguro Viagem
        </h1>
        <p className="text-sm text-[#4a6a8a] leading-relaxed max-w-md">
          Preencha os dados abaixo para calcular o valor do seguro para sua viagem.
        </p>
      </header>

      <section className="space-y-6 animate-fade-up delay-100">
        <div className="rounded-lg border border-[#dee8f2] bg-[#ffffff] p-6">
          <div className="flex items-center gap-2 mb-6">
            <span className="w-1 h-1 rounded-full bg-[#2d7dd2]" />
            <h2 className="text-[11px] tracking-[0.2em] text-[#5a7a94] uppercase font-medium">
              Destino e Período
            </h2>
          </div>
          <Fields>
            <Field label="Destino">
              <select
                value={form.destino}
                onChange={(e) => onFieldChange('destino', e.target.value)}
                className="w-full rounded-lg border border-[#dee8f2] bg-[#f0f6fb] px-4 py-2.5 text-sm text-[#0a1929] focus:outline-none focus:border-[#2d7dd2] focus:ring-1 focus:ring-[#2d7dd2]/30 transition-colors appearance-none"
                style={{
                  backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%234a6a8a' d='M6 8L1 3h10z'/%3E%3C/svg%3E")`,
                  backgroundRepeat: 'no-repeat',
                  backgroundPosition: 'right 14px center',
                }}
              >
                {DESTINOS.map((d) => (
                  <option key={d.value} value={d.value}>{d.label}</option>
                ))}
              </select>
            </Field>

            <div className="grid grid-cols-2 gap-4">
              <Field label="Data Início" error={fieldError('data_inicio')}>
                <input
                  type="date"
                  value={form.data_inicio}
                  onChange={(e) => onFieldChange('data_inicio', e.target.value)}
                  className="w-full rounded-lg border border-[#dee8f2] bg-[#f0f6fb] px-4 py-2.5 text-sm text-[#0a1929] focus:outline-none focus:border-[#2d7dd2] focus:ring-1 focus:ring-[#2d7dd2]/30 transition-colors"
                />
              </Field>
              <Field label="Data Fim" error={fieldError('data_fim')}>
                <input
                  type="date"
                  value={form.data_fim}
                  onChange={(e) => onFieldChange('data_fim', e.target.value)}
                  className="w-full rounded-lg border border-[#dee8f2] bg-[#f0f6fb] px-4 py-2.5 text-sm text-[#0a1929] focus:outline-none focus:border-[#2d7dd2] focus:ring-1 focus:ring-[#2d7dd2]/30 transition-colors"
                />
              </Field>
            </div>
          </Fields>
        </div>
      </section>

      <div className="flex items-center gap-3 animate-fade-up delay-200">
        <span className="flex-1 h-px bg-[#dee8f2]" />
        <span className="w-2 h-2 rotate-45 border border-[#2d7dd2]/40" />
        <span className="flex-1 h-px bg-[#dee8f2]" />
      </div>

      <section className="space-y-6 animate-fade-up delay-300">
        <div className="rounded-lg border border-[#dee8f2] bg-[#ffffff] p-6">
          <div className="flex items-center justify-between mb-6">
            <div className="flex items-center gap-2">
              <span className="w-1 h-1 rounded-full bg-[#2d7dd2]" />
              <h2 className="text-[11px] tracking-[0.2em] text-[#5a7a94] uppercase font-medium">
                Viajantes
              </h2>
              <span className="text-[11px] text-[#8aa4bc] ml-1">
                {form.viajantes.length} {form.viajantes.length === 1 ? 'viajante' : 'viajantes'}
              </span>
            </div>
            <button
              type="button"
              onClick={onAddTraveler}
              className="text-xs tracking-wider text-[#2d7dd2] hover:text-[#1b62b0] transition-colors uppercase flex items-center gap-1.5"
            >
              <span className="text-base leading-none">+</span> Adicionar
            </button>
          </div>

          <div className="space-y-4">
            {form.viajantes.map((viajante: TravelerFormData, index: number) => (
              <TravelerFields
                key={index}
                index={index}
                data={viajante}
                onRemove={form.viajantes.length > 1 ? () => onRemoveTraveler(index) : undefined}
                fieldErrors={fieldErrors}
              />
            ))}
          </div>
        </div>
      </section>

      {error && (
        <div className="rounded-lg border border-[#dc2626]/30 bg-[#fef2f2] px-5 py-3.5 animate-fade-up delay-400">
          <p className="text-sm text-[#dc2626]">{error}</p>
        </div>
      )}

      <button
        type="button"
        onClick={onSubmit}
        disabled={loading}
        className="relative w-full rounded-lg bg-[#2d7dd2] px-6 py-3.5 text-sm font-medium text-[#ffffff] hover:bg-[#1b62b0] disabled:opacity-30 disabled:cursor-not-allowed transition-all duration-300 animate-fade-up delay-500 overflow-hidden"
      >
        {loading ? (
          <span className="relative z-10 flex items-center justify-center gap-2">
            <span className="inline-block w-4 h-4 border-2 border-[#ffffff] border-t-transparent rounded-full animate-spin" />
            Calculando...
          </span>
        ) : (
          <span className="relative z-10">Calcular Cotação</span>
        )}
      </button>
    </div>
  );
}

export function QuoteForm() {
  const { state, setField, addTraveler, removeTraveler, submit, clearResult, loadHistory, setSelectedHistory } = useQuote();
  const { form, loading, error, fieldErrors, result, historyLoading } = state;
  const [rightTab, setRightTab] = useState<'result' | 'history'>('result');

  useEffect(() => {
    loadHistory();
  }, []);

  useEffect(() => {
    if (result) {
      setRightTab('result');
    }
  }, [result]);

  return (
    <div className="max-w-6xl mx-auto">
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <div className="min-w-0">
          <FormPanel
            form={form}
            loading={loading}
            error={error}
            fieldErrors={fieldErrors}
            onFieldChange={(field, value) => setField(field, value)}
            onAddTraveler={addTraveler}
            onRemoveTraveler={removeTraveler}
            onSubmit={submit}
          />
        </div>

        <div className="min-w-0 lg:border-l lg:border-[#dee8f2] lg:pl-10">
          <div className="flex items-center gap-1 mb-6 border-b border-[#dee8f2]">
            <button
              type="button"
              onClick={() => setRightTab('result')}
              className={`px-4 py-2.5 text-xs tracking-wider uppercase transition-colors relative ${
                rightTab === 'result'
                  ? 'text-[#2d7dd2] font-medium'
                  : 'text-[#8aa4bc] hover:text-[#4a6a8a]'
              }`}
            >
              Resultado
              {rightTab === 'result' && (
                <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-[#2d7dd2]" />
              )}
            </button>
            <button
              type="button"
              onClick={() => {
                setSelectedHistory(null);
                setRightTab('history');
                loadHistory();
              }}
              className={`px-4 py-2.5 text-xs tracking-wider uppercase transition-colors relative ${
                rightTab === 'history'
                  ? 'text-[#2d7dd2] font-medium'
                  : 'text-[#8aa4bc] hover:text-[#4a6a8a]'
              }`}
            >
              Histórico
              {rightTab === 'history' && (
                <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-[#2d7dd2]" />
              )}
            </button>
          </div>

          {rightTab === 'result' ? (
            result ? (
              <div className="relative">
                <button
                  type="button"
                  onClick={clearResult}
                  className="absolute top-0 right-0 z-10 text-xs tracking-wider text-[#8aa4bc] hover:text-[#2d7dd2] transition-colors uppercase flex items-center gap-1"
                >
                  <span className="text-lg leading-none">×</span> Fechar
                </button>
                <QuoteResult result={result} />
              </div>
            ) : (
              <div className="h-full flex items-center justify-center text-center animate-fade-up delay-200">
                <div className="space-y-4 max-w-xs">
                  <div className="w-12 h-12 mx-auto rounded-full border border-[#dee8f2] flex items-center justify-center">
                    <span className="w-4 h-4 rotate-45 border border-[#2d7dd2]/40" />
                  </div>
                  <p className="text-sm text-[#8aa4bc] leading-relaxed">
                    Preencha o formulário e clique em <span className="text-[#5a7a94]">Calcular Cotação</span> para ver o resultado aqui.
                  </p>
                </div>
              </div>
            )
          ) : (
            <QuoteHistory />
          )}
        </div>
      </div>
    </div>
  );
}
