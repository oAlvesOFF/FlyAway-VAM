import { useEffect, useState } from 'react';
import { invoke } from '@tauri-apps/api/core';
import { BookOpen, Plane } from 'lucide-react';

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
  submitted_at: string | null;
}

export const Logbook = () => {
  const [flights, setFlights] = useState<PirepRecord[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    invoke<PirepRecord[]>('fetch_pireps')
      .then(data => {
        setFlights(Array.isArray(data) ? data : []);
        setLoading(false);
      })
      .catch(err => {
        setError(typeof err === 'string' ? err : 'Failed to load');
        setLoading(false);
      });
  }, []);

  if (loading) {
    return (
      <div className="flex items-center justify-center py-20 text-slate-600">
        <div className="animate-pulse text-sm">Loading logbook...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex flex-col items-center justify-center py-20 text-center">
        <BookOpen size={40} className="text-slate-700 mb-4" />
        <p className="text-sm text-slate-500 mb-1">Could not load logbook</p>
        <p className="text-xs text-slate-700">{error}</p>
      </div>
    );
  }

  if (flights.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center py-20 text-center">
        <Plane size={48} className="text-slate-700 mb-4" />
        <p className="text-lg text-slate-500 mb-1">No flights yet</p>
        <p className="text-sm text-slate-700">Complete a flight to see it here.</p>
      </div>
    );
  }

  return (
    <div className="bg-[#111111] border border-slate-800/50 rounded-xl overflow-hidden">
      <div className="p-5 border-b border-slate-800/50">
        <h3 className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pilot Logbook</h3>
      </div>
      <div className="overflow-x-auto">
        <table className="w-full text-left">
          <thead>
            <tr className="text-xs text-slate-600 border-b border-slate-800/50">
              <th className="p-4 font-medium">FLIGHT</th>
              <th className="p-4 font-medium">ROUTE</th>
              <th className="p-4 font-medium">AIRCRAFT</th>
              <th className="p-4 font-medium">DURATION</th>
              <th className="p-4 font-medium">LANDING</th>
              <th className="p-4 font-medium">SCORE</th>
              <th className="p-4 font-medium">STATUS</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-800/30">
            {flights.map(f => (
              <tr key={f.id} className="text-sm text-slate-300 hover:bg-slate-800/20 transition-colors">
                <td className="p-4 font-medium text-slate-200">{f.flight_number}</td>
                <td className="p-4">
                  <span className="text-yellow-400">{f.departure}</span>
                  <span className="text-slate-700 mx-2">&rarr;</span>
                  <span className="text-yellow-400">{f.arrival}</span>
                </td>
                <td className="p-4 text-slate-400">{f.aircraft_registration}</td>
                <td className="p-4 text-slate-500">{f.flight_time.toFixed(1)}h</td>
                <td className="p-4">
                  {f.landing_rate != null ? (
                    <span className="text-yellow-400 font-medium">{f.landing_rate} fpm</span>
                  ) : (
                    <span className="text-slate-700">—</span>
                  )}
                </td>
                <td className="p-4">
                  {f.score != null ? (
                    <span className={`font-medium ${f.score >= 80 ? 'text-green-400' : f.score >= 50 ? 'text-yellow-400' : 'text-red-400'}`}>{f.score}/100</span>
                  ) : (
                    <span className="text-slate-700">—</span>
                  )}
                </td>
                <td className="p-4">
                  <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${
                    f.status === 'approved' ? 'bg-green-500/10 text-green-400' :
                    f.status === 'pending' ? 'bg-yellow-500/10 text-yellow-400' :
                    'bg-slate-500/10 text-slate-500'
                  }`}>
                    {f.status || 'unknown'}
                  </span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};
