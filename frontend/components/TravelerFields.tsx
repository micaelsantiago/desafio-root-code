'use client';

import { useQuote } from '@/context/QuoteContext';
import type { TravelerInput } from '@/lib/api';

const ADICIONAIS = [
  { value: 'BAGAGEM', label: 'Bagagem (R$ 3,00/dia)' },
  { value: 'ESPORTES_AVENTURA', label: 'Esportes de Aventura (+25%)' },
];

interface TravelerFieldsProps {
  index: number;
  data: TravelerInput;
  onRemove?: () => void;
  fieldErrors: Record<string, string[]> | null;
}

export function TravelerFields({ index, data, onRemove, fieldErrors }: TravelerFieldsProps) {
  const { setTravelerField, toggleAdicional } = useQuote();

  const travelerError = (field: string) =>
    fieldErrors?.[`viajantes.${index}.${field}`]?.map((msg, i) => (
      <p key={i} className="text-xs text-[#dc2626] mt-1.5">{msg}</p>
    ));

  return (
    <div className="rounded-lg border border-[#dee8f2] bg-[#f0f6fb] p-5 space-y-5">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <span className="flex items-center justify-center w-6 h-6 rounded-full border border-[#2d7dd2]/30 text-[11px] text-[#2d7dd2] font-medium">
            {index + 1}
          </span>
          <span className="text-xs tracking-wider text-[#5a7a94] uppercase">
            Viajante
          </span>
        </div>
        {onRemove && (
          <button
            type="button"
            onClick={onRemove}
            className="text-xs text-[#8aa4bc] hover:text-[#dc2626] transition-colors uppercase tracking-wider"
          >
            Remover
          </button>
        )}
      </div>

      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="block text-xs tracking-wider text-[#4a6a8a] uppercase mb-2">Nome</label>
          <input
            type="text"
            value={data.nome}
            onChange={(e) => setTravelerField(index, 'nome', e.target.value)}
            placeholder="Nome do viajante"
            className="w-full rounded-lg border border-[#dee8f2] bg-[#ffffff] px-4 py-2.5 text-sm text-[#0a1929] placeholder:text-[#8aa4bc] focus:outline-none focus:border-[#2d7dd2] focus:ring-1 focus:ring-[#2d7dd2]/30 transition-colors"
          />
          {travelerError('nome')}
        </div>

        <div>
          <label className="block text-xs tracking-wider text-[#4a6a8a] uppercase mb-2">Data de Nascimento</label>
          <input
            type="date"
            value={data.data_nascimento}
            onChange={(e) => setTravelerField(index, 'data_nascimento', e.target.value)}
            className="w-full rounded-lg border border-[#dee8f2] bg-[#ffffff] px-4 py-2.5 text-sm text-[#0a1929] focus:outline-none focus:border-[#2d7dd2] focus:ring-1 focus:ring-[#2d7dd2]/30 transition-colors"
          />
          {travelerError('data_nascimento')}
        </div>
      </div>

      <div>
        <label className="block text-xs tracking-wider text-[#4a6a8a] uppercase mb-3">Adicionais</label>
        <div className="flex flex-wrap gap-x-6 gap-y-2">
          {ADICIONAIS.map((adicional) => (
            <label key={adicional.value} className="flex items-center gap-2.5 text-sm text-[#4a6a8a] hover:text-[#0a1929] transition-colors cursor-pointer group">
              <input
                type="checkbox"
                checked={data.adicionais.includes(adicional.value)}
                onChange={() => toggleAdicional(index, adicional.value)}
                className="cursor-pointer"
              />
              <span className="group-hover:text-[#0a1929] transition-colors">{adicional.label}</span>
            </label>
          ))}
        </div>
      </div>
    </div>
  );
}
