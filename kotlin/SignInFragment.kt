package com.example.myapplication

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.navigation.fragment.findNavController
import com.example.myapplication.data.SimpleRegistrationRequest
import com.example.myapplication.databinding.FragmentSignInBinding
import com.example.myapplication.repository.LaotRepository
import com.example.myapplication.utils.SharedPrefsManager
import com.example.myapplication.utils.NetworkUtils
import com.google.android.material.snackbar.Snackbar
import kotlinx.coroutines.launch

class SignInFragment : Fragment() {

    private var _binding: FragmentSignInBinding? = null
    private val binding get() = _binding!!
    
    private lateinit var repository: LaotRepository
    private lateinit var prefsManager: SharedPrefsManager

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentSignInBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        
        repository = LaotRepository()
        prefsManager = SharedPrefsManager(requireContext())
        
        setupClickListeners()
        setupUniversityDropdown()
    }

    private fun setupClickListeners() {
        binding.btnSignIn.setOnClickListener {
            performSignIn()
        }

        binding.tvForgotPassword.setOnClickListener {
            Snackbar.make(binding.root, "Forgot password clicked", Snackbar.LENGTH_SHORT).show()
        }

        binding.tvDontHaveAccount.setOnClickListener {
            findNavController().navigate(R.id.action_signIn_to_signUpPersonal)
        }

        binding.ivGoogle.setOnClickListener {
            Snackbar.make(binding.root, "Google sign-in clicked", Snackbar.LENGTH_SHORT).show()
        }

        binding.ivApple.setOnClickListener {
            Snackbar.make(binding.root, "Apple sign-in clicked", Snackbar.LENGTH_SHORT).show()
        }
    }

    private fun performSignIn() {
        val email = binding.etEmail.text.toString()
        val password = binding.etPassword.text.toString()
        
        if (email.isBlank() || password.isBlank()) {
            Snackbar.make(binding.root, "Please fill all fields", Snackbar.LENGTH_SHORT).show()
            return
        }
        
        // Show loading state
        binding.btnSignIn.isEnabled = false
        binding.btnSignIn.text = "Signing in..."
        
        lifecycleScope.launch {
            try {
                val result = repository.login(email, password)
                
                result.fold(
                    onSuccess = { loginResponse ->
                        // Save authentication data using the correct response structure
                        prefsManager.saveAuthToken(loginResponse.token)
                        prefsManager.saveUserInfo(
                            loginResponse.user_id,
                            loginResponse.username,
                            loginResponse.user_role
                        )
                        
                        Snackbar.make(binding.root, "Sign in successful!", Snackbar.LENGTH_SHORT).show()
                        
                        // Navigate to dashboard
                        findNavController().navigate(R.id.action_signIn_to_dashboard)
                    },
                    onFailure = { exception ->
                        Snackbar.make(
                            binding.root, 
                            "Sign in failed: ${exception.message}", 
                            Snackbar.LENGTH_LONG
                        ).show()
                    }
                )
            } catch (e: Exception) {
                val errorMessage = if (!NetworkUtils.isNetworkAvailable(requireContext())) {
                    "No internet connection. Please check your network settings."
                } else {
                    "Network error: ${NetworkUtils.getErrorMessage(e)}"
                }
                
                Snackbar.make(
                    binding.root, 
                    errorMessage, 
                    Snackbar.LENGTH_LONG
                ).show()
            } finally {
                // Restore button state
                binding.btnSignIn.isEnabled = true
                binding.btnSignIn.text = "Sign-In"
            }
        }
    }

    private fun setupUniversityDropdown() {
        // University dropdown is not needed for sign-in screen
        // This method can be removed or used for other purposes
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
