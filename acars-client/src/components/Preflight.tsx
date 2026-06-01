import { useEffect, useState, useRef } from 'react';
import { invoke } from '@tauri-apps/api/core';
import { listen } from '@tauri-apps/api/event';
import { Plane, Send, ArrowRight, ArrowLeft, Loader2, Ticket, Map, Activity, CheckCircle2, Search, Calendar, AlertCircle, PlaneTakeoff, PlaneLanding, Fingerprint, MapPin } from 'lucide-react';
import { saveLocalFlight, PirepRecord as LocalPirepRecord } from '../services/store';
import { motion, AnimatePresence } from 'framer-motion';
import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

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

const slideVariants = {
  enter: (direction: number) => ({
    x: direction > 0 ? 20 : -20,
    opacity: 0,
  }),
  center: {
    zIndex: 1,
    x: 0,
    opacity: 1,
  },
  exit: (direction: number) => ({
    zIndex: 0,
    x: direction < 0 ? 20 : -20,
    opacity: 0,
  }),
};

export const Preflight = ({ isSimConnected = false, onGoToTracking }: PreflightProps) => {
  const [schedules, setSchedules] = useState<Schedule[]>([]);
  const [aircraft, setAircraft] = useState<AircraftInfo[]>([]);
  const [bids, setBids] = useState<Bid[]>([]);
  
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState<string | null>(null);
  
  const [step, setStep] = useState(1);
  const [direction, setDirection] = useState(1);
  const [searchFlight, setSearchFlight] = useState('');
  const [searchAircraft, setSearchAircraft] = useState('');

  const [form, setForm] = useState<FlightForm>({
    flight_number: '', aircraft_registration: '', aircraft_icao: '',
    aircraft_type: '', departure: '', arrival: '',
  });

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

  useEffect(() => {
    if (!submitted) return;

    const unlisten = listen<LiveTelemetry>('telemetry-updated', (event) => {
      const t = event.payload;
      setLiveTelemetry(t);

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

  const goToStep = (newStep: number) => {
    setDirection(newStep > step ? 1 : -1);
    setStep(newStep);
    setError(null);
  };

  const selectSchedule = (s: Schedule) => {
    setForm(prev => ({
      ...prev,
      flight_number: s.flight_number,
      aircraft_icao: s.aircraft_type ?? '',
      aircraft_type: s.aircraft_type ?? '',
      departure: s.departure,
      arrival: s.arrival,
      aircraft_registration: '', // reset aircraft if changing schedule
    }));
    goToStep(2);
  };

  const selectAircraft = (ac: AircraftInfo) => {
    setForm(prev => ({ ...prev, aircraft_registration: ac.registration, aircraft_icao: ac.icao }));
    goToStep(3);
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
    // Bids already have both flight and aircraft usually, jump to step 3
    goToStep(3);
  };

  const handleSubmit = async () => {
    const missing = (Object.entries(form) as [string, string][])
      .filter(([, v]) => !v.trim())
      .map(([k]) => k.replace('aircraft_', '').replace('_', ' '));
      
    if (missing.length) {
      setError(`Required: ${missing.join(', ')}`);
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

  const filteredBids = bids.filter(b => {
    const q = searchFlight.toLowerCase();
    return b.schedule?.flight_number.toLowerCase().includes(q) || 
           b.schedule?.departure.toLowerCase().includes(q) ||
           b.schedule?.arrival.toLowerCase().includes(q);
  });

  const filteredSchedules = schedules.filter(s => {
    const q = searchFlight.toLowerCase();
    return s.flight_number.toLowerCase().includes(q) || 
           s.departure.toLowerCase().includes(q) ||
           s.arrival.toLowerCase().includes(q) ||
           (s.aircraft_type?.toLowerCase().includes(q));
  });

  const filteredAircraft = aircraft.filter(a => {
    const q = searchAircraft.toLowerCase();
    return a.registration.toLowerCase().includes(q) ||
           a.icao.toLowerCase().includes(q) ||
           a.name.toLowerCase().includes(q) ||
           (a.location?.toLowerCase().includes(q));
  });

  // ========== RENDER SUBMITTED ==========
  if (submitted) {
    return (
      <motion.div 
        initial={{ opacity: 0, scale: 0.98 }} 
        animate={{ opacity: 1, scale: 1 }} 
        className="grid grid-cols-1 lg:grid-cols-2 gap-6"
      >
        {/* Left: Flight Info + Status */}
        <div className="space-y-4">
          <div className="bg-slate-900/40 backdrop-blur-xl border border-slate-800/50 rounded-2xl p-6 relative overflow-hidden">
            <div className="absolute top-0 right-0 w-32 h-32 bg-yellow-400/5 rounded-full blur-3xl -mr-10 -mt-10 pointer-events-none" />
            
            <div className="flex items-center gap-5 mb-8">
              <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-yellow-400/20 to-yellow-500/5 border border-yellow-400/30 flex items-center justify-center shadow-lg shadow-yellow-500/10">
                <Plane size={28} className="text-yellow-400" />
              </div>
              <div>
                <div className="flex items-center gap-2 mb-1">
                  <h2 className="text-2xl font-bold text-white tracking-tight">{form.flight_number}</h2>
                  <span className="px-2 py-0.5 rounded-md bg-slate-800 text-xs text-slate-300 font-medium border border-slate-700/50">
                    {form.aircraft_icao}
                  </span>
                </div>
                <div className="flex items-center gap-2 text-slate-400 text-sm font-medium">
                  <span className="text-white">{form.departure}</span>
                  <ArrowRight size={14} className="text-slate-600" />
                  <span className="text-white">{form.arrival}</span>
                  <span className="text-slate-600 mx-1">·</span>
                  <span className="font-mono text-xs">{form.aircraft_registration}</span>
                </div>
              </div>
            </div>

            {/* Simulator Status */}
            <div className={cn(
              "flex items-center gap-4 rounded-xl p-4 border transition-all duration-500 mb-6",
              isSimConnected
                ? "bg-green-500/10 border-green-500/20 shadow-[0_0_15px_rgba(34,197,94,0.1)]"
                : "bg-yellow-400/10 border-yellow-400/20 shadow-[0_0_15px_rgba(250,204,21,0.05)]"
            )}>
              <div className="relative flex h-3 w-3 flex-shrink-0">
                <span className={cn(
                  "animate-ping absolute inline-flex h-full w-full rounded-full opacity-75",
                  isSimConnected ? "bg-green-400" : "bg-yellow-400"
                )}></span>
                <span className={cn(
                  "relative inline-flex rounded-full h-3 w-3",
                  isSimConnected ? "bg-green-500" : "bg-yellow-500"
                )}></span>
              </div>
              <div>
                <p className={cn("text-sm font-semibold", isSimConnected ? "text-green-400" : "text-yellow-400")}>
                  {isSimConnected ? 'Simulator Online — Tracking Active' : 'Waiting for Simulator...'}
                </p>
                <p className="text-xs text-slate-400 mt-0.5">
                  {isSimConnected
                    ? 'Telemetry is being sent to FlyAway servers in real time.'
                    : 'Connect MSFS 2024 to start flight tracking automatically.'}
                </p>
              </div>
            </div>

            {/* Live Telemetry */}
            {isSimConnected && liveTelemetry && (
              <div className="grid grid-cols-3 gap-3 mb-6">
                <div className="bg-slate-950/50 border border-slate-800/60 rounded-xl p-4 text-center relative overflow-hidden group">
                  <div className="absolute inset-0 bg-gradient-to-t from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
                  <div className="text-[10px] text-slate-500 font-semibold uppercase tracking-widest mb-1.5">Altitude</div>
                  <div className="text-lg font-mono font-bold text-white">{liveTelemetry.altitude.toLocaleString()}<span className="text-xs text-slate-500 ml-1 font-sans">ft</span></div>
                </div>
                <div className="bg-slate-950/50 border border-slate-800/60 rounded-xl p-4 text-center relative overflow-hidden group">
                  <div className="absolute inset-0 bg-gradient-to-t from-amber-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
                  <div className="text-[10px] text-slate-500 font-semibold uppercase tracking-widest mb-1.5">Speed</div>
                  <div className="text-lg font-mono font-bold text-white">{liveTelemetry.ground_speed}<span className="text-xs text-slate-500 ml-1 font-sans">kt</span></div>
                </div>
                <div className="bg-slate-950/50 border border-slate-800/60 rounded-xl p-4 text-center relative overflow-hidden group">
                  <div className="absolute inset-0 bg-gradient-to-t from-yellow-400/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
                  <div className="text-[10px] text-slate-500 font-semibold uppercase tracking-widest mb-1.5">Phase</div>
                  <div className="text-sm font-bold text-yellow-400 truncate mt-1">{phaseDisplayName(liveTelemetry.phase)}</div>
                </div>
              </div>
            )}

            {/* Action Buttons */}
            <div className="flex gap-3 pt-2">
              {isSimConnected && onGoToTracking && (
                <button
                  onClick={onGoToTracking}
                  className="flex-1 flex items-center justify-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-black font-bold rounded-xl px-4 py-3 text-sm transition-all shadow-[0_0_20px_rgba(250,204,21,0.2)] hover:shadow-[0_0_25px_rgba(250,204,21,0.4)]"
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
                    goToStep(1);
                  } catch (e) {
                    setError(typeof e === 'string' ? e : 'Failed to cancel flight');
                  }
                }}
                className="flex items-center justify-center gap-2 px-4 py-3 text-sm text-slate-400 hover:text-white bg-slate-900/50 hover:bg-slate-800 border border-slate-700/50 rounded-xl transition-colors font-medium"
              >
                Edit Flight
              </button>
              <button
                onClick={async () => {
                  try {
                    const pirep = await invoke<LocalPirepRecord>('complete_flight_with_pirep');
                    await saveLocalFlight(pirep);
                    setSubmitted(false);
                    setLiveTelemetry(null);
                    setFlightLogs([]);
                    setForm({ flight_number: '', aircraft_registration: '', aircraft_icao: '', aircraft_type: '', departure: '', arrival: '' });
                    goToStep(1);
                  } catch (e) {
                    setError(typeof e === 'string' ? e : 'Failed to submit PIREP');
                  }
                }}
                className="flex items-center justify-center gap-2 px-5 py-3 text-sm text-yellow-400 hover:text-black hover:bg-yellow-400 border border-yellow-400/30 rounded-xl transition-all duration-300 font-bold"
              >
                <CheckCircle2 size={16} />
                Submit PIREP
              </button>
            </div>
            {error && <p className="text-xs font-medium text-red-400 mt-4 bg-red-400/10 p-3 rounded-lg border border-red-400/20">{error}</p>}
          </div>
        </div>

        {/* Right: Flight Logs */}
        <div className="bg-slate-900/40 backdrop-blur-xl border border-slate-800/50 rounded-2xl p-5 flex flex-col shadow-xl" style={{ minHeight: 400 }}>
          <div className="flex items-center gap-2 mb-4 pb-4 border-b border-slate-800/50">
            <Activity size={16} className="text-yellow-400" />
            <h3 className="text-xs font-bold text-slate-300 uppercase tracking-widest">Flight Logs</h3>
            {isSimConnected && <span className="ml-auto flex h-2 w-2 relative">
              <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
              <span className="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
            </span>}
          </div>
          <div className="flex-1 overflow-y-auto font-mono text-xs space-y-2 pr-2 custom-scrollbar">
            {flightLogs.length === 0 ? (
              <div className="h-full flex flex-col items-center justify-center text-slate-600 space-y-3">
                <Activity size={24} className="opacity-20" />
                <p className="italic">Waiting for telemetry events...</p>
              </div>
            ) : (
              flightLogs.map((log, i) => (
                <motion.div 
                  initial={{ opacity: 0, x: -10 }}
                  animate={{ opacity: 1, x: 0 }}
                  key={i} 
                  className="text-slate-400 leading-relaxed bg-slate-950/40 px-3 py-2 rounded-lg border border-slate-800/30"
                >
                  {log}
                </motion.div>
              ))
            )}
            <div ref={logsEndRef} />
          </div>
        </div>
      </motion.div>
    );
  }

  // ========== WIZARD STEPS ==========
  return (
    <div className="max-w-4xl mx-auto">
      {/* Wizard Header / Stepper */}
      <div className="flex items-center justify-between mb-8 px-2">
        {[
          { num: 1, label: 'Flight Selection' },
          { num: 2, label: 'Aircraft' },
          { num: 3, label: 'Dispatch' }
        ].map((s, i) => (
          <div key={s.num} className="flex items-center">
            <div className="flex items-center gap-3">
              <div className={cn(
                "flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold transition-all duration-300",
                step === s.num 
                  ? "bg-yellow-400 text-black shadow-[0_0_15px_rgba(250,204,21,0.4)]"
                  : step > s.num 
                    ? "bg-yellow-400/20 text-yellow-400 border border-yellow-400/30"
                    : "bg-slate-800 text-slate-500 border border-slate-700/50"
              )}>
                {step > s.num ? <Check size={14} /> : s.num}
              </div>
              <span className={cn(
                "text-sm font-semibold transition-colors hidden sm:block",
                step === s.num ? "text-white" : step > s.num ? "text-slate-300" : "text-slate-600"
              )}>
                {s.label}
              </span>
            </div>
            {i < 2 && (
              <div className="w-12 sm:w-24 h-px mx-4 bg-slate-800 relative">
                <div className={cn(
                  "absolute inset-y-0 left-0 bg-yellow-400/50 transition-all duration-500",
                  step > s.num ? "w-full" : "w-0"
                )} />
              </div>
            )}
          </div>
        ))}
      </div>

      {/* Main Content Area */}
      <div className="bg-slate-900/40 backdrop-blur-xl border border-slate-800/50 rounded-2xl p-6 min-h-[500px] shadow-2xl relative overflow-hidden">
        
        {/* Ambient glow */}
        <div className="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-32 bg-yellow-400/5 rounded-full blur-[100px] pointer-events-none" />

        <AnimatePresence mode="wait" custom={direction}>
          {/* STEP 1: SELECT FLIGHT */}
          {step === 1 && (
            <motion.div
              key="step1"
              custom={direction}
              variants={slideVariants}
              initial="enter"
              animate="center"
              exit="exit"
              transition={{ duration: 0.3, ease: "easeInOut" }}
              className="space-y-6"
            >
              <div className="flex items-center justify-between">
                <div>
                  <h2 className="text-xl font-bold text-white mb-1">Select a Flight</h2>
                  <p className="text-sm text-slate-400">Choose a bid or browse available schedules.</p>
                </div>
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" size={16} />
                  <input
                    type="text"
                    placeholder="Search flights..."
                    value={searchFlight}
                    onChange={(e) => setSearchFlight(e.target.value)}
                    className="bg-slate-950/50 border border-slate-800 rounded-xl pl-9 pr-4 py-2.5 text-sm text-slate-300 placeholder:text-slate-600 focus:outline-none focus:border-yellow-400/50 focus:ring-1 focus:ring-yellow-400/50 transition-all w-64"
                  />
                </div>
              </div>

              {loading ? (
                <div className="flex flex-col items-center justify-center py-20 text-slate-500">
                  <Loader2 className="animate-spin mb-4" size={32} />
                  <p>Loading flight data...</p>
                </div>
              ) : (
                <div className="space-y-6 h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                  
                  {/* Bids Section */}
                  {filteredBids.length > 0 && (
                    <div className="space-y-3">
                      <h3 className="text-xs font-bold text-yellow-400 uppercase tracking-widest flex items-center gap-2">
                        <Ticket size={14} /> My Booked Flights (Bids)
                      </h3>
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                        {filteredBids.map(b => (
                          <button
                            key={b.id}
                            onClick={() => selectBid(b)}
                            className="group relative flex flex-col text-left p-4 rounded-xl bg-slate-950/50 border border-slate-800/80 hover:border-yellow-400/50 transition-all duration-300 overflow-hidden"
                          >
                            <div className="absolute inset-0 bg-gradient-to-r from-yellow-400/0 via-yellow-400/0 to-yellow-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
                            <div className="flex justify-between items-start mb-4">
                              <span className="text-lg font-black text-white tracking-tight">{b.schedule?.flight_number}</span>
                              <span className="text-[10px] px-2 py-1 rounded-md bg-yellow-400/10 text-yellow-400 border border-yellow-400/20 font-bold uppercase">Bid #{b.id}</span>
                            </div>
                            <div className="flex items-center gap-3 text-sm font-medium text-slate-300 mb-4">
                              <div className="flex items-center gap-1.5"><PlaneTakeoff size={14} className="text-slate-500"/> {b.schedule?.departure}</div>
                              <div className="h-px w-8 bg-slate-700"></div>
                              <div className="flex items-center gap-1.5"><PlaneLanding size={14} className="text-slate-500"/> {b.schedule?.arrival}</div>
                            </div>
                            <div className="mt-auto pt-3 border-t border-slate-800/50 flex items-center gap-2">
                              <Fingerprint size={12} className="text-slate-500" />
                              <span className="text-xs text-slate-400 font-mono">{b.aircraft?.registration ?? 'Any Tail'}</span>
                              <span className="text-[10px] text-slate-600 bg-slate-900 px-1.5 py-0.5 rounded ml-auto">
                                {b.aircraft?.icao ?? b.schedule?.aircraft_type ?? 'UNK'}
                              </span>
                            </div>
                          </button>
                        ))}
                      </div>
                    </div>
                  )}

                  {/* Schedules Section */}
                  <div className="space-y-3">
                    <h3 className="text-xs font-bold text-slate-500 uppercase tracking-widest flex items-center gap-2">
                      <Calendar size={14} /> Available Schedules
                    </h3>
                    {filteredSchedules.length === 0 ? (
                      <p className="text-sm text-slate-600 py-4">No schedules found.</p>
                    ) : (
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                        {filteredSchedules.map(s => (
                          <button
                            key={s.id}
                            onClick={() => selectSchedule(s)}
                            className="group flex items-center justify-between text-left p-4 rounded-xl bg-slate-950/30 border border-slate-800/50 hover:bg-slate-800/40 hover:border-slate-700 transition-all duration-200"
                          >
                            <div>
                              <div className="text-sm font-bold text-slate-200 mb-1.5 group-hover:text-yellow-400 transition-colors">{s.flight_number}</div>
                              <div className="flex items-center gap-2 text-xs font-medium text-slate-400">
                                <span>{s.departure}</span>
                                <ArrowRight size={10} className="text-slate-600" />
                                <span>{s.arrival}</span>
                              </div>
                            </div>
                            {s.aircraft_type && (
                              <span className="text-[10px] font-mono text-slate-500 bg-slate-900 px-2 py-1 rounded-md border border-slate-800">
                                {s.aircraft_type}
                              </span>
                            )}
                          </button>
                        ))}
                      </div>
                    )}
                  </div>

                </div>
              )}
            </motion.div>
          )}

          {/* STEP 2: SELECT AIRCRAFT */}
          {step === 2 && (
            <motion.div
              key="step2"
              custom={direction}
              variants={slideVariants}
              initial="enter"
              animate="center"
              exit="exit"
              transition={{ duration: 0.3, ease: "easeInOut" }}
              className="space-y-6 flex flex-col h-full"
            >
              <div className="flex items-center justify-between">
                <div>
                  <h2 className="text-xl font-bold text-white mb-1">Select Aircraft</h2>
                  <p className="text-sm text-slate-400">Choose the tail number for flight <span className="text-yellow-400 font-bold">{form.flight_number}</span>.</p>
                </div>
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" size={16} />
                  <input
                    type="text"
                    placeholder="Search tail, ICAO..."
                    value={searchAircraft}
                    onChange={(e) => setSearchAircraft(e.target.value)}
                    className="bg-slate-950/50 border border-slate-800 rounded-xl pl-9 pr-4 py-2.5 text-sm text-slate-300 placeholder:text-slate-600 focus:outline-none focus:border-yellow-400/50 focus:ring-1 focus:ring-yellow-400/50 transition-all w-64"
                  />
                </div>
              </div>

              <div className="flex-1 overflow-y-auto pr-2 custom-scrollbar min-h-[320px]">
                {filteredAircraft.length === 0 ? (
                   <p className="text-sm text-slate-600 py-4 text-center">No aircraft available.</p>
                ) : (
                  <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    {filteredAircraft.map(a => {
                      const isAtDeparture = a.location === form.departure;
                      return (
                        <button
                          key={a.id}
                          onClick={() => selectAircraft(a)}
                          className="group text-left p-4 rounded-xl bg-slate-950/40 border border-slate-800/60 hover:border-yellow-400/40 hover:bg-slate-900/60 transition-all duration-200"
                        >
                          <div className="flex justify-between items-start mb-3">
                            <span className="text-base font-bold text-white group-hover:text-yellow-400 transition-colors font-mono tracking-wider">{a.registration}</span>
                            <span className="text-[10px] font-bold text-slate-400 bg-slate-800 px-1.5 py-0.5 rounded">{a.icao}</span>
                          </div>
                          <div className="text-xs text-slate-500 mb-3 truncate">{a.name}</div>
                          
                          <div className="pt-3 border-t border-slate-800/50 flex items-center gap-1.5 text-xs">
                            <MapPin size={12} className={cn(isAtDeparture ? "text-green-400" : "text-slate-500")} />
                            <span className={cn("font-medium", isAtDeparture ? "text-green-400" : "text-slate-400")}>
                              {a.location ?? 'Unknown'}
                            </span>
                          </div>
                        </button>
                      );
                    })}
                  </div>
                )}
              </div>

              <div className="pt-4 border-t border-slate-800/50 flex justify-between mt-auto">
                <button
                  onClick={() => goToStep(1)}
                  className="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                >
                  <ArrowLeft size={16} /> Back
                </button>
              </div>
            </motion.div>
          )}

          {/* STEP 3: DISPATCH / REVIEW */}
          {step === 3 && (
            <motion.div
              key="step3"
              custom={direction}
              variants={slideVariants}
              initial="enter"
              animate="center"
              exit="exit"
              transition={{ duration: 0.3, ease: "easeInOut" }}
              className="space-y-6 flex flex-col h-full"
            >
              <div>
                <h2 className="text-xl font-bold text-white mb-1">Preflight Dispatch</h2>
                <p className="text-sm text-slate-400">Review your flight details before connecting to the simulator.</p>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="bg-slate-950/50 border border-slate-800/80 rounded-xl p-5">
                  <div className="text-[10px] uppercase font-bold text-slate-500 tracking-widest mb-1">Flight Number</div>
                  <div className="text-2xl font-black text-white">{form.flight_number || '—'}</div>
                  
                  <div className="mt-6 flex items-center justify-between text-sm font-medium">
                    <div className="flex flex-col">
                      <span className="text-[10px] uppercase font-bold text-slate-500 tracking-widest mb-1">Departure</span>
                      <span className="text-lg text-slate-200">{form.departure || '—'}</span>
                    </div>
                    <ArrowRight className="text-slate-600 mt-4" size={20} />
                    <div className="flex flex-col text-right">
                      <span className="text-[10px] uppercase font-bold text-slate-500 tracking-widest mb-1">Arrival</span>
                      <span className="text-lg text-slate-200">{form.arrival || '—'}</span>
                    </div>
                  </div>
                </div>

                <div className="bg-slate-950/50 border border-slate-800/80 rounded-xl p-5 flex flex-col justify-between">
                  <div>
                    <div className="text-[10px] uppercase font-bold text-slate-500 tracking-widest mb-1">Aircraft Registration</div>
                    <div className="text-xl font-mono font-bold text-yellow-400">{form.aircraft_registration || '—'}</div>
                  </div>
                  
                  <div className="grid grid-cols-2 gap-4 mt-6">
                    <div>
                      <div className="text-[10px] uppercase font-bold text-slate-500 tracking-widest mb-1">Type (ICAO)</div>
                      <div className="text-sm font-semibold text-slate-300">{form.aircraft_icao || '—'}</div>
                    </div>
                    <div>
                      <div className="text-[10px] uppercase font-bold text-slate-500 tracking-widest mb-1">Network</div>
                      <div className="text-sm font-semibold text-green-400 flex items-center gap-1.5">
                        <span className="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse" /> Offline
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {error && (
                <motion.div initial={{ opacity: 0, y: 5 }} animate={{ opacity: 1, y: 0 }} className="flex items-center gap-2 bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl text-sm font-medium">
                  <AlertCircle size={16} />
                  {error}
                </motion.div>
              )}

              <div className="pt-6 border-t border-slate-800/50 flex justify-between mt-auto items-center">
                <button
                  onClick={() => goToStep(2)}
                  className="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                >
                  <ArrowLeft size={16} /> Change Aircraft
                </button>
                
                <button
                  onClick={handleSubmit}
                  disabled={submitting}
                  className="flex items-center gap-3 bg-yellow-400 hover:bg-yellow-500 text-black font-bold rounded-xl px-8 py-3.5 transition-all shadow-[0_0_20px_rgba(250,204,21,0.2)] hover:shadow-[0_0_25px_rgba(250,204,21,0.4)] hover:-translate-y-0.5 disabled:opacity-50 disabled:hover:translate-y-0 disabled:hover:shadow-none"
                >
                  {submitting ? <Loader2 size={18} className="animate-spin" /> : <Send size={18} />}
                  {submitting ? 'Setting up flight...' : 'START FLIGHT'}
                  {!submitting && <ChevronRight size={18} />}
                </button>
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </div>
    </div>
  );
};
