import { useState, useEffect } from 'react';
import { invoke } from '@tauri-apps/api/core';
import { listen } from '@tauri-apps/api/event';
import {
  LayoutDashboard, Plane, Map, Ticket, BookOpen, Gauge
} from 'lucide-react';
import { Dashboard } from './Dashboard';
import { LandingAnalyserDetailed } from './LandingAnalyserDetailed';
import { Preflight } from './Preflight';
import { Logbook } from './Logbook';
import { FlightTracking } from './FlightTracking';
import { Bids } from './Bids';
import { AuthPanel } from './AuthPanel';

const iconMap: Record<string, React.ReactNode> = {
  dashboard: <LayoutDashboard size={18} />,
  preflight: <Plane size={18} />,
  tracking: <Map size={18} />,
  bids: <Ticket size={18} />,
  logbook: <BookOpen size={18} />,
  analyser: <Gauge size={18} />,
};

export const AppLayout = () => {
  const [activeTab, setActiveTab] = useState('dashboard');
  const [simState, setSimState] = useState('Disconnected');

  useEffect(() => {
    // Initial poll
    invoke<string>('simulator_state').then(s => setSimState(s)).catch(() => {});

    // Polling fallback every 3s
    const interval = setInterval(() => {
      invoke<string>('simulator_state').then(s => setSimState(s)).catch(() => {});
    }, 3000);

    // Real-time event from Rust tracking loop
    const unlisten = listen<string>('sim-state-changed', (event) => {
      setSimState(event.payload);
    });

    return () => {
      clearInterval(interval);
      unlisten.then(fn => fn());
    };
  }, []);


  const isConnected = simState === 'Connected';
  const isConnecting = simState === 'Connecting';

  const menu = [
    { id: 'dashboard', label: 'Dashboard' },
    { id: 'preflight', label: 'Preflight' },
    { id: 'tracking', label: 'Tracking' },
    { id: 'bids', label: 'Bids' },
    { id: 'logbook', label: 'Logbook' },
    { id: 'analyser', label: 'Landing Analyser' },
  ];

  return (
    <div className="flex h-screen bg-[#0a0a0a] text-slate-100 font-sans">
      <aside className="w-60 flex flex-col border-r border-slate-800/50 bg-[#0d0d0d]">
        <div className="p-5 border-b border-slate-800/50">
          <h1 className="text-lg font-bold tracking-tight">
            <span className="text-yellow-400">FlyAway</span>{' '}
            <span className="text-slate-400 font-normal">ACARS</span>
          </h1>
        </div>

        <nav className="flex-1 p-3 space-y-1">
          {menu.map((item) => {
            const isActive = activeTab === item.id;
            return (
              <button
                key={item.id}
                onClick={() => setActiveTab(item.id)}
                className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 ${
                  isActive
                    ? 'bg-yellow-400/10 text-yellow-400 border border-yellow-400/20'
                    : 'text-slate-500 hover:text-slate-300 hover:bg-slate-800/50 border border-transparent'
                }`}
              >
                {iconMap[item.id]}
                <span>{item.label}</span>
                {isActive && (
                  <span className="ml-auto w-1.5 h-1.5 rounded-full bg-yellow-400" />
                )}
              </button>
            );
          })}
        </nav>

        <div className="p-3 border-t border-slate-800/50">
          <AuthPanel />
        </div>
      </aside>

      <main className="flex-1 flex flex-col overflow-hidden">
        <header className="flex items-center justify-between px-8 py-4 border-b border-slate-800/50 bg-[#0d0d0d]">
          <h2 className="text-xl font-semibold text-slate-200">
            {menu.find(m => m.id === activeTab)?.label}
          </h2>
          <div className="flex items-center gap-3 text-xs text-slate-600">
            <span className="flex items-center gap-1.5">
              <span className={`w-2 h-2 rounded-full ${
                isConnected ? 'bg-green-500' :
                isConnecting ? 'bg-yellow-400 animate-pulse' :
                'bg-slate-600'
              }`} />
              {isConnected ? 'Simulator Online' :
               isConnecting ? 'Connecting...' :
               'Simulator Offline'}
            </span>
          </div>
        </header>

        <div className="flex-1 overflow-auto p-6">
          <div className="max-w-6xl mx-auto">
            {activeTab === 'dashboard' && <Dashboard />}
            {activeTab === 'preflight' && <Preflight isSimConnected={isConnected} onGoToTracking={() => setActiveTab('tracking')} />}
            {activeTab === 'tracking' && <FlightTracking />}
            {activeTab === 'bids' && <Bids />}
            {activeTab === 'logbook' && <Logbook />}
            {activeTab === 'analyser' && <LandingAnalyserDetailed />}
          </div>
        </div>
      </main>
    </div>
  );
};
