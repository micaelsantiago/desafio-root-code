import type { Metadata } from "next";
import { DM_Serif_Display, Plus_Jakarta_Sans } from "next/font/google";
import "./globals.css";
import { Providers } from "./providers";

const displayFont = DM_Serif_Display({
  weight: "400",
  subsets: ["latin"],
  variable: "--font-display",
});

const bodyFont = Plus_Jakarta_Sans({
  subsets: ["latin"],
  variable: "--font-body",
});

export const metadata: Metadata = {
  title: "Cotação de Seguro Viagem",
  description: "Calcule o valor do seu seguro viagem",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html
      lang="pt-BR"
      className={`${displayFont.variable} ${bodyFont.variable} h-full`}
    >
      <body className="min-h-full flex flex-col">
        <header className="relative border-b border-[#dee8f2]">
          <div className="max-w-3xl mx-auto px-6 py-5 flex items-center justify-between">
            <div className="flex items-center gap-3">
              <span className="w-1.5 h-1.5 rounded-full bg-[#2d7dd2]" />
              <span className="text-xs font-medium tracking-[0.25em] text-[#8aa4bc] uppercase">
                Root Code
              </span>
            </div>
            <span className="text-[11px] tracking-wider text-[#8aa4bc] uppercase">
              Seguro Viagem
            </span>
          </div>
        </header>
        <Providers>{children}</Providers>
      </body>
    </html>
  );
}
