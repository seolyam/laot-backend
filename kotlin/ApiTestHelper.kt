package com.example.myapplication.utils

import android.util.Log
import com.example.myapplication.data.FullRegistrationRequest
import com.example.myapplication.repository.LaotRepository
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch

/**
 * Helper class to test API calls and debug registration issues
 * Use this to test your API endpoints independently
 */
class ApiTestHelper {
    
    companion object {
        private const val TAG = "ApiTestHelper"
        
        /**
         * Test the registration API with sample data
         */
        fun testRegistration(repository: LaotRepository) {
            CoroutineScope(Dispatchers.IO).launch {
                try {
                    Log.d(TAG, "🧪 Starting API registration test...")
                    
                    val testRequest = FullRegistrationRequest(
                        username = "testuser${System.currentTimeMillis()}",
                        first_name = "Test",
                        last_name = "User",
                        email = "test${System.currentTimeMillis()}@example.com",
                        password = "TestPass123",
                        university = "University of St. La Salle",
                        age = null,
                        weight = null,
                        height = null,
                        user_role = "athlete",
                        sport = "General",
                        position = "Player",
                        team = "Team",
                        fitness_level = "beginner"
                    )
                    
                    Log.d(TAG, "📤 Test request: $testRequest")
                    
                    val result = repository.registerFull(testRequest)
                    
                    result.fold(
                        onSuccess = { response ->
                            Log.d(TAG, "✅ Registration test SUCCESS: $response")
                        },
                        onFailure = { exception ->
                            Log.e(TAG, "❌ Registration test FAILED: ${exception.message}", exception)
                        }
                    )
                    
                } catch (e: Exception) {
                    Log.e(TAG, "💥 Registration test EXCEPTION", e)
                }
            }
        }
        
        /**
         * Test the login API with sample credentials
         */
        fun testLogin(repository: LaotRepository, username: String, password: String) {
            CoroutineScope(Dispatchers.IO).launch {
                try {
                    Log.d(TAG, "🧪 Starting API login test...")
                    
                    val result = repository.login(username, password)
                    
                    result.fold(
                        onSuccess = { response ->
                            Log.d(TAG, "✅ Login test SUCCESS: $response")
                        },
                        onFailure = { exception ->
                            Log.e(TAG, "❌ Login test FAILED: ${exception.message}", exception)
                        }
                    )
                    
                } catch (e: Exception) {
                    Log.e(TAG, "💥 Login test EXCEPTION", e)
                }
            }
        }
        
        /**
         * Log the current API configuration
         */
        fun logApiConfig() {
            Log.d(TAG, "🔧 API Configuration:")
            Log.d(TAG, "   Base URL: https://laot.great-site.net/laot-api/api/")
            Log.d(TAG, "   Registration endpoint: register.php")
            Log.d(TAG, "   Login endpoint: login.php")
            Log.d(TAG, "   Expected field names: first_name, last_name, user_role, fitness_level")
        }
    }
}
