use crate::api::FlightTelemetry;
use sim_core::SimSnapshot;

#[derive(Debug, Clone, PartialEq)]
pub enum FlightPhase {
    Preflight,
    Pushback,
    TaxiOut,
    Takeoff,
    Climb,
    Cruise,
    Descent,
    Approach,
    Landing,
    TaxiIn,
    Shutdown,
}

pub struct FlightManager {
    pub current_phase: FlightPhase,
    pub max_altitude_reached: f64,
}

impl FlightManager {
    pub fn new() -> Self {
        Self {
            current_phase: FlightPhase::Preflight,
            max_altitude_reached: 0.0,
        }
    }

    pub fn update_phase(&mut self, snap: &SimSnapshot, telemetry: &FlightTelemetry) -> FlightPhase {
        // Track max altitude for descent detection
        if telemetry.altitude as f64 > self.max_altitude_reached {
            self.max_altitude_reached = telemetry.altitude as f64;
        }

        let vs = snap.vertical_speed_fpm;
        let gs = snap.groundspeed_kt;
        let agl = snap.altitude_agl_ft;

        match self.current_phase {
            FlightPhase::Preflight => {
                if snap.engines_running > 0 && snap.parking_brake {
                    self.current_phase = FlightPhase::Pushback;
                } else if !snap.parking_brake && gs > 1.0 {
                    self.current_phase = FlightPhase::TaxiOut;
                }
            }
            FlightPhase::Pushback => {
                if !snap.parking_brake && gs > 1.0 {
                    self.current_phase = FlightPhase::TaxiOut;
                }
            }
            FlightPhase::TaxiOut => {
                if gs > 40.0 {
                    self.current_phase = FlightPhase::Takeoff;
                }
            }
            FlightPhase::Takeoff => {
                if !snap.on_ground && agl > 50.0 {
                    self.current_phase = FlightPhase::Climb;
                }
            }
            FlightPhase::Climb => {
                // If altitude is stable for a bit, or reached a high level
                if vs.abs() < 500.0 && telemetry.altitude > 5000 {
                    self.current_phase = FlightPhase::Cruise;
                } else if vs < -500.0 && (self.max_altitude_reached - telemetry.altitude as f64) > 500.0 {
                    self.current_phase = FlightPhase::Descent; // Allowed to skip Cruise
                } else if snap.on_ground {
                    self.current_phase = FlightPhase::Landing; // Crash or early landing
                }
            }
            FlightPhase::Cruise => {
                if vs < -500.0 && (self.max_altitude_reached - telemetry.altitude as f64) > 1000.0 {
                    self.current_phase = FlightPhase::Descent;
                } else if snap.on_ground {
                    self.current_phase = FlightPhase::Landing; // Fallback
                }
            }
            FlightPhase::Descent => {
                if agl < 5000.0 && snap.flaps_position > 0.05 {
                    self.current_phase = FlightPhase::Approach;
                } else if snap.on_ground {
                    self.current_phase = FlightPhase::Landing; // fallback
                }
            }
            FlightPhase::Approach => {
                if snap.on_ground {
                    self.current_phase = FlightPhase::Landing;
                }
            }
            FlightPhase::Landing => {
                if gs < 30.0 {
                    self.current_phase = FlightPhase::TaxiIn;
                }
            }
            FlightPhase::TaxiIn => {
                if snap.parking_brake && gs < 1.0 {
                    self.current_phase = FlightPhase::Shutdown;
                }
            }
            FlightPhase::Shutdown => {
                // Final state, no transitions
            }
        }
        
        self.current_phase.clone()
    }
}
