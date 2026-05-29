import { useEffect, useState, useRef } from 'react';
import { invoke } from '@tauri-apps/api/core';
import { listen } from '@tauri-apps/api/event';
import { Plane, Send, ArrowRight, Loader2, Ticket, Map, Activity, CheckCircle2 } from 'lucide-react';

interface Schedule {
  id: number;
  flight_number: string;
  departure: string;
  arrival: string;
  aircraft_type: string | null;
  flight_time: number | null;
  route: string | null;
}

interface AircraftInfo {
  id: number;
  registration: string;
  icao: string;
  name: string;
  location: string | null;
}

interface Bid {
  id: number;
  user_id: number;
  schedule_id: number | null;
  aircraft_id: number | null;
  schedule: Schedule | null;
  aircraft: AircraftInfo | null;
}

interface FlightForm {
  flight_number: string;
  aircraft_registration: string;
  aircraft_icao: string;
  aircraft_type: string;
  departure: string;
  arrival: string;
}

interface LiveTelemetry {
  altitude: number;
  ground_speed: number;
  heading: number;
  phase: string;
  current_lat: number;
  current_lng: number;
}

interface PreflightProps {
  isSimConnected?: boolean;
  onGoToTracking?: () => void;
}

export const Preflight = ({ isSimConnected = false, onGoToTracking }: PreflightProps) => {
  const [schedules, setSchedules] = useState<Schedule[]>([]);
  const [aircraft, setAircraft] = useState<AircraftInfo[]>([]);
  const [bids, setBids] = useState<Bid[]>([]);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [form, setForm] = useState<FlightForm>({
    flight_number: '', aircraft_registration: '', aircraft_icao: '',
    aircraft_type: '', departure: '', arrival: '',
  });
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [liveTelemetry, setLiveTelemetry] = useState<LiveTelemetry | null>(null);
  const [flightLogs, setFlightLogs] = useState<string[]>([]);
  const logsEndRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    Promise.all([
      invoke<Schedule[]>('fetch_schedules').catch(() => []),
      invoke<AircraftInfo[]>('fetch_aircraft').catch(() => []),
      invoke<Bid[]>('fetch_my_reservations').catch(() => []),
    ]).then(([sched, ac, bd]) => {
      setSchedules(Array.isArray(sched) ? sched : []);
      setAircraft(Array.isArray(ac) ? ac : []);
      setBids(Array.isArray(bd) ? bd : []);
      setLoading(false);
    });
  }, []);

  // Listen to real-time telemetry events from simulator
  useEffect(() => {
    if (!submitted) return;

    const unlisten = listen<LiveTelemetry>('telemetry-updated', (event) => {
      const t = event.payload;
      setLiveTelemetry(t);

      // Add log entries for phase changes
      if (t.phase) {
        const phaseLabel = phaseDisplayName(t.phase);
        setFlightLogs(prev => {
          const last = prev[prev.length - 1];
          const newEntry = `[${new Date().toLocaleTimeString()}] Phase: ${phaseLabel} | ALT: ${t.altitude.toLocaleString()}ft | SPD: ${t.ground_speed}kt`;
          if (!last || !last.includes(phaseLabel)) {
            return [...prev.slice(-49), newEntry];
          }
          return prev;
        });
      }
    });

    return () => { unlisten.then(fn => fn()); };
  }, [submitted]);

  // Auto scroll logs to bottom
  useEffect(() => {
    logsEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [flightLogs]);

  const phaseDisplayName = (phase: string): string => {
    const map: Record<string, string> = {
      preflight: '🛫 Pre-Flight',
      boarding: '🧳 Boarding',
      departed: '🛬 Departed / Taxi',
      enroute: '✈️ En Route',
      onapproach: '📡 On Approach',
      landed: '🛬 Landed',
    };
    return map[phase] ?? phase;
  };

  const selectSchedule = (s: Schedule) => {
    setForm({
      flight_number: s.flight_number,
      aircraft_registration: '',
      aircraft_icao: s.aircraft_type ?? '',
      aircraft_type: s.aircraft_type ?? '',
      departure: s.departure,
      arrival: s.arrival,
    });
  };

  const selectAircraft = (ac: AircraftInfo) => {
    setForm(prev => ({ ...prev, aircraft_registration: ac.registration, aircraft_icao: ac.icao }));
  };

  const selectBid = (b: Bid) => {
    setForm({
      flight_number: b.schedule?.flight_number ?? '',
      aircraft_registration: b.aircraft?.registration ?? '',
      aircraft_icao: b.aircraft?.icao ?? b.schedule?.aircraft_type ?? '',
      aircraft_type: b.aircraft?.name ?? b.schedule?.aircraft_type ?? '',
      departure: b.schedule?.departure ?? '',
      arrival: b.schedule?.arrival ?? '',
    });
  };

  const handleSubmit = async () => {
    const missing = (Object.entries(form) as [string, string][])
      .filter(([, v]) => !v.trim())
      .map(([k]) => k);
    if (missing.length) {
      setError(`Fill in: ${missing.join(', ')}`);
      return;
    }
    setError(null);
    setSubmitting(true);

    try {
      await invoke('set_flight_context', { ctx: form });
      setFlightLogs([`[${new Date().toLocaleTimeString()}] ✅ Flight context set: ${form.flight_number} (${form.departure} → ${form.arrival})`]);
      setSubmitting(false);
      setSubmitted(true);
    } catch (e) {
      setError(typeof e === 'string' ? e : 'Failed to set flight context');
      setSubmitting(false);
    }
  };

  if (submitted) {
    return (
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Left: Flight Info + Status */}
        <div className="space-y-4">
          <div className="bg-[#111111] border border-slate-800/50 rounded-xl p-6">
            <div className="flex items-center gap-4 mb-6">
              <div className="w-14 h-14 rounded-full bg-yellow-400/10 border border-yellow-400/20 flex items-center justify-center">
                <Plane size={24} className="text-yellow-400" />
              </div>
              <div>
                <h2 className="text-lg font-bold text-slate-200">Flight Set</h2>
                <p className="text-sm text-slate-500">{form.flight_number} · {form.departure} → {form.arrival}</p>
                <p className="text-xs text-slate-700 mt-0.5">{form.aircraft_icao} · {form.aircraft_registration}</p>
              </div>
            </div>

            {/* Simulator Status */}
            <div className={`flex items-center gap-3 rounded-lg p-3 border mb-4 ${
              isSimConnected
                ? 'bg-green-500/5 border-green-500/20'
                : 'bg-yellow-400/5 border-yellow-400/20'
            }`}>
              <span className={`w-2.5 h-2.5 rounded-full flex-shrink-0 ${
                isSimConnected ? 'bg-green-500 animate-pulse' : 'bg-yellow-400 animate-pulse'
              }`} />
              <div>
                <p className={`text-sm font-medium ${isSimConnected ? 'text-green-400' : 'text-yellow-400'}`}>
                  {isSimConnected ? 'Simulator Online — Tracking Active' : 'Waiting for Simulator...'}
                </p>
                <p className="text-xs text-slate-600 mt-0.5">
                  {isSimConnected
                    ? 'Telemetry is being sent to FlyAway servers in real time.'
                    : 'Connect MSFS 2024 to start flight tracking automatically.'}
                </p>
              </div>
            </div>

            {/* Live Telemetry */}
            {isSimConnected && liveTelemetry && (
              <div className="grid grid-cols-3 gap-2 mb-4">
                <div className="bg-[#161616] border border-slate-800/40 rounded-lg p-3 text-center">
                  <div className="text-[10px] text-slate-600 uppercase tracking-wider mb-1">Altitude</div>
                  <div className="text-sm font-mono font-bold text-slate-200">{liveTelemetry.altitude.toLocaleString()}<span className="text-xs text-slate-600 ml-1">ft</span></div>
                </div>
                <div className="bg-[#161616] border border-slate-800/40 rounded-lg p-3 text-center">
                  <div className="text-[10px] text-slate-600 uppercase tracking-wider mb-1">Speed</div>
                  <div className="text-sm font-mono font-bold text-slate-200">{liveTelemetry.ground_speed}<span className="text-xs text-slate-600 ml-1">kt</span></div>
                </div>
                <div className="bg-[#161616] border border-slate-800/40 rounded-lg p-3 text-center">
                  <div className="text-[10px] text-slate-600 uppercase tracking-wider mb-1">Phase</div>
                  <div className="text-xs font-bold text-yellow-400 truncate">{phaseDisplayName(liveTelemetry.phase)}</div>
                </div>
              </div>
            )}

            {/* Action Buttons */}
            <div className="flex gap-3 mt-2">
              {isSimConnected && onGoToTracking && (
                <button
                  onClick={onGoToTracking}
                  className="flex-1 flex items-center justify-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-black font-semibold rounded-lg px-4 py-2.5 text-sm transition-colors"
                >
                  <Map size={16} />
                  Go to Tracking
                </button>
              )}
              <button
                onClick={async () => {
                  try {
                    await invoke('cancel_flight');
                    setSubmitted(false);
                    setLiveTelemetry(null);
                    setFlightLogs([]);
                    setForm({ flight_number: '', aircraft_registration: '', aircraft_icao: '', aircraft_type: '', departure: '', arrival: '' });
                  } catch (e) {
                    setError(typeof e === 'string' ? e : 'Failed to cancel flight');
                  }
                }}
                className="flex items-center justify-center gap-2 px-4 py-2.5 text-sm text-slate-500 hover:text-slate-400 border border-slate-800/50 rounded-lg transition-colors"
              >
                Edit Flight
              </button>
              <button
                onClick={async () => {
                  try {
                    await invoke('complete_flight_with_pirep');
                    setSubmitted(false);
                    setLiveTelemetry(null);
                    setFlightLogs([]);
                    setForm({ flight_number: '', aircraft_registration: '', aircraft_icao: '', aircraft_type: '', departure: '', arrival: '' });
                  } catch (e) {
                    setError(typeof e === 'string' ? e : 'Failed to submit PIREP');
                  }
                }}
                className="flex items-center justify-center gap-2 px-4 py-2.5 text-sm text-yellow-400 hover:text-yellow-300 border border-yellow-400/20 rounded-lg transition-colors font-medium"
              >
                <CheckCircle2 size={15} />
                Submit PIREP
              </button>
            </div>
            {error && <p className="text-xs text-red-400 mt-3">{error}</p>}
          </div>
        </div>

        {/* Right: Flight Logs */}
        <div className="bg-[#111111] border border-slate-800/50 rounded-xl p-4 flex flex-col" style={{ minHeight: 320 }}>
          <div className="flex items-center gap-2 mb-3 pb-3 border-b border-slate-800/50">
            <Activity size={14} className="text-yellow-400" />
            <h3 className="text-xs font-semibold text-slate-400 uppercase tracking-wider">Flight Logs</h3>
            {isSimConnected && <span className="ml-auto w-2 h-2 rounded-full bg-green-500 animate-pulse" />}
          </div>
          <div className="flex-1 overflow-y-auto font-mono text-xs space-y-1 text-slate-500 max-h-64">
            {flightLogs.length === 0 ? (
              <p className="text-slate-700 italic">Waiting for telemetry events...</p>
            ) : (
              flightLogs.map((log, i) => (
                <div key={i} className="text-slate-500 leading-relaxed">{log}</div>
              ))
            )}
            <div ref={logsEndRef} />
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="grid grid-cols-1 lg:grid-cols-5 gap-6">
      <div className="lg:col-span-3 space-y-4">
        {bids.length > 0 && (
          <div className="bg-[#111111] border border-slate-800/50 rounded-xl p-4">
            <h3 className="text-xs font-semibold text-yellow-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
              <Ticket size={14} />
              My Booked Flights (Bids)
            </h3>
            <div className="space-y-2 max-h-56 overflow-y-auto pr-1">
              {bids.map(b => {
                const isSelected = form.flight_number === b.schedule?.flight_number &&
                                   form.aircraft_registration === b.aircraft?.registration;
                return (
                  <button
                    key={b.id}
                    onClick={() => selectBid(b)}
                    className={`w-full text-left p-3 rounded-xl border transition-all duration-150 relative overflow-hidden group ${
                      isSelected
                        ? 'bg-yellow-400/10 border-yellow-400/30 text-yellow-400'
                        : 'bg-slate-900/10 border-slate-800/60 text-slate-300 hover:border-slate-700 hover:bg-slate-800/20'
                    }`}
                  >
                    <div className="flex items-center justify-between mb-1.5">
                      <div className="flex items-center gap-2">
                        <span className="font-bold text-sm tracking-wide">
                          {b.schedule?.flight_number ?? 'Flight'}
                        </span>
                        <span className="text-[10px] px-2 py-0.5 rounded bg-slate-800/80 text-slate-400 border border-slate-700/30">
                          {b.aircraft?.icao ?? b.schedule?.aircraft_type ?? '—'}
                        </span>
                      </div>
                      <span className="text-[10px] text-slate-500 font-mono">
                        Bid #{b.id}
                      </span>
                    </div>
                    <div className="flex items-center gap-4 text-xs">
                      <div>
                        <span className="text-slate-600 mr-1">Route:</span>
                        <span className="font-medium text-slate-300 group-hover:text-white">
                          {b.schedule?.departure} &rarr; {b.schedule?.arrival}
                        </span>
                      </div>
                      <div>
                        <span className="text-slate-600 mr-1">Aircraft:</span>
                        <span className="font-mono text-slate-300 group-hover:text-white">
                          {b.aircraft?.registration ?? '—'}
                        </span>
                      </div>
                    </div>
                  </button>
                );
              })}
            </div>
          </div>
        )}

        <div className="bg-[#111111] border border-slate-800/50 rounded-xl p-4">
          <h3 className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Available Schedules</h3>
          {loading ? (
            <div className="text-sm text-slate-600 py-4 text-center">Loading schedules...</div>
          ) : schedules.length === 0 ? (
            <div className="text-sm text-slate-600 py-4 text-center">No schedules available.</div>
          ) : (
            <div className="max-h-64 overflow-y-auto space-y-1">
              {schedules.map(s => (
                <button
                  key={s.id}
                  onClick={() => selectSchedule(s)}
                  className={`w-full text-left px-3 py-2 rounded-lg text-sm transition-colors ${
                    form.flight_number === s.flight_number
                      ? 'bg-yellow-400/10 border border-yellow-400/20 text-yellow-400'
                      : 'text-slate-400 hover:bg-slate-800/30 border border-transparent'
                  }`}
                >
                  <span className="font-medium">{s.flight_number}</span>
                  <span className="text-slate-600 mx-2">&rarr;</span>
                  <span>{s.departure}</span>
                  <span className="text-slate-600 mx-1">-</span>
                  <span>{s.arrival}</span>
                  {s.aircraft_type && <span className="text-slate-600 ml-2 text-[10px]">{s.aircraft_type}</span>}
                </button>
              ))}
            </div>
          )}
        </div>

        <div className="bg-[#111111] border border-slate-800/50 rounded-xl p-4">
          <h3 className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Aircraft</h3>
          {loading ? (
            <div className="text-sm text-slate-600 py-4 text-center">Loading aircraft...</div>
          ) : (
            <div className="max-h-40 overflow-y-auto space-y-1">
              {aircraft.map(a => (
                <button
                  key={a.id}
                  onClick={() => selectAircraft(a)}
                  className={`w-full text-left px-3 py-2 rounded-lg text-sm transition-colors ${
                    form.aircraft_registration === a.registration
                      ? 'bg-yellow-400/10 border border-yellow-400/20 text-yellow-400'
                      : 'text-slate-400 hover:bg-slate-800/30 border border-transparent'
                  }`}
                >
                  <span className="font-medium">{a.registration}</span>
                  <span className="text-slate-600 ml-2">{a.icao}</span>
                  <span className="text-slate-600 ml-2 text-[10px]">{a.name}</span>
                  <span className="text-slate-700 ml-2 text-[10px]">{a.location ?? '—'}</span>
                </button>
              ))}
            </div>
          )}
        </div>
      </div>

      <div className="lg:col-span-2">
        <div className="bg-[#111111] border border-slate-800/50 rounded-xl p-5 space-y-4">
          <div className="flex items-center gap-3 pb-4 border-b border-slate-800/50">
            <Plane size={20} className="text-yellow-400" />
            <h2 className="text-lg font-semibold text-slate-200">Flight Details</h2>
          </div>

          <div>
            <label className="text-xs text-slate-600 mb-1.5 block">Flight Number</label>
            <input value={form.flight_number} readOnly
              className="w-full bg-slate-800/20 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm text-slate-400 cursor-not-allowed" />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="text-xs text-slate-600 mb-1.5 block">Registration</label>
              <input value={form.aircraft_registration} readOnly
                className="w-full bg-slate-800/20 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm text-slate-400 cursor-not-allowed" />
            </div>
            <div>
              <label className="text-xs text-slate-600 mb-1.5 block">ICAO</label>
              <input value={form.aircraft_icao} readOnly
                className="w-full bg-slate-800/20 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm text-slate-400 cursor-not-allowed" />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="text-xs text-slate-600 mb-1.5 block">Departure</label>
              <input value={form.departure} readOnly
                className="w-full bg-slate-800/20 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm text-slate-400 cursor-not-allowed" />
            </div>
            <div>
              <label className="text-xs text-slate-600 mb-1.5 block">Arrival</label>
              <input value={form.arrival} readOnly
                className="w-full bg-slate-800/20 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm text-slate-400 cursor-not-allowed" />
            </div>
          </div>

          {error && <p className="text-xs text-red-400">{error}</p>}

          <button
            onClick={handleSubmit}
            disabled={submitting}
            className="w-full flex items-center justify-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-black font-semibold rounded-lg px-4 py-2.5 text-sm transition-colors disabled:opacity-50"
          >
            {submitting ? <Loader2 size={16} className="animate-spin" /> : <Send size={16} />}
            {submitting ? 'Setting...' : 'Start Flight'}
            <ArrowRight size={16} />
          </button>
        </div>
      </div>
    </div>
  );
};
