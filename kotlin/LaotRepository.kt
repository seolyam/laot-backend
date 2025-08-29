package com.example.myapplication.repository

import android.util.Log
import com.example.myapplication.api.ApiClient
import com.example.myapplication.data.*
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext

class LaotRepository {
    private val apiService = ApiClient.apiService
    
    suspend fun login(username: String, password: String): Result<LoginResponse> {
        return withContext(Dispatchers.IO) {
            try {
                val request = SimpleRegistrationRequest(username, password)
                val response = apiService.login(request)
                
                if (response.success && response.data != null) {
                    Result.success(response.data)
                } else {
                    Result.failure(Exception(response.message))
                }
            } catch (e: Exception) {
                Result.failure(e)
            }
        }
    }
    
    suspend fun registerSimple(username: String, password: String): Result<RegistrationResponse> {
        return withContext(Dispatchers.IO) {
            try {
                val request = SimpleRegistrationRequest(username, password)
                val response = apiService.registerSimple(request)
                
                if (response.success && response.data != null) {
                    Result.success(response.data)
                } else {
                    Result.failure(Exception(response.message))
                }
            } catch (e: Exception) {
                Result.failure(e)
            }
        }
    }
    
    suspend fun registerFull(request: FullRegistrationRequest): Result<RegistrationResponse> {
        return withContext(Dispatchers.IO) {
            try {
                Log.d("LaotRepository", "Full registration request: $request")
                
                // Log the individual fields being sent
                Log.d("LaotRepository", "Username: ${request.username}")
                Log.d("LaotRepository", "First Name: ${request.first_name}")
                Log.d("LaotRepository", "Last Name: ${request.last_name}")
                Log.d("LaotRepository", "Email: ${request.email}")
                Log.d("LaotRepository", "University: ${request.university}")
                Log.d("LaotRepository", "User Role: ${request.user_role}")
                Log.d("LaotRepository", "Password length: ${request.password.length}")
                Log.d("LaotRepository", "Sport: ${request.sport}")
                Log.d("LaotRepository", "Position: ${request.position}")
                Log.d("LaotRepository", "Team: ${request.team}")
                Log.d("LaotRepository", "Fitness Level: ${request.fitness_level}")
                
                val response = apiService.registerFull(request)
                Log.d("LaotRepository", "Full registration response: $response")
                Log.d("LaotRepository", "Response success: ${response.success}")
                Log.d("LaotRepository", "Response message: ${response.message}")
                Log.d("LaotRepository", "Response data: ${response.data}")
                
                if (response.success && response.data != null) {
                    Result.success(response.data)
                } else {
                    Log.e("LaotRepository", "Full registration failed: ${response.message}")
                    Result.failure(Exception(response.message))
                }
            } catch (e: Exception) {
                Log.e("LaotRepository", "Full registration exception", e)
                
                // Check if it's a JSON parsing error (HTML response)
                if (e.message?.contains("MalformedJsonException") == true || 
                    e.message?.contains("malformed JSON") == true) {
                    Log.e("LaotRepository", "Server returned HTML instead of JSON - possible hosting provider interference")
                    Result.failure(Exception("Server returned invalid response format. This may be due to hosting provider restrictions. Please contact your server administrator."))
                } else {
                    Result.failure(e)
                }
            }
        }
    }
    
    suspend fun getProfile(token: String): Result<Map<String, Any>> {
        return withContext(Dispatchers.IO) {
            try {
                val response = apiService.getProfile("Bearer $token")
                
                if (response.success && response.data != null) {
                    Result.success(response.data)
                } else {
                    Result.failure(Exception(response.message))
                }
            } catch (e: Exception) {
                Result.failure(e)
            }
        }
    }
    
    suspend fun getWorkouts(token: String): Result<List<WorkoutSession>> {
        return withContext(Dispatchers.IO) {
            try {
                val response = apiService.getWorkouts("Bearer $token")
                
                if (response.success && response.data != null) {
                    Result.success(response.data)
                } else {
                    Result.failure(Exception(response.message))
                }
            } catch (e: Exception) {
                Result.failure(e)
            }
        }
    }
    
    suspend fun getGoals(token: String): Result<List<Goal>> {
        return withContext(Dispatchers.IO) {
            try {
                val response = apiService.getGoals("Bearer $token")
                
                if (response.success && response.data != null) {
                    Result.success(response.data)
                } else {
                    Result.failure(Exception(response.message))
                }
            } catch (e: Exception) {
                Result.failure(e)
            }
        }
    }
}
