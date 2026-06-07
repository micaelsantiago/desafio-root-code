'use client';

import {
  createContext,
  useContext,
  useCallback,
  useReducer,
  type ReactNode,
} from 'react';
import {
  submitQuote,
  ApiError,
  type QuoteRequest,
  type QuoteResponse,
} from '@/lib/api';

type Destino = QuoteRequest['destino'];

export interface TravelerFormData {
  nome: string;
  data_nascimento: string;
  adicionais: string[];
}

export interface FormData {
  destino: Destino;
  data_inicio: string;
  data_fim: string;
  viajantes: TravelerFormData[];
}

interface QuoteState {
  form: FormData;
  result: QuoteResponse | null;
  loading: boolean;
  error: string | null;
  fieldErrors: Record<string, string[]> | null;
}

type QuoteAction =
  | { type: 'SET_FIELD'; field: keyof FormData; value: string }
  | { type: 'ADD_TRAVELER' }
  | { type: 'REMOVE_TRAVELER'; index: number }
  | { type: 'SET_TRAVELER_FIELD'; index: number; field: keyof TravelerFormData; value: string }
  | { type: 'TOGGLE_ADICIONAL'; index: number; adicional: string }
  | { type: 'SUBMIT_START' }
  | { type: 'SUBMIT_SUCCESS'; result: QuoteResponse }
  | { type: 'SUBMIT_ERROR'; error: string; fieldErrors: Record<string, string[]> | null }
  | { type: 'CLEAR_RESULT' }
  | { type: 'RESET' };

const emptyTraveler = (): TravelerFormData => ({
  nome: '',
  data_nascimento: '',
  adicionais: [],
});

const initialState: QuoteState = {
  form: {
    destino: 'NACIONAL',
    data_inicio: '',
    data_fim: '',
    viajantes: [emptyTraveler()],
  },
  result: null,
  loading: false,
  error: null,
  fieldErrors: null,
};

function reducer(state: QuoteState, action: QuoteAction): QuoteState {
  switch (action.type) {
    case 'SET_FIELD':
      return {
        ...state,
        form: { ...state.form, [action.field]: action.value },
      };

    case 'ADD_TRAVELER':
      return {
        ...state,
        form: {
          ...state.form,
          viajantes: [...state.form.viajantes, emptyTraveler()],
        },
      };

    case 'REMOVE_TRAVELER':
      return {
        ...state,
        form: {
          ...state.form,
          viajantes: state.form.viajantes.filter((_, i) => i !== action.index),
        },
      };

    case 'SET_TRAVELER_FIELD': {
      const viajantes = [...state.form.viajantes];
      viajantes[action.index] = {
        ...viajantes[action.index],
        [action.field]: action.value,
      };
      return { ...state, form: { ...state.form, viajantes } };
    }

    case 'TOGGLE_ADICIONAL': {
      const viajantes = [...state.form.viajantes];
      const adicionais = viajantes[action.index].adicionais;
      const idx = adicionais.indexOf(action.adicional);
      viajantes[action.index] = {
        ...viajantes[action.index],
        adicionais:
          idx >= 0
            ? adicionais.filter((a) => a !== action.adicional)
            : [...adicionais, action.adicional],
      };
      return { ...state, form: { ...state.form, viajantes } };
    }

    case 'SUBMIT_START':
      return { ...state, loading: true, error: null, fieldErrors: null };

    case 'SUBMIT_SUCCESS':
      return { ...state, loading: false, result: action.result };

    case 'SUBMIT_ERROR':
      return {
        ...state,
        loading: false,
        error: action.error,
        fieldErrors: action.fieldErrors,
      };

    case 'CLEAR_RESULT':
      return { ...state, result: null };

    case 'RESET':
      return initialState;

    default:
      return state;
  }
}

interface QuoteContextValue {
  state: QuoteState;
  setField: (field: keyof FormData, value: string) => void;
  addTraveler: () => void;
  removeTraveler: (index: number) => void;
  setTravelerField: (index: number, field: keyof TravelerFormData, value: string) => void;
  toggleAdicional: (index: number, adicional: string) => void;
  submit: () => Promise<void>;
  clearResult: () => void;
  reset: () => void;
}

const QuoteContext = createContext<QuoteContextValue | null>(null);

export function QuoteProvider({ children }: { children: ReactNode }) {
  const [state, dispatch] = useReducer(reducer, initialState);

  const setField = useCallback((field: keyof FormData, value: string) => {
    dispatch({ type: 'SET_FIELD', field, value });
  }, []);

  const addTraveler = useCallback(() => {
    dispatch({ type: 'ADD_TRAVELER' });
  }, []);

  const removeTraveler = useCallback((index: number) => {
    dispatch({ type: 'REMOVE_TRAVELER', index });
  }, []);

  const setTravelerField = useCallback(
    (index: number, field: keyof TravelerFormData, value: string) => {
      dispatch({ type: 'SET_TRAVELER_FIELD', index, field, value });
    },
    [],
  );

  const toggleAdicional = useCallback((index: number, adicional: string) => {
    dispatch({ type: 'TOGGLE_ADICIONAL', index, adicional });
  }, []);

  const submit = useCallback(async () => {
    dispatch({ type: 'SUBMIT_START' });

    const payload: QuoteRequest = {
      destino: state.form.destino,
      data_inicio: state.form.data_inicio,
      data_fim: state.form.data_fim,
      viajantes: state.form.viajantes.map((v) => ({
        nome: v.nome,
        data_nascimento: v.data_nascimento,
        adicionais: v.adicionais,
      })),
    };

    try {
      const result = await submitQuote(payload);
      dispatch({ type: 'SUBMIT_SUCCESS', result });
    } catch (err) {
      if (err instanceof ApiError) {
        if (err.errors && Object.keys(err.errors).length > 0) {
          dispatch({
            type: 'SUBMIT_ERROR',
            error: 'Verifique os campos destacados acima.',
            fieldErrors: err.errors,
          });
        } else {
          dispatch({
            type: 'SUBMIT_ERROR',
            error: err.message,
            fieldErrors: null,
          });
        }
      } else {
        dispatch({
          type: 'SUBMIT_ERROR',
          error: 'Erro de conexão com o servidor',
          fieldErrors: null,
        });
      }
    }
  }, [state.form]);

  const clearResult = useCallback(() => {
    dispatch({ type: 'CLEAR_RESULT' });
  }, []);

  const reset = useCallback(() => {
    dispatch({ type: 'RESET' });
  }, []);

  return (
    <QuoteContext.Provider
      value={{
        state,
        setField,
        addTraveler,
        removeTraveler,
        setTravelerField,
        toggleAdicional,
        submit,
        clearResult,
        reset,
      }}
    >
      {children}
    </QuoteContext.Provider>
  );
}

export function useQuote(): QuoteContextValue {
  const ctx = useContext(QuoteContext);
  if (!ctx) throw new Error('useQuote must be used within QuoteProvider');
  return ctx;
}
