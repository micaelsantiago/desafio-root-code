# Desafio Root Code - Seguro Viagem

Motor de cotação de seguro viagem com API Laravel + frontend Next.js.

## Requisitos

- Docker e Docker Compose (recomendado)
- OU PHP 8.4+, Composer, Node.js 22+

## Como Rodar

### Com Docker (recomendado)

```bash
docker compose up -d

# Backend:  http://localhost:8000
# Frontend: http://localhost:3000
```

Sem Docker:

```bash
# Backend
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan serve --port=8000

# Frontend (outro terminal)
cd frontend
cp .env.example .env.local
npm install
npm run dev
```

### Backend - Testes

```bash
cd backend && php artisan test
```

22 testes, 54 asserções.

### Frontend - Build

```bash
cd frontend && npm run build
```

## API

`POST /api/quotes`

```json
{
  "destino": "EUROPA",
  "data_inicio": "2026-07-10",
  "data_fim": "2026-07-20",
  "viajantes": [
    {
      "nome": "Ana",
      "data_nascimento": "1990-03-15",
      "adicionais": ["BAGAGEM", "ESPORTES_AVENTURA"]
    }
  ]
}
```

## Decisões e Premissas

### Ordem de cálculo dos add-ons

`ESPORTES_AVENTURA` (25% sobre o subtotal) é calculado **antes** de somar `BAGAGEM` (R$ 3,00/dia). A base do ESPORTES é `tarifa × dias × multiplicador`, sem incluir bagagem. Inverter essa ordem é o erro mais comum.

### Período mínimo de 5 dias

Quando a viagem tem menos de 5 dias, o backend retorna um aviso no response: "Período mínimo de 5 dias cobrados...". A cotação segue normalmente.

### Singular/plural no aviso de período mínimo

A regra não especifica se o aviso deve usar singular ou plural quando `dias_reais = 1` (ex.: "1 dia" vs "1 dias"). Decidi tratar com `$diasReais === 1 ? 'dia' : 'dias'`. Singulares corretos para 1 dia, plural para os demais.

### Limite da faixa etária no aniversário

A regra diz "idade calculada na data de início", mas não explicita se o viajante que faz 18 ou 65 anos **exatamente** na `data_inicio` já conta na nova faixa. O PHP `DateTime::diff()->y` considera anos completos, então quem completa a idade no dia da viagem já entra na faixa superior (ex.: 65 anos → multiplicador 2,0). Decidi confiar no comportamento do PHP por ser o esperado para um seguro viagem.

### ESPORTES_AVENTURA para menores de idade

A regra diz "18 a 64 anos". Portanto, tanto 0-17 quanto 65+ estão fora. Ambos geram aviso - não só idosos. Decidi tratar explicitamente as duas bordas.

### Subtotal no JSON

O subtotal retornado pela API mantém precisão total (sem arredondamento). O `total_final` é o único valor arredondado (half-up, 2 casas). O frontend formata o subtotal com 2 casas apenas na exibição.

### Validação de data

`data_fim >= data_inicio` usa `after_or_equal` do Laravel (validação específica para datas), não `gte` (que compara como string).

### Estado no frontend

React Context + `useReducer`. A escolha evita dependências externas (Zustand, Redux) para um estado de formulário single-page.

### Layout de duas colunas

Formulário sempre visível à esquerda, resultado à direita. Resultado persiste durante re-submissão. Botão "Fechar" limpa apenas o resultado, mantendo os dados do formulário intactos.

### Arredondamento half-up

`round($valor, 2, PHP_ROUND_HALF_UP)` - arredondamento meio-para-cima.

### Paleta de cores

Azul claro e branco ("Coastal Light") com tipografia DM Serif Display (títulos) + Plus Jakarta Sans (corpo). Background com gradiente radial sutil e overlay de ruído para profundidade.
