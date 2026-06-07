'use client';

import { QuoteProvider } from '@/context/QuoteContext';
import type { ReactNode } from 'react';

export function Providers({ children }: { children: ReactNode }) {
  return <QuoteProvider>{children}</QuoteProvider>;
}
