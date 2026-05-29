import { useEffect, useState } from 'react';
import { invoke } from '@tauri-apps/api/core';
import { Plane, Clock, Award, Activity } from 'lucide-react';

interface UserInfo {
  name: string;
  pilot_id: string;
  total_hours: number;
  total_flights: number;
  last_location: string | null;
  rank: { name: string } | null;
}

interface PirepRecord {
  id: number;
  flight_number: string;
  departure: string;
  arrival: string;
  aircraft_registration: string;
  flight_time: number;
  landing_rate: number | null;
  score: number | null;
  status: string;
  submitted_at: string | null;
  user: UserInfo | null;
}

const StatCard = ({ icon, label, value, accent }: {
  icon: React.ReactNode; label: string; value: string; accent?: string;
}) => (
  <div className="bg-[#111111] border border-slate-800/50 rounded-xl p-5 hover:border-slate-700/50 transition-colors">
    <div className="flex items-start justify-between mb-4">
      <div className="text-slate-500">{icon}</div>
      {accent && <span className="text-xs text-yellow-400 font-medium">{accent}</span>}
    </div>
    <div className="text-2xl font-bold text-slate-100 mb-1">{value}</div>
    <div className="text-xs text-slate-600">{label}</div>
  </div>
);

export const Dashboard = () => {
  const [user, setUser] = useState<UserInfo | null>(null);
  const [pireps, setPireps] = useState<PirepRecord[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    invoke<PirepRecord[]>('fetch_pireps')
      .then(data => {
        if (Array.isArray(data) && data.length > 0) {
          const withUser = data.find(p => p.user != null);
          if (withUser?.user) setUser(withUser.user);
          setPireps(data);
        }
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, []);

  const hours = user?.total_hours ?? 0;
  const flights = user?.total_flights ?? 0;
  const lastScore = pireps.length > 0
    ? Math.max(...pireps.filter(p => p.score != null).map(p => p.score!))
    : null;

  return (
    <div className="space-y-6">
      <div className="bg-gradient-to-r from-yellow-500/20 to-yellow-600/5 border border-yellow-500/20 rounded-xl p-6 flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-yellow-400">
            {loading ? 'Loading...' : `Welcome back, ${user?.name?.split(' ')[0] ?? 'Pilot'}.`}
          </h1>
          <p className="text-sm text-slate-500 mt-1">
            {user?.last_location ? `Last seen at ${user.last_location}` : 'Ready for your next flight?'}
          </p>
        </div>
        <div className="hidden sm:block">
          <Plane size={40} className="text-yellow-500/30" />
        </div>
      </div>

      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <StatCard icon={<Clock size={20} />} label="Total Hours" value={hours.toFixed(1)} />
        <StatCard icon={<Award size={20} />} label="Flights" value={String(flights)} />
        <StatCard icon={<Activity size={20} />} label="Landing Score" value={lastScore != null ? `${lastScore}/100` : '—'} accent={lastScore != null ? 'Best' : undefined} />
        <StatCard icon={<Plane size={20} />} label="Current Flight" value={pireps.filter(p => p.status === 'pending').length > 0 ? 'Pending' : '—'} />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 bg-[#111111] border border-slate-800/50 rounded-xl p-5">
          <h3 className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">Recent Activity</h3>
          <div className="divide-y divide-slate-800/30">
            {pireps.length === 0 && (
              <div className="text-sm text-slate-600 py-3">No flights yet. Complete a flight to see it here.</div>
            )}
            {pireps.slice(0, 10).map(p => (
              <div key={p.id} className="flex gap-3 py-3 border-b border-slate-800/30 last:border-0">
                <div className={`w-2 h-2 rounded-full mt-1.5 shrink-0 ${
                  p.status === 'approved' ? 'bg-green-400/60' :
                  p.status === 'pending' ? 'bg-yellow-400/60' : 'bg-slate-500/60'
                }`} />
                <div className="flex-1 min-w-0">
                  <div className="text-sm text-slate-200">{p.flight_number} &middot; {p.departure}&rarr;{p.arrival}</div>
                  <div className="text-xs text-slate-600 truncate">{p.status} &middot; {p.flight_time.toFixed(1)}h{p.landing_rate ? ` &middot; ${p.landing_rate}fpm` : ''}</div>
                </div>
                <div className="text-xs text-slate-700 shrink-0">{p.submitted_at ? new Date(p.submitted_at).toLocaleDateString() : '—'}</div>
              </div>
            ))}
          </div>
        </div>

        <div className="bg-[#111111] border border-slate-800/50 rounded-xl p-5">
          <h3 className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">Profile</h3>
          {loading ? (
            <div className="text-sm text-slate-600">Loading...</div>
          ) : user ? (
            <div className="space-y-4">
              <div>
                <div className="text-sm font-medium text-slate-200">{user.name}</div>
                <div className="text-xs text-slate-600">{user.pilot_id || '—'}</div>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div className="bg-[#161616] rounded-lg p-3 text-center">
                  <div className="text-lg font-bold text-slate-200">{hours.toFixed(1)}</div>
                  <div className="text-[10px] text-slate-600">HOURS</div>
                </div>
                <div className="bg-[#161616] rounded-lg p-3 text-center">
                  <div className="text-lg font-bold text-slate-200">{flights}</div>
                  <div className="text-[10px] text-slate-600">FLIGHTS</div>
                </div>
              </div>
              <div className="bg-[#161616] rounded-lg p-3 text-center">
                <div className="text-lg font-bold text-yellow-400">{user.rank?.name ?? '—'}</div>
                <div className="text-[10px] text-slate-600">RANK</div>
              </div>
            </div>
          ) : (
            <div className="text-sm text-slate-600">Set API key to view profile</div>
          )}
        </div>
      </div>
    </div>
  );
};
