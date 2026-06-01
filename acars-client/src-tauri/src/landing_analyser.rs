use sim_core::SimSnapshot;
use serde::{Deserialize, Serialize};

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct FlareDataPoint {
    pub time_offset: f32,
    pub altitude_agl: f32,
    pub vertical_speed: f32,
    pub pitch: f32,
    pub ground_speed: f32,
}

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct LandingMetrics {
    pub vertical_speed_at_touchdown: f32,
    pub g_force: f32,
    pub bounce_detected: bool,
    pub flare_profile: Vec<FlareDataPoint>,
}

pub struct LandingAnalyser {
    pub is_analysing: bool,
    pub metrics: Option<LandingMetrics>,
    pub last_vertical_speed: f32,
    pub history: std::collections::VecDeque<SimSnapshot>,
}

impl LandingAnalyser {
    pub fn new() -> Self {
        Self { 
            is_analysing: false, 
            metrics: None, 
            last_vertical_speed: 0.0,
            history: std::collections::VecDeque::new()
        }
    }

    pub fn process_telemetry(&mut self, snap: &SimSnapshot) {
        self.history.push_back(snap.clone());
        if self.history.len() > 30 {
            self.history.pop_front();
        }

        if !snap.on_ground {
            self.is_analysing = true;
            self.last_vertical_speed = snap.vertical_speed_fpm;
        } else if self.is_analysing && self.metrics.is_none() {
            let final_vs = snap.touchdown_vs_fpm.unwrap_or(self.last_vertical_speed);
            let touchdown_time = snap.timestamp.timestamp_millis() as f64;
            
            let mut flare_profile = Vec::new();
            for past_snap in &self.history {
                let time_offset = ((past_snap.timestamp.timestamp_millis() as f64) - touchdown_time) / 1000.0;
                // Capture the last 15 seconds below 200ft AGL
                if time_offset >= -15.0 && past_snap.altitude_agl_ft < 200.0 {
                    flare_profile.push(FlareDataPoint {
                        time_offset: time_offset as f32,
                        altitude_agl: past_snap.altitude_agl_ft as f32,
                        vertical_speed: past_snap.vertical_speed_fpm,
                        pitch: past_snap.pitch_deg,
                        ground_speed: past_snap.groundspeed_kt,
                    });
                }
            }
            
            // Also push the exact touchdown point
            flare_profile.push(FlareDataPoint {
                time_offset: 0.0,
                altitude_agl: 0.0,
                vertical_speed: final_vs,
                pitch: snap.pitch_deg,
                ground_speed: snap.groundspeed_kt,
            });
            
            self.metrics = Some(LandingMetrics {
                vertical_speed_at_touchdown: final_vs,
                g_force: snap.g_force,
                bounce_detected: false,
                flare_profile,
            });
            self.is_analysing = false;
        }
    }
}
