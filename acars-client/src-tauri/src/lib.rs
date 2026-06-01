mod api;
mod simulators;
mod flight_manager;
mod landing_analyser;

use std::sync::Arc;
use std::time::Duration;
use tauri::{Emitter, Manager};
use tokio::sync::RwLock;
use std::path::PathBuf;
use tokio::fs;
use api::{ActiveFlightRecord, AircraftRecord, ApiClient, BidRecord, FlightTelemetry, PirepRecord, PirepSubmitRequest, ScheduleRecord, UserInfo};
use flight_manager::{FlightManager, FlightPhase};
use landing_analyser::LandingAnalyser;
use simulators::msfs::SimConnection;

async fn load_queue(path: &PathBuf) -> Vec<FlightTelemetry> {
    if let Ok(data) = fs::read_to_string(path).await {
        serde_json::from_str(&data).unwrap_or_default()
    } else {
        Vec::new()
    }
}

async fn save_queue(path: &PathBuf, queue: &Vec<FlightTelemetry>) {
    if let Ok(data) = serde_json::to_string(queue) {
        let _ = fs::write(path, data).await;
    }
}


#[derive(Clone, serde::Serialize, serde::Deserialize)]
pub struct FlightContext {
    pub flight_number: String,
    pub aircraft_registration: String,
    pub aircraft_icao: String,
    pub aircraft_type: String,
    pub departure: String,
    pub arrival: String,
    #[serde(default)]
    pub departure_lat: Option<f64>,
    #[serde(default)]
    pub departure_lng: Option<f64>,
    #[serde(default)]
    pub arrival_lat: Option<f64>,
    #[serde(default)]
    pub arrival_lng: Option<f64>,
}

/// Combined app state to avoid Tauri type-based state conflicts
/// (two `Arc<RwLock<Option<String>>>` would collide).
pub struct AppState {
    pub api_key: Option<String>,
    pub pilot_id: Option<String>,
    pub flight_logs: String,
    pub landing_analyser: LandingAnalyser,
    pub flight_context: Option<FlightContext>,
    pub start_time: Option<std::time::SystemTime>,
}

#[cfg_attr(mobile, tauri::mobile_entry_point)]
pub fn run() {
    let state = Arc::new(RwLock::new(AppState { 
        api_key: None, 
        pilot_id: None, 
        flight_logs: String::new(),
        landing_analyser: LandingAnalyser::new(),
        flight_context: None,
        start_time: None,
    }));
    let flight_context_state = Arc::new(RwLock::new(None::<FlightContext>));

    let state_ref = state.clone();
    // flight_context_ref is now handled via state_ref


    tauri::Builder::default()
        .plugin(tauri_plugin_store::Builder::new().build())
        .plugin(tauri_plugin_keyring::init())
        .manage(state)
        .manage(flight_context_state)
        .invoke_handler(tauri::generate_handler![
            update_telemetry, set_api_key, set_pilot_id, get_pilot_id,
            set_flight_context, start_simulator, stop_simulator, simulator_state,
            fetch_pireps, fetch_schedules, fetch_aircraft, fetch_me,
            fetch_my_reservations, fetch_active_flights, submit_pirep, complete_flight, complete_flight_with_pirep, cancel_flight
        ])
        .setup(move |app| {
            let app_handle = app.app_handle().clone();
            let sim = SimConnection::new();
            sim.start();
            app.manage(sim);

            tauri::async_runtime::spawn(async move {
                let api_client = ApiClient::new("https://v1.flyazoresvirtual.com".to_string());
                let mut flight_manager = FlightManager::new();
                let mut last_phase = None;
                let mut last_sim_state = String::new();
                let mut last_perf_log = std::time::Instant::now();
                
                let local_data_dir = app_handle.path().app_local_data_dir().unwrap_or_else(|_| std::env::temp_dir());
                let _ = fs::create_dir_all(&local_data_dir).await;
                let queue_path = local_data_dir.join("telemetry_queue.json");
                let mut telemetry_queue = load_queue(&queue_path).await;


                loop {
                    let sim: tauri::State<'_, SimConnection> = app_handle.state();

                    // Always emit simulator state so frontend gets real-time updates
                    let current_sim_state = format!("{:?}", sim.state());
                    if current_sim_state != last_sim_state {
                        let _ = app_handle.emit("sim-state-changed", &current_sim_state);
                        last_sim_state = current_sim_state.clone();
                    }

                    if let Some(snap) = sim.snapshot() {
                        // Telemetry is flowing
                        let mut telemetry = FlightTelemetry {
                            flight_number: String::new(),
                            aircraft_registration: String::new(),
                            aircraft_icao: snap.aircraft_icao.clone().unwrap_or_default(),
                            aircraft_type: snap.aircraft_title.clone().unwrap_or_default(),
                            departure: String::new(),
                            arrival: String::new(),
                            departure_lat: None,
                            departure_lng: None,
                            arrival_lat: None,
                            arrival_lng: None,
                            current_lat: snap.lat,
                            current_lng: snap.lon,
                            heading: snap.heading_deg_true as i32,
                            altitude: snap.altitude_msl_ft as i32,
                            ground_speed: snap.groundspeed_kt as i32,
                            phase: String::new(),
                        };
                        
                        if let Some(ctx) = state_ref.read().await.flight_context.as_ref() {
                            telemetry.flight_number = ctx.flight_number.clone();
                            telemetry.aircraft_registration = ctx.aircraft_registration.clone();
                            telemetry.aircraft_icao = ctx.aircraft_icao.clone();
                            telemetry.aircraft_type = ctx.aircraft_type.clone();
                            telemetry.departure = ctx.departure.clone();
                            telemetry.arrival = ctx.arrival.clone();
                            telemetry.departure_lat = ctx.departure_lat;
                            telemetry.departure_lng = ctx.departure_lng;
                            telemetry.arrival_lat = ctx.arrival_lat;
                            telemetry.arrival_lng = ctx.arrival_lng;
                        }
                        
                        let phase = flight_manager.update_phase(&snap, &telemetry);
                        
                        let laravel_phase = match phase {
                            FlightPhase::Preflight => "preflight",
                            FlightPhase::Pushback | FlightPhase::TaxiOut | FlightPhase::Takeoff => "departed",
                            FlightPhase::Climb | FlightPhase::Cruise | FlightPhase::Descent | FlightPhase::GoAround => "enroute",
                            FlightPhase::Approach => "onapproach",
                            FlightPhase::Landing | FlightPhase::TaxiIn | FlightPhase::Shutdown => "landed",
                        };
                        telemetry.phase = laravel_phase.to_string();
                        
                        if Some(&phase) != last_phase.as_ref() {
                            let now = chrono::Utc::now().format("%H:%M:%S").to_string();
                            let mut s = state_ref.write().await;

                            // OOOI Log Triggers
                            match (&last_phase, &phase) {
                                (Some(FlightPhase::Preflight | FlightPhase::Pushback), FlightPhase::TaxiOut) => {
                                    s.flight_logs.push_str(&format!("[{}] [OUT] Block out. Pushback/Taxi started.\n", now));
                                    // Set/reset start_time to actual block-out moment for accurate flight time
                                    s.start_time = Some(std::time::SystemTime::now());
                                }
                                (Some(FlightPhase::TaxiOut | FlightPhase::Takeoff), FlightPhase::Climb) => {
                                    s.flight_logs.push_str(&format!("[{}] [OFF] Airborne. Takeoff complete.\n", now));
                                }
                                (Some(FlightPhase::Approach | FlightPhase::Descent), FlightPhase::Landing) => {
                                    s.flight_logs.push_str(&format!("[{}] [ON] Touchdown. Welcome to {}.\n", now, telemetry.arrival));
                                }
                                (Some(FlightPhase::TaxiIn | FlightPhase::Landing), FlightPhase::Shutdown) => {
                                    s.flight_logs.push_str(&format!("[{}] [IN] Block in. Engines shutdown, parking brake set.\n", now));
                                }
                                _ => {}
                            }

                            s.flight_logs.push_str(&format!("[{}] Phase: {:?}\n", now, phase));
                            last_phase = Some(phase.clone());
                        }

                        // Periodic Performance Log (Every 10 mins)
                        if last_perf_log.elapsed() > std::time::Duration::from_secs(600) {
                            let now = chrono::Utc::now().format("%H:%M:%S").to_string();
                            let mut s = state_ref.write().await;
                            s.flight_logs.push_str(&format!(
                                "[{}] [PERF] Altitude: {}ft | GS: {}kt | Fuel: {:.1}kg | Wind: {:03}/{:02}kt\n",
                                now,
                                telemetry.altitude,
                                telemetry.ground_speed,
                                snap.fuel_total_kg,
                                snap.wind_direction_deg.unwrap_or(0.0) as i32,
                                snap.wind_speed_kt.unwrap_or(0.0) as i32
                            ));
                            last_perf_log = std::time::Instant::now();
                        }
                        // Always process telemetry for landing analysis to catch unexpected touchdowns
                        {
                            let mut s = state_ref.write().await;
                            s.landing_analyser.process_telemetry(&snap);
                        }
                        
                        let _ = app_handle.emit("telemetry-updated", &telemetry);
                        
                        let (has_ctx, key) = {
                            let s = state_ref.read().await;
                            (s.flight_context.is_some(), s.api_key.clone())
                        };

                        if has_ctx {
                            if let Some(ref api_key) = key {
                                // Add current telemetry to queue
                                telemetry_queue.push(telemetry);
                                
                                // Limit queue size to avoid massive memory/disk usage on very long offline periods
                                // (1000 items is ~16 mins of data at 1Hz, sufficient for tracking gaps)
                                if telemetry_queue.len() > 1000 {
                                    telemetry_queue.remove(0);
                                }
                                
                                let mut queue_modified = true;
                                
                                // Try to flush the queue (oldest first)
                                // We send up to 5 items per iteration to catch up quickly when online
                                let mut sent_count = 0;
                                while !telemetry_queue.is_empty() && sent_count < 5 {
                                    let item_to_send = telemetry_queue[0].clone();
                                    match api_client.update_flight_position(api_key, &item_to_send).await {
                                        Ok(_) => {
                                            telemetry_queue.remove(0);
                                            sent_count += 1;
                                            queue_modified = true;
                                        }
                                        Err(e) => {
                                            if e.starts_with("NETWORK_ERROR") || e.starts_with("SERVER_ERROR") {
                                                eprintln!("[tracking] Network/Server offline, keeping in queue: {}", e);
                                            } else {
                                                eprintln!("[tracking] Client error, discarding telemetry: {}", e);
                                                telemetry_queue.remove(0);
                                                queue_modified = true;
                                            }
                                            break; // Stop flushing on first error
                                        }
                                    }
                                }
                                
                                if queue_modified {
                                    save_queue(&queue_path, &telemetry_queue).await;
                                }

                            } else {
                                eprintln!("[tracking] No API key found in state");
                            }
                        }
                    }
                    
                    tokio::time::sleep(Duration::from_secs(1)).await;
                }
            });

            Ok(())
        })
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}

#[tauri::command]
async fn set_api_key(key: String, state: tauri::State<'_, Arc<RwLock<AppState>>>) -> Result<(), String> {
    state.write().await.api_key = Some(key);
    Ok(())
}

#[tauri::command]
async fn set_pilot_id(pilot_id: String, state: tauri::State<'_, Arc<RwLock<AppState>>>) -> Result<(), String> {
    state.write().await.pilot_id = Some(pilot_id);
    Ok(())
}

#[tauri::command]
async fn get_pilot_id(state: tauri::State<'_, Arc<RwLock<AppState>>>) -> Result<Option<String>, String> {
    Ok(state.read().await.pilot_id.clone())
}

#[tauri::command]
async fn set_flight_context(ctx: FlightContext, state: tauri::State<'_, Arc<RwLock<AppState>>>, flight_context_state: tauri::State<'_, Arc<RwLock<Option<FlightContext>>>>) -> Result<(), String> {
    let mut s = state.write().await;
    s.flight_context = Some(ctx.clone());
    s.flight_logs.clear();
    s.flight_logs.push_str("Flight started\n");
    s.start_time = Some(std::time::SystemTime::now());

    let mut lock = flight_context_state.write().await;
    *lock = Some(ctx);

    Ok(())
}

#[tauri::command]
async fn update_telemetry(api_key: String, telemetry: FlightTelemetry) -> Result<(), String> {
    let api_client = ApiClient::new("https://v1.flyazoresvirtual.com".to_string());
    api_client.update_flight_position(&api_key, &telemetry).await
}

#[tauri::command]
async fn start_simulator(sim: tauri::State<'_, SimConnection>) -> Result<(), String> {
    sim.start();
    Ok(())
}

#[tauri::command]
async fn stop_simulator(sim: tauri::State<'_, SimConnection>) -> Result<(), String> {
    sim.stop();
    Ok(())
}

#[tauri::command]
async fn simulator_state(sim: tauri::State<'_, SimConnection>) -> Result<String, String> {
    Ok(format!("{:?}", sim.state()))
}

// --- API fetch commands ---

fn get_api_client() -> ApiClient {
    ApiClient::new("https://v1.flyazoresvirtual.com".to_string())
}

#[tauri::command]
async fn fetch_pireps(state: tauri::State<'_, Arc<RwLock<AppState>>>) -> Result<Vec<PirepRecord>, String> {
    let key = state.read().await.api_key.clone().ok_or_else(|| "no api key set".to_string())?;
    get_api_client().fetch_pireps(&key).await
}

#[tauri::command]
async fn fetch_schedules(state: tauri::State<'_, Arc<RwLock<AppState>>>) -> Result<Vec<ScheduleRecord>, String> {
    let key = state.read().await.api_key.clone().ok_or_else(|| "no api key set".to_string())?;
    get_api_client().fetch_schedules(&key).await
}

#[tauri::command]
async fn fetch_aircraft(state: tauri::State<'_, Arc<RwLock<AppState>>>) -> Result<Vec<AircraftRecord>, String> {
    let key = state.read().await.api_key.clone().ok_or_else(|| "no api key set".to_string())?;
    get_api_client().fetch_aircraft(&key).await
}

#[tauri::command]
async fn fetch_me(state: tauri::State<'_, Arc<RwLock<AppState>>>) -> Result<UserInfo, String> {
    let key = state.read().await.api_key.clone().ok_or_else(|| "no api key set".to_string())?;
    get_api_client().fetch_me(&key).await
}

#[tauri::command]
async fn fetch_my_reservations(state: tauri::State<'_, Arc<RwLock<AppState>>>) -> Result<Vec<BidRecord>, String> {
    let key = state.read().await.api_key.clone().ok_or_else(|| "no api key set".to_string())?;
    get_api_client().fetch_my_reservations(&key).await
}

#[tauri::command]
async fn fetch_active_flights() -> Result<Vec<ActiveFlightRecord>, String> {
    get_api_client().fetch_active_flights().await
}

#[tauri::command]
async fn complete_flight_with_pirep(
    state: tauri::State<'_, Arc<RwLock<AppState>>>,
    flight_context_state: tauri::State<'_, Arc<RwLock<Option<FlightContext>>>>,
) -> Result<PirepRecord, String> {
    let s = state.read().await;
    
    let ctx = s.flight_context.as_ref().ok_or_else(|| "No active flight context found".to_string())?;
    let key = s.api_key.clone().ok_or_else(|| "No API key set".to_string())?;
    
    let landing_rate = s.landing_analyser.metrics.as_ref()
        .map(|m| m.vertical_speed_at_touchdown as i32);

    let flare_profile = s.landing_analyser.metrics.as_ref()
        .map(|m| m.flare_profile.clone());
    
    let logs = s.flight_logs.clone();
    
    let flight_time_hours = match s.start_time {
        Some(start) => {
            match std::time::SystemTime::now().duration_since(start) {
                Ok(duration) => duration.as_secs_f64() / 3600.0,
                Err(_) => 0.1,
            }
        }
        None => 0.1,
    };
    let flight_time = flight_time_hours.max(0.01);
    
    let pirep_req = PirepSubmitRequest {
        flight_number: ctx.flight_number.clone(),
        departure: ctx.departure.clone(),
        arrival: ctx.arrival.clone(),
        aircraft_registration: ctx.aircraft_registration.clone(),
        aircraft_icao: ctx.aircraft_icao.clone(),
        flight_time,
        landing_rate,
        route: None,
        log: if !logs.is_empty() { Some(logs) } else { None },
    };
    
    drop(s);
    
    let mut result = get_api_client().submit_pirep(&key, &pirep_req).await;
    
    if let Ok(ref mut r) = result {
        r.flare_profile = flare_profile;
        let mut s_write = state.write().await;
        s_write.flight_logs.clear();
        s_write.landing_analyser = LandingAnalyser::new();
        s_write.flight_context = None;
        s_write.start_time = None;
        
        let mut ctx_write = flight_context_state.write().await;
        *ctx_write = None;
    }
    
    result
}

#[tauri::command]
async fn cancel_flight(
    state: tauri::State<'_, Arc<RwLock<AppState>>>,
    flight_context_state: tauri::State<'_, Arc<RwLock<Option<FlightContext>>>>,
) -> Result<(), String> {
    let s = state.read().await;
    
    let ctx = s.flight_context.as_ref().ok_or_else(|| "No active flight context found".to_string())?;
    let key = s.api_key.clone().ok_or_else(|| "No API key set".to_string())?;
    let flight_number = ctx.flight_number.clone();
    drop(s);

    // Find the active flight ID on the server to complete/cancel it
    let active_flights = get_api_client().fetch_active_flights().await
        .map_err(|e| format!("Failed to fetch active flights: {}", e))?;
    
    if let Some(flight) = active_flights.iter().find(|f| f.flight_number == flight_number) {
        get_api_client().complete_flight(&key, flight.id).await?;
    }

    // Clear local state
    let mut s_write = state.write().await;
    s_write.flight_logs.clear();
    s_write.landing_analyser = LandingAnalyser::new();
    s_write.flight_context = None;
    s_write.start_time = None;
    
    let mut ctx_write = flight_context_state.write().await;
    *ctx_write = None;

    Ok(())
}

#[tauri::command]
async fn submit_pirep(state: tauri::State<'_, Arc<RwLock<AppState>>>, mut pirep: PirepSubmitRequest) -> Result<PirepRecord, String> {

    let s = state.read().await;
    let key = s.api_key.clone().ok_or_else(|| "no api key set".to_string())?;
    let logs = s.flight_logs.clone();
    drop(s);

    if !logs.is_empty() {
        pirep.log = Some(logs);
    }

    get_api_client().submit_pirep(&key, &pirep).await
}

#[tauri::command]
async fn complete_flight(state: tauri::State<'_, Arc<RwLock<AppState>>>, flight_id: u64) -> Result<(), String> {
    let key = state.read().await.api_key.clone().ok_or_else(|| "no api key set".to_string())?;
    get_api_client().complete_flight(&key, flight_id).await
}
