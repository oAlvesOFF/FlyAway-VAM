import { useState } from 'react';
import { invoke } from '@tauri-apps/api/core';
import { saveApiKey } from '../services/authService';
import { Eye, EyeOff, LogIn, User, CheckCircle, XCircle, AlertTriangle } from 'lucide-react';

interface UserInfo {
  id: number;
  name: string;
  pilot_id: string;
  total_hours: number;
  total_flights: number;
  rank: { name: string } | null;
}

interface PirepWithUser {
  user: UserInfo | null;
}

export const AuthPanel = () => {
  const [expanded, setExpanded] = useState(false);
  const [apiKey, setApiKey] = useState('');
  const [showKey, setShowKey] = useState(false);
  const [user, setUser] = useState<UserInfo | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async () => {
    if (!apiKey.trim()) return;
    setLoading(true);
    setError(null);

    try {
      await invoke('set_api_key', { key: apiKey.trim() });
      await saveApiKey(apiKey.trim());
    } catch (err) {
      setError('Erro ao guardar chave');
      setLoading(false);
      return;
    }

    // 1. Método principal: fetch_me (/api/me)
    try {
      const me = await invoke<UserInfo>('fetch_me');
      if (me && me.name) {
        setUser(me);
        if (me.pilot_id) {
          await invoke('set_pilot_id', { pilotId: me.pilot_id });
        }
        setExpanded(false);
        setLoading(false);
        return;
      }
    } catch (err) {
      const msg = typeof err === 'string' ? err : String(err);
      if (msg.includes('401') || msg.includes('invalid token') || msg.includes('Unauthorized')) {
        setError('API Key Inválida');
        invoke('set_api_key', { key: '' });
        setLoading(false);
        return;
      }
      console.warn('fetch_me falhou, a tentar fetch_pireps como fallback:', err);
    }

    // 2. Fallback: fetch_pireps
    try {
      const pireps = await invoke<PirepWithUser[]>('fetch_pireps');
      if (Array.isArray(pireps) && pireps.length > 0) {
        const firstWithUser = pireps.find(p => p.user != null);
        if (firstWithUser?.user) {
          setUser(firstWithUser.user);
          await invoke('set_pilot_id', { pilotId: firstWithUser.user.pilot_id });
          setExpanded(false);
          setLoading(false);
          return;
        }
      }
    } catch (err) {
      const msg = typeof err === 'string' ? err : String(err);
      if (msg.includes('401')) {
        setError('API Key Inválida');
        invoke('set_api_key', { key: '' });
        setLoading(false);
        return;
      }
      console.warn('fetch_pireps também falhou:', err);
    }

    // 3. Ambos falharam — servidor instável
    setUser({ id: 0, name: 'Pilot (Dados indisponíveis)', pilot_id: '', total_hours: 0, total_flights: 0, rank: null });
    setError('Aviso: servidor instável — login aceite, dados limitados');
    setExpanded(false);
    setLoading(false);
  };

  if (user) {
    return (
      <div className="space-y-2 px-1">
        <div className="flex items-center gap-2 text-xs text-green-400">
          <CheckCircle size={12} />
          <span className="flex-1 truncate">Autenticado</span>
        </div>
        {error && (
            <div className="flex items-center gap-2 text-xs text-yellow-400">
                <AlertTriangle size={12} />
                <span className="truncate">Erro no Servidor</span>
            </div>
        )}
        <div className="flex items-center gap-2 text-xs text-slate-400">
          <User size={12} />
          <span className="flex-1 truncate">{user.name}{user.pilot_id ? ` (${user.pilot_id})` : ''}</span>
        </div>
        {user.rank && (
          <div className="text-[10px] text-slate-600 pl-5">{user.rank.name}</div>
        )}
        <button
          onClick={() => { setUser(null); setApiKey(''); setError(null); }}
          className="flex items-center gap-1.5 px-1 text-[10px] text-slate-600 hover:text-slate-400 transition-colors"
        >
          <XCircle size={10} /> sair
        </button>
      </div>
    );
  }

  return (
    <div>
      {!expanded ? (
        <button
          onClick={() => setExpanded(true)}
          className="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-slate-500 hover:text-slate-300 hover:bg-slate-800/50 transition-all"
        >
          <LogIn size={16} />
          <span>Configurar API Key</span>
        </button>
      ) : (
        <div className="space-y-2 px-1">
          <div className="relative">
            <input
              type={showKey ? 'text' : 'password'}
              value={apiKey}
              onChange={e => setApiKey(e.target.value)}
              placeholder="Cole sua API key"
              className="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-yellow-400/50 transition-colors"
              autoFocus
              onKeyDown={e => e.key === 'Enter' && handleSubmit()}
            />
            <button
              onClick={() => setShowKey(!showKey)}
              className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-600 hover:text-slate-400"
            >
              {showKey ? <EyeOff size={14} /> : <Eye size={14} />}
            </button>
          </div>
          {error && <p className="text-xs text-red-400">{error}</p>}
          <div className="flex gap-2">
            <button
              onClick={handleSubmit}
              disabled={loading}
              className="flex-1 bg-yellow-400/10 border border-yellow-400/20 text-yellow-400 rounded-lg py-1.5 text-xs font-medium hover:bg-yellow-400/20 transition-colors disabled:opacity-50"
            >
              {loading ? 'A verificar...' : 'Guardar'}
            </button>
            <button
              onClick={() => { setExpanded(false); setError(null); }}
              className="px-3 py-1.5 text-xs text-slate-600 hover:text-slate-400 transition-colors"
            >
              Cancelar
            </button>
          </div>
        </div>
      )}
    </div>
  );
};
