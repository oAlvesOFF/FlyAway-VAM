use crate::api::FlightTelemetry;
use sim_core::SimSnapshot;

#[derive(Debug, Clone, PartialEq, serde::Serialize, serde::Deserialize)]
pub enum FlightPhase {
    Preflight,
    Pushback,
    TaxiOut,
    Takeoff,
    Climb,
    Cruise,
    Descent,
    Approach,
    GoAround,
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

        self.current_phase = match self.current_phase {
            FlightPhase::Preflight => {
                if snap.engines_running > 0 && snap.parking_brake {
                    FlightPhase::Pushback
                } else if !snap.parking_brake && gs > 1.0 {
                    FlightPhase::TaxiOut
                } else {
                    FlightPhase::Preflight
                }
            }
            FlightPhase::Pushback => {
                if !snap.parking_brake && gs > 1.0 {
                    FlightPhase::TaxiOut
                } else {
                    FlightPhase::Pushback
                }
            }
            FlightPhase::TaxiOut => {
                if gs > 40.0 && snap.engines_running > 0 {
                    FlightPhase::Takeoff
                } else {
                    FlightPhase::TaxiOut
                }
            }
            FlightPhase::Takeoff => {
                if !snap.on_ground && agl > 50.0 && vs > 200.0 {
                    FlightPhase::Climb
                } else if snap.on_ground && gs < 30.0 {
                    FlightPhase::TaxiIn // Rejected takeoff fallback
                } else {
                    FlightPhase::Takeoff
                }
            }
            FlightPhase::Climb | FlightPhase::GoAround => {
                if vs.abs() < 500.0 && telemetry.altitude > 5000 {
                    FlightPhase::Cruise
                } else if vs < -500.0 && (self.max_altitude_reached - telemetry.altitude as f64) > 500.0 {
                    FlightPhase::Descent
                } else if snap.on_ground {
                    FlightPhase::Landing // Unexpected landing / crash
                } else {
                    self.current_phase.clone()
                }
            }
            FlightPhase::Cruise => {
                if vs < -500.0 && (self.max_altitude_reached - telemetry.altitude as f64) > 1000.0 {
                    FlightPhase::Descent
                } else if vs > 500.0 {
                    FlightPhase::Climb // Step climb
                } else if snap.on_ground {
                    FlightPhase::Landing
                } else {
                    FlightPhase::Cruise
                }
            }
            FlightPhase::Descent => {
                if agl < 5000.0 && snap.flaps_position > 0.05 {
                    FlightPhase::Approach
                } else if vs > 500.0 && agl > 1000.0 {
                    FlightPhase::Climb // Aborted descent
                } else if snap.on_ground {
                    FlightPhase::Landing
                } else {
                    FlightPhase::Descent
                }
            }
            FlightPhase::Approach => {
                if snap.on_ground {
                    FlightPhase::Landing
                } else if vs > 500.0 && gs > 100.0 {
                    FlightPhase::GoAround // Go-Around detected!
                } else {
                    FlightPhase::Approach
                }
            }
            FlightPhase::Landing => {
                if gs < 30.0 {
                    FlightPhase::TaxiIn
                } else if !snap.on_ground && vs > 500.0 {
                    FlightPhase::GoAround // Touch and go detected!
                } else {
                    FlightPhase::Landing
                }
            }
            FlightPhase::TaxiIn => {
                if snap.parking_brake && gs < 1.0 {
                    FlightPhase::Shutdown
                } else if gs > 40.0 {
                    FlightPhase::Takeoff // In case they takeoff again from taxiway
                } else {
                    FlightPhase::TaxiIn
                }
            }
            FlightPhase::Shutdown => {
                // Final state, no transitions, unless they restart engines and release brake
                if snap.engines_running > 0 && !snap.parking_brake && gs > 1.0 {
                    FlightPhase::TaxiOut
                } else {
                    FlightPhase::Shutdown
                }
            }
        };
        
        self.current_phase.clone()
    }
}
