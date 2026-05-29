use serde::{Deserialize, Deserializer, Serialize};
use reqwest::Client;

/// Deserialise a field that Laravel may return as either a JSON number or a decimal string.
fn deserialize_f64_or_string<'de, D>(deserializer: D) -> Result<f64, D::Error>
where
    D: Deserializer<'de>,
{
    #[derive(Deserialize)]
    #[serde(untagged)]
    enum NumOrStr {
        Num(f64),
        Str(String),
    }
    match NumOrStr::deserialize(deserializer)? {
        NumOrStr::Num(n) => Ok(n),
        NumOrStr::Str(s) => s.parse::<f64>().map_err(serde::de::Error::custom),
    }
}

fn deserialize_option_f64_or_string<'de, D>(deserializer: D) -> Result<Option<f64>, D::Error>
where
    D: Deserializer<'de>,
{
    #[derive(Deserialize)]
    #[serde(untagged)]
    enum OptNumOrStr {
        Num(f64),
        Str(String),
    }
    match Option::<OptNumOrStr>::deserialize(deserializer)? {
        Some(OptNumOrStr::Num(n)) => Ok(Some(n)),
        Some(OptNumOrStr::Str(s)) => {
            if s.trim().is_empty() || s == "null" {
                Ok(None)
            } else {
                s.parse::<f64>().map(Some).map_err(serde::de::Error::custom)
            }
        }
        None => Ok(None),
    }
}

fn deserialize_option_string_or_number<'de, D>(deserializer: D) -> Result<Option<String>, D::Error>
where
    D: Deserializer<'de>,
{
    #[derive(Deserialize)]
    #[serde(untagged)]
    enum OptStrOrNum {
        Num(f64),
        Str(String),
    }
    match Option::<OptStrOrNum>::deserialize(deserializer)? {
        Some(OptStrOrNum::Str(s)) => {
            if s.trim().is_empty() || s == "null" {
                Ok(None)
            } else {
                Ok(Some(s))
            }
        }
        Some(OptStrOrNum::Num(n)) => Ok(Some(n.to_string())),
        None => Ok(None),
    }
}

#[derive(Serialize, Deserialize, Debug, Clone)]
pub struct FlightTelemetry {
    pub flight_number: String,
    pub aircraft_registration: String,
    pub aircraft_icao: String,
    pub aircraft_type: String,
    pub departure: String,
    pub arrival: String,
    pub departure_lat: Option<f64>,
    pub departure_lng: Option<f64>,
    pub arrival_lat: Option<f64>,
    pub arrival_lng: Option<f64>,
    pub current_lat: f64,
    pub current_lng: f64,
    pub heading: i32,
    pub altitude: i32,
    pub ground_speed: i32,
    pub phase: String,
}

#[derive(Serialize, Deserialize, Debug, Clone)]
pub struct RankInfo {
    pub id: u64,
    pub name: String,
    pub image: Option<String>,
    pub minimum_hours: f64,
}

#[derive(Serialize, Deserialize, Debug, Clone)]
pub struct UserInfo {
    pub id: u64,
    pub name: String,
    #[serde(default)]
    pub email: String,
    #[serde(default)]
    pub pilot_id: String,
    pub rank_id: Option<u64>,
    #[serde(deserialize_with = "deserialize_f64_or_string")]
    pub total_hours: f64,
    #[serde(default)]
    pub total_flights: u64,
    pub last_location: Option<String>,
    pub status: Option<String>,
    pub avatar: Option<String>,
    pub rank: Option<RankInfo>,
}

#[derive(Serialize, Deserialize, Debug, Clone)]
pub struct PirepRecord {
    pub id: u64,
    pub flight_number: String,
    pub departure: String,
    pub arrival: String,
    pub aircraft_registration: String,
    pub aircraft_icao: String,
    #[serde(deserialize_with = "deserialize_f64_or_string")]
    pub flight_time: f64,
    pub landing_rate: Option<i32>,
    pub score: Option<i32>,
    pub status: String,
    pub route: Option<String>,
    pub submitted_at: Option<String>,
    #[serde(default)]
    pub user: Option<UserInfo>,
}

#[derive(Serialize, Deserialize, Debug, Clone)]
pub struct ScheduleRecord {
    pub id: u64,
    pub flight_number: String,
    pub departure: String,
    pub arrival: String,
    pub route: Option<String>,
    pub aircraft_type: Option<String>,
    #[serde(default, deserialize_with = "deserialize_option_f64_or_string")]
    pub flight_time: Option<f64>,
    pub departure_time: Option<String>,
    #[serde(default, deserialize_with = "deserialize_option_string_or_number")]
    pub altitude: Option<String>,
}

#[derive(Serialize, Deserialize, Debug, Clone)]
pub struct AircraftRecord {
    pub id: u64,
    pub registration: String,
    pub icao: String,
    pub name: String,
    pub location: Option<String>,
    pub status: Option<String>,
    pub category: Option<String>,
    #[serde(default, deserialize_with = "deserialize_option_f64_or_string")]
    pub total_hours_since_service: Option<f64>,
}

#[derive(Serialize, Deserialize, Debug, Clone)]
pub struct BidRecord {
    pub id: u64,
    pub user_id: u64,
    pub schedule_id: Option<u64>,
    pub aircraft_id: Option<u64>,
    pub schedule: Option<ScheduleRecord>,
    pub aircraft: Option<AircraftRecord>,
}

#[derive(Serialize, Deserialize, Debug, Clone)]
pub struct ActiveFlightRecord {
    pub id: u64,
    pub flight_number: String,
    pub aircraft_registration: String,
    pub aircraft_icao: String,
    pub aircraft_type: String,
    pub departure: String,
    pub arrival: String,
    pub current_lat: Option<f64>,
    pub current_lng: Option<f64>,
    pub heading: Option<i32>,
    pub altitude: Option<i32>,
    pub ground_speed: Option<i32>,
    pub phase: String,
    pub status: String,
    pub user_id: Option<u64>,
}

#[derive(Serialize, Deserialize, Debug, Clone)]
pub struct PirepSubmitRequest {
    pub flight_number: String,
    pub departure: String,
    pub arrival: String,
    pub aircraft_registration: String,
    pub aircraft_icao: String,
    pub flight_time: f64,
    pub landing_rate: Option<i32>,
    pub route: Option<String>,
    pub log: Option<String>,
}

pub struct ApiClient {
    client: Client,
    base_url: String,
}

impl ApiClient {
    pub fn new(base_url: String) -> Self {
        let mut headers = reqwest::header::HeaderMap::new();
        if let Ok(val) = reqwest::header::HeaderValue::from_str("application/json") {
            headers.insert(reqwest::header::ACCEPT, val);
        }
        let client = Client::builder()
            .default_headers(headers)
            .build()
            .unwrap_or_else(|_| Client::new());

        Self {
            client,
            base_url,
        }
    }

    fn auth_header(&self, api_key: &str) -> String {
        format!("Bearer {}", api_key)
    }

    async fn handle_error_response(&self, response: reqwest::Response) -> String {
        let status = response.status();
        let body_text = response.text().await.unwrap_or_default();
        if let Ok(json_err) = serde_json::from_str::<serde_json::Value>(&body_text) {
            if let Some(msg) = json_err.get("error").and_then(|v| v.as_str()) {
                return msg.to_string();
            }
            if let Some(msg) = json_err.get("message").and_then(|v| v.as_str()) {
                return msg.to_string();
            }
        }
        format!("request failed with status {}: {}", status, body_text)
    }



    pub async fn fetch_me(&self, api_key: &str) -> Result<UserInfo, String> {
        let response = self.client
            .get(format!("{}/api/me", self.base_url))
            .header("Authorization", self.auth_header(api_key))
            .send()
            .await
            .map_err(|e| format!("me request failed: {}", e))?;
        if !response.status().is_success() {
            return Err(self.handle_error_response(response).await);
        }
        response.json().await.map_err(|e| format!("me parse failed: {}", e))
    }

    pub async fn fetch_pireps(&self, api_key: &str) -> Result<Vec<PirepRecord>, String> {
        let response = self.client
            .get(format!("{}/api/pireps?per_page=50", self.base_url))
            .header("Authorization", self.auth_header(api_key))
            .send()
            .await
            .map_err(|e| format!("pireps request failed: {}", e))?;
        if !response.status().is_success() {
            return Err(self.handle_error_response(response).await);
        }
        #[derive(Deserialize)]
        struct PirepPage {
            data: Vec<PirepRecord>,
        }
        let page: PirepPage = response.json().await.map_err(|e| format!("pireps parse failed: {}", e))?;
        Ok(page.data)
    }

    pub async fn fetch_schedules(&self, api_key: &str) -> Result<Vec<ScheduleRecord>, String> {
        let response = self.client
            .get(format!("{}/api/schedules", self.base_url))
            .header("Authorization", self.auth_header(api_key))
            .send()
            .await
            .map_err(|e| format!("schedules request failed: {}", e))?;
        if !response.status().is_success() {
            return Err(self.handle_error_response(response).await);
        }
        response.json().await.map_err(|e| format!("schedules parse failed: {}", e))
    }

    pub async fn fetch_aircraft(&self, api_key: &str) -> Result<Vec<AircraftRecord>, String> {
        let response = self.client
            .get(format!("{}/api/aircraft", self.base_url))
            .header("Authorization", self.auth_header(api_key))
            .send()
            .await
            .map_err(|e| format!("aircraft request failed: {}", e))?;
        if !response.status().is_success() {
            return Err(self.handle_error_response(response).await);
        }
        response.json().await.map_err(|e| format!("aircraft parse failed: {}", e))
    }

    pub async fn fetch_my_reservations(&self, api_key: &str) -> Result<Vec<BidRecord>, String> {
        let response = self.client
            .get(format!("{}/api/schedules/my-reservations", self.base_url))
            .header("Authorization", self.auth_header(api_key))
            .send()
            .await
            .map_err(|e| format!("reservations request failed: {}", e))?;
        if !response.status().is_success() {
            return Err(self.handle_error_response(response).await);
        }
        response.json().await.map_err(|e| format!("reservations parse failed: {}", e))
    }

    pub async fn fetch_active_flights(&self) -> Result<Vec<ActiveFlightRecord>, String> {
        let response = self.client
            .get(format!("{}/api/flights/active", self.base_url))
            .send()
            .await
            .map_err(|e| format!("active flights request failed: {}", e))?;
        if !response.status().is_success() {
            return Err(self.handle_error_response(response).await);
        }
        response.json().await.map_err(|e| format!("active flights parse failed: {}", e))
    }

    pub async fn update_flight_position(&self, api_key: &str, telemetry: &FlightTelemetry) -> Result<(), String> {
        // Skip if no flight context is set (empty flight_number)
        if telemetry.flight_number.is_empty() {
            return Ok(());
        }

        // Truncate fields to match server validation limits
        let mut sanitized = telemetry.clone();
        if sanitized.aircraft_type.len() > 100 {
            sanitized.aircraft_type = sanitized.aircraft_type[..100].to_string();
        }
        if sanitized.aircraft_icao.len() > 10 {
            sanitized.aircraft_icao = sanitized.aircraft_icao[..10].to_string();
        }
        if sanitized.aircraft_registration.len() > 20 {
            sanitized.aircraft_registration = sanitized.aircraft_registration[..20].to_string();
        }
        // Ensure departure/arrival are max 4 chars
        if sanitized.departure.len() > 4 {
            sanitized.departure = sanitized.departure[..4].to_string();
        }
        if sanitized.arrival.len() > 4 {
            sanitized.arrival = sanitized.arrival[..4].to_string();
        }

        let response = self.client
            .post(format!("{}/api/flights/track", self.base_url))
            .header("Authorization", self.auth_header(api_key))
            .json(&sanitized)
            .send()
            .await
            .map_err(|e| format!("track request failed: {}", e))?;
        if !response.status().is_success() {
            return Err(self.handle_error_response(response).await);
        }
        Ok(())
    }

    pub async fn complete_flight(&self, api_key: &str, flight_id: u64) -> Result<(), String> {
        let response = self.client
            .post(format!("{}/api/flights/{}/complete", self.base_url, flight_id))
            .header("Authorization", self.auth_header(api_key))
            .send()
            .await
            .map_err(|e| format!("complete request failed: {}", e))?;
        if !response.status().is_success() {
            return Err(self.handle_error_response(response).await);
        }
        Ok(())
    }

    pub async fn submit_pirep(&self, api_key: &str, pirep: &PirepSubmitRequest) -> Result<PirepRecord, String> {
        let response = self.client
            .post(format!("{}/api/pireps", self.base_url))
            .header("Authorization", self.auth_header(api_key))
            .json(&pirep)
            .send()
            .await
            .map_err(|e| format!("pirep submit failed: {}", e))?;
        if !response.status().is_success() {
            return Err(self.handle_error_response(response).await);
        }
        response.json().await.map_err(|e| format!("pirep parse failed: {}", e))
    }
}
