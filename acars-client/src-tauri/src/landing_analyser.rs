use sim_core::SimSnapshot;

#[derive(Debug, Clone)]
pub struct LandingMetrics {
    pub vertical_speed_at_touchdown: f32,
    pub g_force: f32,
    pub bounce_detected: bool,
}

pub struct LandingAnalyser {
    pub is_analysing: bool,
    pub metrics: Option<LandingMetrics>,
    pub last_vertical_speed: f32,
}

impl LandingAnalyser {
    pub fn new() -> Self {
        Self { is_analysing: false, metrics: None, last_vertical_speed: 0.0 }
    }

    pub fn process_telemetry(&mut self, snap: &SimSnapshot) {
        if !snap.on_ground {
            self.is_analysing = true;
            self.last_vertical_speed = snap.vertical_speed_fpm;
        } else if self.is_analysing && self.metrics.is_none() {
            // Touched down! Capture the last airborne vertical speed as landing rate
            self.metrics = Some(LandingMetrics {
                vertical_speed_at_touchdown: self.last_vertical_speed,
                g_force: 1.0,
                bounce_detected: false,
            });
            self.is_analysing = false;
        }
    }
}
