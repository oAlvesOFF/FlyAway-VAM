import { load } from '@tauri-apps/plugin-store';

let flightsStore: any = null;

async function getStore() {
  if (!flightsStore) {
    flightsStore = await load('flights.json');
  }
  return flightsStore;
}

export interface FlareDataPoint {
  time_offset: number;
  altitude_agl: number;
  vertical_speed: number;
  pitch: number;
  ground_speed: number;
}

export interface PirepRecord {
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
  created_at?: string;
  flare_profile?: FlareDataPoint[];
}

export async function saveLocalFlight(flight: PirepRecord) {
  const store = await getStore();
  const current: PirepRecord[] = await store.get('local_flights') || [];
  if (!flight.created_at) {
    flight.created_at = new Date().toISOString();
  }
  // Remove if it exists (by id) just in case
  const filtered = current.filter(f => f.id !== flight.id);
  filtered.unshift(flight);
  await store.set('local_flights', filtered);
  await store.save();
}

export async function getLocalFlights(): Promise<PirepRecord[]> {
  const store = await getStore();
  const flights: PirepRecord[] = await store.get('local_flights') || [];
  return flights;
}
