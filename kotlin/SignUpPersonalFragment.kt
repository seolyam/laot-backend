package com.example.myapplication

import android.os.Bundle
import android.util.Log
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.navigation.fragment.findNavController
import com.example.myapplication.data.FullRegistrationRequest
import com.example.myapplication.databinding.FragmentSignUpPersonalBinding
import com.example.myapplication.repository.LaotRepository
import com.example.myapplication.utils.SharedPrefsManager
import com.example.myapplication.utils.ApiTestHelper
import com.google.android.material.snackbar.Snackbar
import kotlinx.coroutines.launch

class SignUpPersonalFragment : Fragment() {

    private var _binding: FragmentSignUpPersonalBinding? = null
    private val binding get() = _binding!!
    
    private lateinit var repository: LaotRepository
    private lateinit var prefsManager: SharedPrefsManager

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentSignUpPersonalBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        
        repository = LaotRepository()
        prefsManager = SharedPrefsManager(requireContext())
        
        setupClickListeners()
        setupUniversityDropdown()
        
        // Add debug button for testing API (remove in production)
        binding.root.findViewById<android.widget.Button>(android.R.id.button1)?.setOnClickListener {
            ApiTestHelper.testRegistration(repository)
            Snackbar.make(binding.root, "API test started - check logs", Snackbar.LENGTH_SHORT).show()
        }
    }

    private fun setupClickListeners() {
        binding.ivBack.setOnClickListener {
            findNavController().navigateUp()
        }

        binding.btnNext.setOnClickListener {
            if (validateFields()) {
                performRegistration()
            } else {
                Snackbar.make(binding.root, "Please fill all required fields", Snackbar.LENGTH_SHORT).show()
            }
        }

        binding.tvPersonalDataTab.setOnClickListener {
            // Already on this tab
        }

        binding.tvSetupTab.setOnClickListener {
            findNavController().navigate(R.id.action_signUpPersonal_to_signUpSetup)
        }
    }

    private fun validateFields(): Boolean {
        val username = binding.etUsername.text.toString()
        val firstName = binding.etFirstName.text.toString()
        val lastName = binding.etLastName.text.toString()
        val email = binding.etEmail.text.toString()
        val password = binding.etPassword.text.toString()
        val confirmPassword = binding.etConfirmPassword.text.toString()

        // Check if fields are empty
        if (username.isBlank() || firstName.isBlank() || lastName.isBlank() || 
            email.isBlank() || password.isBlank() || confirmPassword.isBlank()) {
            Snackbar.make(binding.root, "Please fill all required fields", Snackbar.LENGTH_SHORT).show()
            return false
        }

        // Check if passwords match
        if (password != confirmPassword) {
            Snackbar.make(binding.root, "Passwords do not match", Snackbar.LENGTH_SHORT).show()
            return false
        }

        // Validate password strength (must be at least 8 characters with uppercase, lowercase, and number)
        if (!isPasswordStrong(password)) {
            Snackbar.make(
                binding.root, 
                "Password must be at least 8 characters with uppercase, lowercase, and number", 
                Snackbar.LENGTH_LONG
            ).show()
            return false
        }

        return true
    }

    private fun isPasswordStrong(password: String): Boolean {
        // At least 8 characters
        if (password.length < 8) return false
        
        // Must contain uppercase letter
        if (!password.any { it.isUpperCase() }) return false
        
        // Must contain lowercase letter
        if (!password.any { it.isLowerCase() }) return false
        
        // Must contain number
        if (!password.any { it.isDigit() }) return false
        
        return true
    }

    private fun performRegistration() {
        val username = binding.etUsername.text.toString()
        val firstName = binding.etFirstName.text.toString()
        val lastName = binding.etLastName.text.toString()
        val email = binding.etEmail.text.toString()
        val password = binding.etPassword.text.toString()
        val university = binding.actvUniversity.text.toString().ifEmpty { "University of St. La Salle" }
        
        // Log the registration data
        Log.d("SignUpPersonal", "Registration data: username=$username, firstName=$firstName, lastName=$lastName, email=$email, university=$university")
        
        // Show loading state
        binding.btnNext.isEnabled = false
        binding.btnNext.text = "Processing..."
        
        lifecycleScope.launch {
            try {
                val request = FullRegistrationRequest(
                    username = username,
                    first_name = firstName,  // Changed to match PHP API expectation
                    last_name = lastName,    // Changed to match PHP API expectation
                    email = email,
                    password = password,
                    university = university,
                    age = null,
                    weight = null,
                    height = null,
                    user_role = "athlete",   // Changed to match PHP API expectation
                    // Add required athlete profile fields for PHP API
                    sport = "General",
                    position = "Player", 
                    team = "Team",
                    fitness_level = "beginner"
                )
                
                Log.d("SignUpPersonal", "Making API call with request: $request")
                
                val result = repository.registerFull(request)
                
                result.fold(
                    onSuccess = { registrationResponse ->
                        Log.d("SignUpPersonal", "Registration successful: $registrationResponse")
                        
                        // Save authentication data
                        prefsManager.saveAuthToken(registrationResponse.token)
                        prefsManager.saveUserInfo(
                            registrationResponse.user_id,
                            registrationResponse.username,
                            registrationResponse.user_role
                        )
                        
                        Snackbar.make(binding.root, "Registration successful!", Snackbar.LENGTH_SHORT).show()
                        
                        // Navigate to setup screen
                        findNavController().navigate(R.id.action_signUpPersonal_to_signUpSetup)
                    },
                    onFailure = { exception ->
                        Log.e("SignUpPersonal", "Registration failed: ${exception.message}", exception)
                        Snackbar.make(
                            binding.root, 
                            "Registration failed: ${exception.message}", 
                            Snackbar.LENGTH_LONG
                        ).show()
                    }
                )
            } catch (e: Exception) {
                Log.e("SignUpPersonal", "Network error during registration", e)
                Snackbar.make(
                    binding.root, 
                    "Network error: ${e.message}", 
                    Snackbar.LENGTH_LONG
                ).show()
            } finally {
                // Restore button state
                binding.btnNext.isEnabled = true
                binding.btnNext.text = "Next"
            }
        }
    }

    private fun setupUniversityDropdown() {
        val universities = arrayOf("University of St. La Salle", "La-ot University", "Other University")
        val adapter = ArrayAdapter(requireContext(), android.R.layout.simple_dropdown_item_1line, universities)
        binding.actvUniversity.setAdapter(adapter)
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}