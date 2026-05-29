import { invoke } from '@tauri-apps/api/core';

export interface TelemetryData {
  flight_number: string;
  aircraft_registration: string;
  aircraft_icao: string;
  aircraft_type: string;
  departure: string;
  arrival: string;
  departure_lat?: number;
  departure_lng?: number;
  arrival_lat?: number;
  arrival_lng?: number;
  current_lat: number;
  current_lng: number;
  heading: number;
  altitude: number;
  ground_speed: number;
  phase: string;
}

export const sendTelemetry = async (apiKey: string, telemetry: TelemetryData) => {
  try {
    await invoke('update_telemetry', { apiKey, telemetry });
    console.log('Telemetry updated successfully');
  } catch (error) {
    console.error('Failed to update telemetry:', error);
  }
};
