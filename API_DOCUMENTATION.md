# La-ot API Documentation

## 📱 Overview

The La-ot API provides a unified interface for user management, athlete profiles, workouts, and goals tracking. The API supports both simple and full registration/login modes for flexibility and is designed for mobile app integration with Samsung Galaxy Watch 5 Pro and a vanilla web dashboard.

**Base URL:** `https://laot.great-site.net/laot-api/api/`

## 🔐 Authentication

The API uses JWT tokens for authentication. Include the token in the Authorization header:

```
Authorization: Bearer <your_jwt_token>
```

**JWT Token Details:**

- **Algorithm:** HS256
- **Expiration:** 24 hours
- **Secret:** Server-side configured
- **Payload:** `user_id`, `username`, `user_role`, `iat`, `exp`

## 📋 API Endpoints

### 1. User Registration

#### Simple Registration (Username + Password Only)

**Endpoint:** `POST /register.php`

**Request Body:**

```json
{
  "username": "testuser123",
  "password": "TestPass123"
}
```

**Response (201 Created):**

```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user_id": 12,
    "username": "testuser123",
    "first_name": "testuser123",
    "last_name": "",
    "email": "testuser123@example.com",
    "university": "La-ot University",
    "user_role": "athlete",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "registration_mode": "simple"
  },
  "timestamp": "2025-08-28 08:47:33"
}
```

#### Full Registration (All Fields)

**Endpoint:** `POST /register.php`

**Request Body:**

```json
{
  "username": "johndoe",
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "password": "SecurePass123",
  "university": "La-ot University",
  "age": 25,
  "weight": 75.5,
  "height": "180cm",
  "user_role": "athlete",
  "sport": "Football",
  "position": "Forward",
  "team": "Team Alpha",
  "fitness_level": "intermediate"
}
```

**Response (201 Created):**

```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user_id": 13,
    "username": "johndoe",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "university": "La-ot University",
    "user_role": "athlete",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "registration_mode": "full"
  },
  "timestamp": "2025-08-28 08:47:33"
}
```

### 2. User Login

**Endpoint:** `POST /login.php`

**Request Body:**

```json
{
  "username": "testuser123",
  "password": "TestPass123"
}
```

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user_id": 12,
    "username": "testuser123",
    "first_name": "testuser123",
    "last_name": "",
    "email": "testuser123@example.com",
    "university": "La-ot University",
    "user_role": "athlete",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "profile": {
      "sport": "General",
      "position": "Player",
      "team": "Team",
      "fitness_level": "beginner"
    },
    "coach_data": null,
    "login_timestamp": "2025-08-28 08:47:33"
  },
  "timestamp": "2025-08-28 08:47:33"
}
```

### 3. User Profile

**Endpoint:** `GET /profile.php`

**Headers:**

```
Authorization: Bearer <your_jwt_token>
```

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Profile retrieved successfully",
  "data": {
    "user": {
      "id": 12,
      "username": "testuser123",
      "first_name": "testuser123",
      "last_name": "",
      "email": "testuser123@example.com",
      "university": "La-ot University",
      "user_role": "athlete"
    },
    "profile": {
      "sport": "General",
      "position": "Player",
      "team": "Team",
      "fitness_level": "beginner"
    }
  },
  "timestamp": "2025-08-28 08:47:33"
}
```

### 4. Workouts

**Endpoint:** `GET /workouts.php`

**Headers:**

```
Authorization: Bearer <your_jwt_token>
```

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Workouts retrieved successfully",
  "data": [
    {
      "id": 1,
      "session_date": "2025-08-28",
      "start_time": "09:00:00",
      "end_time": "10:30:00",
      "duration_minutes": 90,
      "workout_type": "Cardio",
      "notes": "Morning run in the park"
    }
  ],
  "timestamp": "2025-08-28 08:47:33"
}
```

### 5. Goals

**Endpoint:** `GET /goals.php`

**Headers:**

```
Authorization: Bearer <your_jwt_token>
```

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Goals retrieved successfully",
  "data": [
    {
      "id": 1,
      "goal_type": "distance",
      "target_value": "5000.00",
      "current_value": "3200.00",
      "target_date": "2025-09-30",
      "is_completed": false
    }
  ],
  "timestamp": "2025-08-28 08:47:33"
}
```

## 🚨 Error Responses

### 400 Bad Request

```json
{
  "success": false,
  "message": "Username and password are required",
  "data": null,
  "timestamp": "2025-08-28 08:47:33"
}
```

### 401 Unauthorized

```json
{
  "success": false,
  "message": "Invalid username or password",
  "data": null,
  "timestamp": "2025-08-28 08:47:33"
}
```

### 403 Forbidden

```json
{
  "success": false,
  "message": "Account is deactivated",
  "data": null,
  "timestamp": "2025-08-28 08:47:33"
}
```

### 409 Conflict

```json
{
  "success": false,
  "message": "Username or email already exists",
  "data": null,
  "timestamp": "2025-08-28 08:47:33"
}
```

### 500 Internal Server Error

```json
{
  "success": false,
  "message": "Database error",
  "data": null,
  "timestamp": "2025-08-28 08:47:33"
}
```

## 💻 Code Examples

### Vanilla JavaScript (Web Dashboard)

#### Registration

```javascript
// Registration data interface
const RegistrationData = {
  username: "",
  password: "",
  first_name: "",
  last_name: "",
  email: "",
  university: "",
  age: null,
  user_role: "athlete",
};

async function registerUser(data) {
  try {
    const response = await fetch(
      "https://laot.great-site.net/laot-api/api/register.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      }
    );

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    if (result.success) {
      // Store JWT token
      localStorage.setItem("jwt_token", result.data.token);
      return result.data;
    } else {
      throw new Error(result.message);
    }
  } catch (error) {
    console.error("Registration failed:", error);
    throw error;
  }
}

// Simple registration
const simpleUser = await registerUser({
  username: "testuser123",
  password: "TestPass123",
});

// Full registration
const fullUser = await registerUser({
  username: "johndoe",
  first_name: "John",
  last_name: "Doe",
  email: "john.doe@example.com",
  password: "SecurePass123",
  university: "La-ot University",
  age: 25,
  user_role: "athlete",
});
```

#### Login

```javascript
async function loginUser(data) {
  try {
    const response = await fetch(
      "https://laot.great-site.net/laot-api/api/login.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      }
    );

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    if (result.success) {
      // Store JWT token
      localStorage.setItem("jwt_token", result.data.token);
      return result.data;
    } else {
      throw new Error(result.message);
    }
  } catch (error) {
    console.error("Login failed:", error);
    throw error;
  }
}

const user = await loginUser({
  username: "testuser123",
  password: "TestPass123",
});
```

#### Authenticated Requests

```javascript
async function makeAuthenticatedRequest(endpoint, options = {}) {
  const token = localStorage.getItem("jwt_token");

  if (!token) {
    throw new Error("No authentication token found");
  }

  const response = await fetch(
    `https://laot.great-site.net/laot-api/api/${endpoint}`,
    {
      ...options,
      headers: {
        ...options.headers,
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
      },
    }
  );

  if (!response.ok) {
    if (response.status === 401) {
      // Token expired or invalid
      localStorage.removeItem("jwt_token");
      throw new Error("Authentication expired. Please login again.");
    }
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  return await response.json();
}

// Get user profile
const profile = await makeAuthenticatedRequest("profile.php");

// Get workouts
const workouts = await makeAuthenticatedRequest("workouts.php");

// Get goals
const goals = await makeAuthenticatedRequest("goals.php");
```

#### Web Dashboard Integration

```javascript
// Dashboard API service
class LaotDashboardService {
  constructor() {
    this.baseUrl = "https://laot.great-site.net/laot-api/api";
    this.token = localStorage.getItem("jwt_token");
  }

  setToken(token) {
    this.token = token;
    localStorage.setItem("jwt_token", token);
  }

  clearToken() {
    this.token = null;
    localStorage.removeItem("jwt_token");
  }

  async request(endpoint, options = {}) {
    const url = `${this.baseUrl}/${endpoint}`;

    const headers = {
      "Content-Type": "application/json",
      ...options.headers,
    };

    if (this.token) {
      headers.Authorization = `Bearer ${this.token}`;
    }

    const response = await fetch(url, {
      ...options,
      headers,
    });

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw new Error(errorData.message || `HTTP ${response.status}`);
    }

    return response.json();
  }

  async register(data) {
    const result = await this.request("register.php", {
      method: "POST",
      body: JSON.stringify(data),
    });

    if (result.success) {
      this.setToken(result.data.token);
    }

    return result;
  }

  async login(data) {
    const result = await this.request("login.php", {
      method: "POST",
      body: JSON.stringify(data),
    });

    if (result.success) {
      this.setToken(result.data.token);
    }

    return result;
  }

  async getProfile() {
    return this.request("profile.php");
  }

  async getWorkouts() {
    return this.request("workouts.php");
  }

  async getGoals() {
    return this.request("goals.php");
  }
}

// Initialize dashboard service
const dashboardService = new LaotDashboardService();
```

#### HTML Integration Example

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>La-ot Coach Dashboard</title>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <div class="dashboard">
      <header>
        <h1>La-ot Coach Dashboard</h1>
        <div id="user-info"></div>
      </header>

      <nav>
        <button onclick="showSection('athletes')">Athletes</button>
        <button onclick="showSection('workouts')">Workouts</button>
        <button onclick="showSection('goals')">Goals</button>
      </nav>

      <main id="main-content">
        <!-- Content will be loaded dynamically -->
      </main>
    </div>

    <script src="dashboard.js"></script>
  </body>
</html>
```

#### CSS Styling

```css
/* styles.css */
.dashboard {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  font-family: Arial, sans-serif;
}

header {
  background: #2c3e50;
  color: white;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

nav {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

nav button {
  padding: 10px 20px;
  border: none;
  background: #3498db;
  color: white;
  border-radius: 5px;
  cursor: pointer;
}

nav button:hover {
  background: #2980b9;
}

.athlete-card {
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.workout-item {
  background: #f8f9fa;
  border-left: 4px solid #28a745;
  padding: 15px;
  margin-bottom: 10px;
  border-radius: 0 5px 5px 0;
}
```

### Kotlin (Android Mobile App)

#### API Service

```kotlin
// LaotApiService.kt
class LaotApiService {
    private val baseUrl = "https://laot.great-site.net/laot-api/api"
    private var token: String? = null
    private val client = OkHttpClient.Builder()
        .connectTimeout(30, TimeUnit.SECONDS)
        .readTimeout(30, TimeUnit.SECONDS)
        .writeTimeout(30, TimeUnit.SECONDS)
        .build()

    fun setToken(token: String) {
        this.token = token
    }

    fun clearToken() {
        this.token = null
    }

    private fun makeRequest(endpoint: String, method: String, body: String? = null): String {
        val url = "$baseUrl/$endpoint"
        val requestBuilder = Request.Builder().url(url)

        // Add headers
        requestBuilder.addHeader("Content-Type", "application/json")
        token?.let { requestBuilder.addHeader("Authorization", "Bearer $it") }

        // Set method and body
        when (method) {
            "GET" -> requestBuilder.get()
            "POST" -> {
                val requestBody = body?.let {
                    RequestBody.create("application/json".toMediaType(), it)
                }
                requestBuilder.post(requestBody ?: RequestBody.create(null, ""))
            }
        }

        val request = requestBuilder.build()
        val response = client.newCall(request).execute()

        if (!response.isSuccessful) {
            throw IOException("HTTP ${response.code}: ${response.message}")
        }

        return response.body?.string() ?: ""
    }

    fun register(username: String, password: String, additionalData: Map<String, Any>? = null): JSONObject {
        val data = mutableMapOf(
            "username" to username,
            "password" to password
        )

        additionalData?.let { data.putAll(it) }

        val body = JSONObject(data).toString()
        val result = makeRequest("register.php", "POST", body)
        val jsonResult = JSONObject(result)

        if (jsonResult.getBoolean("success")) {
            val token = jsonResult.getJSONObject("data").getString("token")
            setToken(token)
        }

        return jsonResult
    }

    fun login(username: String, password: String): JSONObject {
        val data = mapOf(
            "username" to username,
            "password" to password
        )

        val body = JSONObject(data).toString()
        val result = makeRequest("login.php", "POST", body)
        val jsonResult = JSONObject(result)

        if (jsonResult.getBoolean("success")) {
            val token = jsonResult.getJSONObject("data").getString("token")
            setToken(token)
        }

        return jsonResult
    }

    fun getProfile(): JSONObject {
        val result = makeRequest("profile.php", "GET")
        return JSONObject(result)
    }

    fun getWorkouts(): JSONObject {
        val result = makeRequest("workouts.php", "GET")
        return JSONObject(result)
    }

    fun getGoals(): JSONObject {
        val result = makeRequest("goals.php", "GET")
        return JSONObject(result)
    }
}
```

#### Data Models

```kotlin
// DataModels.kt
data class User(
    val id: Int,
    val username: String,
    val firstName: String,
    val lastName: String,
    val email: String,
    val university: String,
    val userRole: String,
    val isActive: Boolean
)

data class AthleteProfile(
    val id: Int,
    val userId: Int,
    val sport: String?,
    val position: String?,
    val team: String?,
    val fitnessLevel: String,
    val goals: String?
)

data class WorkoutSession(
    val id: Int,
    val sessionDate: String,
    val startTime: String?,
    val endTime: String?,
    val durationMinutes: Int?,
    val workoutType: String?,
    val notes: String?
)

data class Goal(
    val id: Int,
    val goalType: String,
    val targetValue: Double?,
    val currentValue: Double,
    val targetDate: String?,
    val isCompleted: Boolean
)
```

#### Main Activity

```kotlin
// MainActivity.kt
class MainActivity : AppCompatActivity() {
    private lateinit var apiService: LaotApiService
    private lateinit var binding: ActivityMainBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        apiService = LaotApiService()

        // Check if user is already logged in
        val savedToken = getSharedPreferences("laot_prefs", Context.MODE_PRIVATE)
            .getString("jwt_token", null)

        if (savedToken != null) {
            apiService.setToken(savedToken)
            showMainContent()
        } else {
            showLoginScreen()
        }
    }

    private fun showLoginScreen() {
        binding.loginLayout.visibility = View.VISIBLE
        binding.mainLayout.visibility = View.GONE

        binding.loginButton.setOnClickListener {
            performLogin()
        }
    }

    private fun performLogin() {
        val username = binding.usernameInput.text.toString()
        val password = binding.passwordInput.text.toString()

        if (username.isBlank() || password.isBlank()) {
            Toast.makeText(this, "Please fill all fields", Toast.LENGTH_SHORT).show()
            return
        }

        // Show loading
        binding.loginButton.isEnabled = false
        binding.loginButton.text = "Logging in..."

        CoroutineScope(Dispatchers.IO).launch {
            try {
                val result = apiService.login(username, password)

                withContext(Dispatchers.Main) {
                    if (result.getBoolean("success")) {
                        // Save token
                        getSharedPreferences("laot_prefs", Context.MODE_PRIVATE)
                            .edit()
                            .putString("jwt_token", apiService.getToken())
                            .apply()

                        showMainContent()
                    } else {
                        Toast.makeText(
                            this@MainActivity,
                            result.getString("message"),
                            Toast.LENGTH_LONG
                        ).show()
                    }
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    Toast.makeText(
                        this@MainActivity,
                        "Login failed: ${e.message}",
                        Toast.LENGTH_LONG
                    ).show()
                }
            } finally {
                withContext(Dispatchers.Main) {
                    binding.loginButton.isEnabled = true
                    binding.loginButton.text = "Login"
                }
            }
        }
    }

    private fun showMainContent() {
        binding.loginLayout.visibility = View.GONE
        binding.mainLayout.visibility = View.VISIBLE

        // Load user data
        loadUserData()
    }

    private fun loadUserData() {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val profile = apiService.getProfile()
                val workouts = apiService.getWorkouts()
                val goals = apiService.getGoals()

                withContext(Dispatchers.Main) {
                    updateUI(profile, workouts, goals)
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    Toast.makeText(
                        this@MainActivity,
                        "Failed to load data: ${e.message}",
                        Toast.LENGTH_LONG
                    ).show()
                }
            }
        }
    }

    private fun updateUI(profile: JSONObject, workouts: JSONObject, goals: JSONObject) {
        // Update UI with loaded data
        // Implementation depends on your layout
    }
}
```

#### Samsung Galaxy Watch 5 Pro Integration

```kotlin
// WatchIntegrationService.kt
class WatchIntegrationService(private val context: Context) {
    private val wearClient = Wearable.getNodeClient(context)
    private val dataClient = Wearable.getDataClient(context)
    private val messageClient = Wearable.getMessageClient(context)

    // Send workout data to watch
    fun sendWorkoutData(workoutData: WorkoutData) {
        val dataMap = DataMap().apply {
            putString("type", "workout")
            putString("startTime", workoutData.startTime)
            putString("workoutType", workoutData.workoutType)
        }

        val dataItem = DataItem.Builder()
            .setUri(Uri.parse("/workout"))
            .setDataMap(dataMap)
            .build()

        dataClient.putDataItem(dataItem)
    }

    // Send goals to watch
    fun sendGoals(goals: List<Goal>) {
        val dataMap = DataMap().apply {
            putString("type", "goals")
            putInt("count", goals.size)

            goals.forEachIndexed { index, goal ->
                putString("goal_${index}_type", goal.goalType)
                putDouble("goal_${index}_target", goal.targetValue ?: 0.0)
                putDouble("goal_${index}_current", goal.currentValue)
            }
        }

        val dataItem = DataItem.Builder()
            .setUri(Uri.parse("/goals"))
            .setDataMap(dataMap)
            .build()

        dataClient.putDataItem(dataItem)
    }

    // Listen for messages from watch
    fun startMessageListener() {
        messageClient.addListener { messageEvent ->
            when (messageEvent.path) {
                "/workout_start" -> handleWorkoutStart(messageEvent)
                "/workout_stop" -> handleWorkoutStop(messageEvent)
                "/biometric_data" -> handleBiometricData(messageEvent)
            }
        }
    }

    private fun handleWorkoutStart(messageEvent: MessageEvent) {
        val data = DataMap.fromByteArray(messageEvent.data)
        val workoutType = data.getString("workout_type")

        // Start workout tracking
        startWorkoutSession(workoutType)
    }

    private fun handleWorkoutStop(messageEvent: MessageEvent) {
        val data = DataMap.fromByteArray(messageEvent.data)
        val duration = data.getLong("duration")
        val calories = data.getInt("calories")

        // Stop workout tracking and save
        stopWorkoutSession(duration, calories)
    }

    private fun handleBiometricData(messageEvent: MessageEvent) {
        val data = DataMap.fromByteArray(messageEvent.data)
        val heartRate = data.getInt("heart_rate")
        val steps = data.getInt("steps")
        val distance = data.getDouble("distance")

        // Process and store biometric data
        processBiometricData(heartRate, steps, distance)
    }
}
```

#### Biometric Data Processing

```kotlin
// BiometricDataProcessor.kt
class BiometricDataProcessor {

    fun processHeartRate(heartRate: Int): HeartRateData {
        return HeartRateData(
            value = heartRate,
            timestamp = System.currentTimeMillis(),
            zone = calculateHeartRateZone(heartRate)
        )
    }

    private fun calculateHeartRateZone(heartRate: Int): String {
        return when {
            heartRate < 120 -> "Rest"
            heartRate < 140 -> "Fat Burn"
            heartRate < 160 -> "Cardio"
            heartRate < 180 -> "Peak"
            else -> "Maximum"
        }
    }

    fun processSteps(steps: Int, previousTotal: Int): StepData {
        val newSteps = steps - previousTotal
        return StepData(
            totalSteps = steps,
            newSteps = newSteps,
            timestamp = System.currentTimeMillis()
        )
    }

    fun processDistance(distance: Double, unit: String = "km"): DistanceData {
        return DistanceData(
            value = distance,
            unit = unit,
            timestamp = System.currentTimeMillis()
        )
    }
}
```

## 🗄️ Database Schema

### Users Table

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    university VARCHAR(255) NOT NULL,
    age INT,
    weight DECIMAL(5,2),
    height VARCHAR(10),
    password VARCHAR(255) NOT NULL,
    user_role ENUM('athlete', 'coach', 'admin') DEFAULT 'athlete',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Athlete Profiles Table

```sql
CREATE TABLE athlete_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sport VARCHAR(100),
    position VARCHAR(100),
    team VARCHAR(100),
    coach_id INT,
    fitness_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    goals TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE SET NULL
);
```

### Additional Tables

- `workout_sessions` - Track workout sessions
- `biometric_data` - Store fitness metrics
- `goals` - User fitness goals
- `coach_athlete_relationships` - Coach-athlete connections

## 🔒 Security Features

- **JWT Authentication**: Secure token-based system with 24-hour expiration
- **Password Hashing**: Bcrypt with PASSWORD_DEFAULT
- **Input Validation**: Comprehensive sanitization and validation
- **SQL Injection Prevention**: Prepared statements for all database queries
- **CORS Support**: Cross-origin requests enabled
- **HTTPS Only**: All endpoints require secure connections

## 📱 Mobile App Integration Tips

### 1. Samsung Galaxy Watch 5 Pro Integration

- **Wear OS 4.0**: Target API level 33+
- **Health Connect**: Use for biometric data access
- **BLE Communication**: Implement reliable data sync
- **Battery Optimization**: Minimize watch battery drain
- **Offline Storage**: Cache data when connection is lost

### 2. Token Management

- Store JWT tokens securely in Android Keystore
- Implement automatic token refresh
- Handle token expiration gracefully
- Secure token transmission to watch

### 3. Error Handling

- Implement retry logic for network failures
- Show user-friendly error messages
- Handle offline scenarios
- Graceful degradation when watch is disconnected

### 4. Data Caching

- Cache user profile and workout data locally
- Implement offline-first approach where possible
- Sync data when connection is restored
- Optimize data transfer to watch

### 5. User Experience

- Show loading states during API calls
- Implement optimistic updates
- Provide clear feedback for all actions
- Smooth transitions between app and watch

## 🧪 Testing

### Test Server Endpoint

```
GET https://laot.great-site.net/laot-api/test_simple.php
```

### Test Form

Use the provided `test_api_form.html` for interactive testing.

### cURL Examples

#### Simple Registration

```bash
curl -X POST https://laot.great-site.net/laot-api/api/register.php \
  -H "Content-Type: application/json" \
  -d '{"username": "testuser123", "password": "TestPass123"}'
```

#### Full Registration

```bash
curl -X POST https://laot.great-site.net/laot-api/api/register.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "johndoe",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "password": "SecurePass123",
    "university": "La-ot University",
    "age": 25,
    "user_role": "athlete"
  }'
```

#### Login

```bash
curl -X POST https://laot.great-site.net/laot-api/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"username": "testuser123", "password": "TestPass123"}'
```

## 🚀 Production Considerations

### Rate Limiting

- Currently no rate limiting implemented
- Consider adding for production use
- Monitor API usage patterns

### Monitoring

- Log all API requests and responses
- Monitor error rates and response times
- Set up alerts for critical failures

### Backup

- Regular database backups
- API configuration backups
- Document all customizations

## 📞 Support

For API support and questions:

- Check the test endpoints first
- Review error responses for debugging
- Contact the development team for issues

---

**Note:** This API is designed specifically for the La-ot student-athlete goal-tracking application with Samsung Galaxy Watch 5 Pro integration. All endpoints include proper validation, security measures, and are optimized for both web dashboard and mobile app integration.
