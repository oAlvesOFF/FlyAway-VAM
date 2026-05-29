use std::sync::{Arc, Mutex};
use sim_core::{SimKind, SimSnapshot};
use sim_msfs::{ConnectionState, MsfsAdapter};

pub struct SimConnection {
    adapter: Arc<Mutex<MsfsAdapter>>,
}

impl SimConnection {
    pub fn new() -> Self {
        Self {
            adapter: Arc::new(Mutex::new(MsfsAdapter::new())),
        }
    }

    pub fn start(&self) {
        if let Ok(mut guard) = self.adapter.lock() {
            guard.start(SimKind::Msfs2024);
        }
    }

    pub fn stop(&self) {
        if let Ok(mut guard) = self.adapter.lock() {
            guard.stop();
        }
    }

    pub fn snapshot(&self) -> Option<SimSnapshot> {
        self.adapter.lock().ok().and_then(|g| g.snapshot())
    }

    pub fn state(&self) -> ConnectionState {
        self.adapter
            .lock()
            .map(|g| g.state())
            .unwrap_or(ConnectionState::Disconnected)
    }
}
