package com.example.myapplication.api

import com.example.myapplication.data.*
import retrofit2.http.*

interface LaotApiService {
    
    // User Registration
    @POST("register.php")
    suspend fun registerSimple(
        @Body request: SimpleRegistrationRequest
    ): ApiResponse<RegistrationResponse>
    
    @POST("register.php")
    suspend fun registerFull(
        @Body request: FullRegistrationRequest
    ): ApiResponse<RegistrationResponse>
    
    // User Login
    @POST("login.php")
    suspend fun login(
        @Body request: SimpleRegistrationRequest
    ): ApiResponse<LoginResponse>
    
    // User Profile
    @GET("profile.php")
    suspend fun getProfile(
        @Header("Authorization") token: String
    ): ApiResponse<Map<String, Any>>
    
    // Workouts
    @GET("workouts.php")
    suspend fun getWorkouts(
        @Header("Authorization") token: String
    ): ApiResponse<List<WorkoutSession>>
    
    // Goals
    @GET("goals.php")
    suspend fun getGoals(
        @Header("Authorization") token: String
    ): ApiResponse<List<Goal>>
}
