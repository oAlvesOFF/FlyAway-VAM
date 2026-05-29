import { useEffect, useState } from 'react';
import { invoke } from '@tauri-apps/api/core';
import { Gauge, Wind } from 'lucide-react';

interface PirepRecord {
  id: number;
  flight_number: string;
  departure: string;
  arrival: string;
  aircraft_registration: string;
  aircraft_icao: string;
  flight_time: number;
  landing_rate: number | null;
  score: number | null;
  status: string;
}

const MetricBar = ({ label, value, score, max }: { label: string; value: string; score: number; max: number }) => {
  const pct = Math.min((score / max) * 100, 100);
  const color = pct >= 80 ? 'bg-green-500' : pct >= 50 ? 'bg-yellow-400' : 'bg-red-500';
  return (
    <div className="bg-[#111111] border border-slate-800/50 rounded-xl p-5">
      <div className="flex justify-between items-center mb-3">
        <span className="text-xs font-medium text-slate-500 uppercase tracking-wider">{label}</span>
        <span className={`text-sm font-bold ${color.replace('bg-', 'text-')}`}>{score} pts</span>
      </div>
      <div className="text-xl font-bold text-slate-200 mb-3">{value}</div>
      <div className="w-full bg-slate-800/50 h-1.5 rounded-full overflow-hidden">
        <div className={`${color} h-full rounded-full transition-all duration-500`} style={{ width: `${pct}%` }} />
      </div>
    </div>
  );
};

const gradeLabel = (score: number): { grade: string; label: string } => {
  if (score >= 90) return { grade: 'A', label: 'Perfect landing' };
  if (score >= 80) return { grade: 'B', label: 'Good landing' };
  if (score >= 60) return { grade: 'C', label: 'Average landing' };
  if (score >= 40) return { grade: 'D', label: 'Hard landing' };
  return { grade: 'F', label: 'Unsafe landing' };
};

export const LandingAnalyserDetailed = () => {
  const [latest, setLatest] = useState<PirepRecord | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    invoke<PirepRecord[]>('fetch_pireps')
      .then(data => {
        const withRate = (Array.isArray(data) ? data : []).filter(p => p.landing_rate != null);
        setLatest(withRate.length > 0 ? withRate[0] : null);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, []);

  if (loading) {
    return <div className="flex items-center justify-center py-20 text-slate-600"><div className="animate-pulse text-sm">Loading landing data...</div></div>;
  }

  if (!latest) {
    return (
      <div className="flex flex-col items-center justify-center py-20 text-center">
        <Gauge size={48} className="text-slate-700 mb-4" />
        <p className="text-lg text-slate-500 mb-1">No landing data yet</p>
        <p className="text-sm text-slate-700">Complete a flight with landing rate to analyse it here.</p>
      </div>
    );
  }

  const score = latest.score ?? 0;
  const lr = latest.landing_rate ?? 0;
  const absLr = Math.abs(lr);
  const { grade, label } = gradeLabel(score);

  const lrScore = absLr <= 50 ? 100 : absLr <= 200 ? 80 : absLr <= 500 ? 60 : 30;
  const gScore = absLr <= 100 ? 100 : absLr <= 300 ? 80 : absLr <= 600 ? 50 : 20;
  const bounceScore = lr >= -50 && lr < 0 ? 100 : lr < -200 ? 60 : 80;

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-2 md:grid-cols-5 gap-4 bg-[#111111] border border-slate-800/50 rounded-xl p-5">
        {[
          ['FLIGHT', latest.flight_number],
          ['ROUTE', `${latest.departure} → ${latest.arrival}`],
          ['AIRCRAFT', latest.aircraft_registration],
          ['TOUCHDOWN', <span className="text-yellow-400">{lr} fpm</span>],
          ['SCORE', <span className="text-yellow-400 font-bold">{score} / 100</span>],
        ].map(([label, value]) => (
          <div key={label as string}>
            <div className="text-[10px] text-slate-600 uppercase tracking-wider mb-1">{label as string}</div>
            <div className="text-sm font-semibold text-slate-200">{value as React.ReactNode}</div>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-[#111111] border border-slate-800/50 rounded-xl p-6 flex flex-col items-center justify-center">
          <div className={`w-28 h-28 rounded-full border-4 flex items-center justify-center mb-3 ${
            score >= 80 ? 'border-green-400/60' : score >= 50 ? 'border-yellow-400/60' : 'border-red-400/60'
          }`}>
            <Gauge size={40} className={
              score >= 80 ? 'text-green-400' : score >= 50 ? 'text-yellow-400' : 'text-red-400'
            } />
          </div>
          <div className={`text-3xl font-bold mb-1 ${
            score >= 80 ? 'text-green-400' : score >= 50 ? 'text-yellow-400' : 'text-red-400'
          }`}>{grade}</div>
          <div className="text-sm text-slate-500">{label}</div>
        </div>

        <div className="md:col-span-2 bg-[#111111] border border-slate-800/50 rounded-xl p-5">
          <div className="flex items-center gap-2 mb-4">
            <Wind size={16} className="text-slate-500" />
            <h3 className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Landing Details</h3>
          </div>
          <div className="grid grid-cols-3 gap-4">
            <div className="bg-[#161616] rounded-lg p-3 text-center">
              <div className="text-lg font-bold text-slate-200">{lr} fpm</div>
              <div className="text-[10px] text-slate-600">VERTICAL SPEED</div>
            </div>
            <div className="bg-[#161616] rounded-lg p-3 text-center">
              <div className="text-lg font-bold text-slate-200">{(absLr / 200 + 1.0).toFixed(2)} G</div>
              <div className="text-[10px] text-slate-600">G-FORCE (est.)</div>
            </div>
            <div className="bg-[#161616] rounded-lg p-3 text-center">
              <div className={`text-lg font-bold ${absLr <= 100 ? 'text-green-400' : absLr <= 300 ? 'text-yellow-400' : 'text-red-400'}`}>
                {absLr <= 100 ? 'Smooth' : absLr <= 300 ? 'Firm' : 'Hard'}
              </div>
              <div className="text-[10px] text-slate-600">QUALITY</div>
            </div>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <MetricBar label="Landing Rate" value={`${lr} fpm`} score={lrScore} max={100} />
        <MetricBar label="G-Force (est.)" value={`${(absLr / 200 + 1.0).toFixed(2)} G`} score={gScore} max={100} />
        <MetricBar label="Bounce" value={absLr <= 100 ? 'None' : absLr <= 300 ? 'Light' : 'Heavy'} score={bounceScore} max={100} />
      </div>
    </div>
  );
};
