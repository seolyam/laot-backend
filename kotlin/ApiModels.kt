package com.example.myapplication.data

import com.google.gson.annotations.SerializedName

// API Response Models - This matches your working PHP API structure
data class ApiResponse<T>(
    val success: Boolean,
    val message: String,
    val data: T?,
    val timestamp: String
)

// User Models - This matches the user_data structure from your PHP API
data class User(
    val user_id: Int,
    val username: String,
    val first_name: String,  // Remove @SerializedName to match PHP API exactly
    val last_name: String,   // Remove @SerializedName to match PHP API exactly
    val email: String,
    val university: String,
    val user_role: String,   // Remove @SerializedName to match PHP API exactly
    val token: String
)

data class UserProfile(
    val sport: String?,
    val position: String?,
    val team: String?,
    val fitness_level: String  // Remove @SerializedName to match PHP API exactly
)

// Login Response - This matches your PHP login.php response structure
data class LoginResponse(
    val user_id: Int,
    val username: String,
    val first_name: String,  // Remove @SerializedName to match PHP API exactly
    val last_name: String,   // Remove @SerializedName to match PHP API exactly
    val email: String,
    val university: String,
    val user_role: String,   // Remove @SerializedName to match PHP API exactly
    val token: String,
    val profile: UserProfile?,
    val coach_data: Any?,    // Remove @SerializedName to match PHP API exactly
    val login_timestamp: String  // Remove @SerializedName to match PHP API exactly
)

// Registration Models - This matches your PHP register.php request structure
data class SimpleRegistrationRequest(
    val username: String,
    val password: String
)

data class FullRegistrationRequest(
    val username: String,
    val first_name: String,  // Remove @SerializedName to match PHP API exactly
    val last_name: String,   // Remove @SerializedName to match PHP API exactly
    val email: String,
    val password: String,
    val university: String,
    val age: Int?,
    val weight: Double?,
    val height: String?,
    val user_role: String = "athlete",  // Remove @SerializedName to match PHP API exactly
    val sport: String? = "General",
    val position: String? = "Player",
    val team: String? = "Team",
    val fitness_level: String = "beginner"  // Remove @SerializedName to match PHP API exactly
)

// Registration Response - This matches the user_data structure from your PHP API
data class RegistrationResponse(
    val user_id: Int,
    val username: String,
    val first_name: String,  // Remove @SerializedName to match PHP API exactly
    val last_name: String,   // Remove @SerializedName to match PHP API exactly
    val email: String,
    val university: String,
    val user_role: String,   // Remove @SerializedName to match PHP API exactly
    val token: String,
    val registration_mode: String  // Remove @SerializedName to match PHP API exactly
)

// Workout Models
data class WorkoutSession(
    val id: Int,
    val session_date: String,  // Match PHP API field names exactly
    val start_time: String?,   // Match PHP API field names exactly
    val end_time: String?,     // Match PHP API field names exactly
    val duration_minutes: Int?, // Match PHP API field names exactly
    val workout_type: String?, // Match PHP API field names exactly
    val notes: String?
)

// Goal Models
data class Goal(
    val id: Int,
    val goal_type: String,      // Match PHP API field names exactly
    val target_value: Double?,  // Match PHP API field names exactly
    val current_value: Double,  // Match PHP API field names exactly
    val target_date: String?,   // Match PHP API field names exactly
    val is_completed: Boolean   // Match PHP API field names exactly
)

// Auth Token Model
data class AuthToken(
    val token: String,
    val expiresAt: Long
)
