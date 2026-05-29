import { useEffect, useState } from 'react';
import { invoke } from '@tauri-apps/api/core';
import { Ticket, Plane, Loader2 } from 'lucide-react';

interface BidSchedule {
  id: number;
  flight_number: string;
  departure: string;
  arrival: string;
  aircraft_type: string | null;
  flight_time: number | null;
  route: string | null;
}

interface BidAircraft {
  id: number;
  registration: string;
  icao: string;
  name: string;
}

interface Bid {
  id: number;
  user_id: number;
  schedule_id: number | null;
  aircraft_id: number | null;
  schedule: BidSchedule | null;
  aircraft: BidAircraft | null;
}

export const Bids = () => {
  const [bids, setBids] = useState<Bid[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    invoke<Bid[]>('fetch_my_reservations')
      .then(data => {
        setBids(Array.isArray(data) ? data : []);
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
        <Loader2 size={20} className="animate-spin mr-2" />
        <span className="text-sm">Loading bids...</span>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex flex-col items-center justify-center py-20 text-center">
        <Ticket size={48} className="text-slate-700 mb-4" />
        <p className="text-lg text-slate-500 mb-1">Could not load bids</p>
        <p className="text-sm text-slate-700">{error}</p>
      </div>
    );
  }

  if (bids.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center py-20 text-center">
        <Ticket size={48} className="text-slate-700 mb-4" />
        <p className="text-lg text-slate-500 mb-1">No active bids</p>
        <p className="text-sm text-slate-700">Book a flight from the Preflight tab.</p>
      </div>
    );
  }

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
      {bids.map(bid => (
        <div key={bid.id} className="bg-[#111111] border border-slate-800/50 rounded-xl p-5 hover:border-yellow-400/20 transition-colors">
          <div className="flex items-center gap-3 mb-4">
            <Plane size={20} className="text-yellow-400" />
            <div>
              <div className="text-sm font-semibold text-slate-200">
                {bid.schedule?.flight_number ?? 'Flight #' + bid.schedule_id}
              </div>
              <div className="text-xs text-slate-600">
                {bid.schedule?.departure ?? '—'} &rarr; {bid.schedule?.arrival ?? '—'}
              </div>
            </div>
          </div>
          <div className="grid grid-cols-2 gap-3 text-xs">
            <div className="bg-[#161616] rounded-lg p-2">
              <span className="text-slate-600">Aircraft</span>
              <div className="text-slate-300">{bid.aircraft?.registration ?? '—'}</div>
            </div>
            <div className="bg-[#161616] rounded-lg p-2">
              <span className="text-slate-600">Type</span>
              <div className="text-slate-300">{bid.aircraft?.icao ?? bid.schedule?.aircraft_type ?? '—'}</div>
            </div>
            <div className="bg-[#161616] rounded-lg p-2">
              <span className="text-slate-600">Flight Time</span>
              <div className="text-slate-300">{bid.schedule?.flight_time != null ? `${bid.schedule.flight_time}h` : '—'}</div>
            </div>
            <div className="bg-[#161616] rounded-lg p-2">
              <span className="text-slate-600">Route</span>
              <div className="text-slate-300 truncate">{bid.schedule?.route ?? '—'}</div>
            </div>
          </div>
        </div>
      ))}
    </div>
  );
};
