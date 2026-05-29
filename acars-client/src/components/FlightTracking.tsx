import { useEffect, useState } from 'react';
import { MapContainer, TileLayer, Marker, useMap } from 'react-leaflet';
import { listen } from '@tauri-apps/api/event';
import { invoke } from '@tauri-apps/api/core';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const aircraftIcon = L.divIcon({
  className: '',
  html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#facc15" width="28" height="28"><path d="M21 16v-2l-8-5V3.5A1.5 1.5 0 0 0 11.5 2 1.5 1.5 0 0 0 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/></svg>`,
  iconSize: [28, 28],
  iconAnchor: [14, 14],
});

const otherAircraftIcon = L.divIcon({
  className: '',
  html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#60a5fa" width="20" height="20"><path d="M21 16v-2l-8-5V3.5A1.5 1.5 0 0 0 11.5 2 1.5 1.5 0 0 0 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/></svg>`,
  iconSize: [20, 20],
  iconAnchor: [10, 10],
});

interface ActiveFlight {
  id: number;
  flight_number: string;
  departure: string;
  arrival: string;
  current_lat: number | null;
  current_lng: number | null;
  heading: number | null;
  altitude: number | null;
  ground_speed: number | null;
  phase: string;
}

const MapUpdater = ({ lat, lng }: { lat: number; lng: number }) => {
  const map = useMap();
  useEffect(() => {
    map.setView([lat, lng], map.getZoom(), { animate: true });
  }, [lat, lng, map]);
  return null;
};

export const FlightTracking = () => {
  const [pos, setPos] = useState<[number, number]>([38.7756, -9.1354]);
  const [activeFlights, setActiveFlights] = useState<ActiveFlight[]>([]);
  const [flightInfo, setFlightInfo] = useState<{ heading: number; altitude: number; speed: number; phase: string } | null>(null);

  useEffect(() => {
    const unlisten = listen<{
      current_lat: number; current_lng: number;
      heading: number; altitude: number; ground_speed: number; phase: string;
    }>('telemetry-updated', (event) => {
      const { current_lat: lat, current_lng: lng, heading, altitude, ground_speed, phase } = event.payload;
      if (lat && lng) setPos([lat, lng]);
      setFlightInfo({ heading, altitude, speed: ground_speed, phase });
    });
    return () => { unlisten.then(fn => fn()); };
  }, []);

  useEffect(() => {
    const poll = setInterval(async () => {
      try {
        const flights = await invoke<ActiveFlight[]>('fetch_active_flights');
        if (Array.isArray(flights)) setActiveFlights(flights);
      } catch { /* ignore */ }
    }, 5000);
    return () => clearInterval(poll);
  }, []);

  const altLabel = flightInfo != null
    ? `${flightInfo.altitude.toLocaleString()}ft | ${flightInfo.speed}kt | ${flightInfo.phase}`
    : 'No active telemetry';

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-4 text-xs text-slate-600 bg-[#111111] border border-slate-800/50 rounded-xl px-5 py-3">
        <span className="flex items-center gap-1.5">
          <span className="w-2 h-2 rounded-full bg-yellow-400" />
          Your aircraft
        </span>
        <span className="text-slate-700">|</span>
        <span>{altLabel}</span>
        <span className="text-slate-700">|</span>
        <span>{activeFlights.length} active flight{activeFlights.length !== 1 ? 's' : ''}</span>
      </div>

      <div className="h-[500px] w-full rounded-xl overflow-hidden border border-slate-800/50">
        <MapContainer center={pos} zoom={5} className="h-full w-full" zoomControl={true}>
          <TileLayer
            attribution='&copy; <a href="https://carto.com/">CARTO</a>'
            url="https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png"
          />
          <Marker position={pos} icon={aircraftIcon} />
          {activeFlights.map(f => (
            f.current_lat !== null && f.current_lng !== null ? (
              <Marker key={f.id} position={[f.current_lat, f.current_lng]} icon={otherAircraftIcon} />
            ) : null
          ))}
          <MapUpdater lat={pos[0]} lng={pos[1]} />
        </MapContainer>
      </div>
    </div>
  );
};
