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
import com.example.myapplication.databinding.FragmentSignUpSetupBinding
import com.example.myapplication.repository.LaotRepository
import com.example.myapplication.utils.SharedPrefsManager
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.google.android.material.snackbar.Snackbar
import kotlinx.coroutines.launch

class SignUpSetupFragment : Fragment() {

    private var _binding: FragmentSignUpSetupBinding? = null
    private val binding get() = _binding!!
    
    private lateinit var repository: LaotRepository
    private lateinit var prefsManager: SharedPrefsManager
    private var registrationData: Bundle? = null

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentSignUpSetupBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        
        repository = LaotRepository()
        prefsManager = SharedPrefsManager(requireContext())
        
        // Get registration data from arguments
        registrationData = arguments
        
        // Debug: Log what data we received
        Log.d("SignUpSetup", "Arguments received: $arguments")
        
        setupClickListeners()
        setupDropdowns()
    }

    private fun setupClickListeners() {
        binding.ivBack.setOnClickListener {
            findNavController().navigateUp()
        }

        binding.btnSignUp.setOnClickListener {
            if (validateFields()) {
                completeRegistration()
            } else {
                Snackbar.make(binding.root, "Please fill all required fields", Snackbar.LENGTH_SHORT).show()
            }
        }

        binding.tvPersonalDataTab.setOnClickListener {
            findNavController().navigateUp()
        }

        binding.tvSetupTab.setOnClickListener {
            // Already on this tab
        }
    }

    private fun validateFields(): Boolean {
        val age = binding.actvAge.text.toString()
        val weight = binding.actvWeight.text.toString()
        val height = binding.actvHeight.text.toString()

        return age.isNotEmpty() && weight.isNotEmpty() && height.isNotEmpty()
    }

    private fun completeRegistration() {
        val age = binding.actvAge.text.toString().toIntOrNull()
        val weight = binding.actvWeight.text.toString().replace(" kg", "").toDoubleOrNull()
        val height = binding.actvHeight.text.toString()
        
        if (age == null || weight == null) {
            Snackbar.make(binding.root, "Please enter valid age and weight", Snackbar.LENGTH_SHORT).show()
            return
        }
        
        // Show loading state
        binding.btnSignUp.isEnabled = false
        binding.btnSignUp.text = "Completing..."
        
        lifecycleScope.launch {
            try {
                // For now, we'll just show success since the main registration was already done
                // In a real app, you might want to update the user profile with these additional details
                
                Snackbar.make(binding.root, "Setup completed successfully!", Snackbar.LENGTH_SHORT).show()
                
                // Show success dialog
                showSuccessDialog()
                
            } catch (e: Exception) {
                Snackbar.make(
                    binding.root, 
                    "Setup failed: ${e.message}", 
                    Snackbar.LENGTH_LONG
                ).show()
            } finally {
                // Restore button state
                binding.btnSignUp.isEnabled = true
                binding.btnSignUp.text = "Sign Up"
            }
        }
    }

    private fun setupDropdowns() {
        // Age options
        val ages = (16..80).map { it.toString() }.toTypedArray()
        val ageAdapter = ArrayAdapter(requireContext(), android.R.layout.simple_dropdown_item_1line, ages)
        binding.actvAge.setAdapter(ageAdapter)

        // Weight options
        val weights = (40..200).map { "$it kg" }.toTypedArray()
        val weightAdapter = ArrayAdapter(requireContext(), android.R.layout.simple_dropdown_item_1line, weights)
        binding.actvWeight.setAdapter(weightAdapter)

        // Height options
        val heights = arrayOf("5'0\"", "5'1\"", "5'2\"", "5'3\"", "5'4\"", "5'5\"", "5'6\"", "5'7\"", "5'8\"", "5'9\"", "5'10\"", "5'11\"", "6'0\"", "6'1\"", "6'2\"", "6'3\"", "6'4\"", "6'5\"", "6'6\"")
        val heightAdapter = ArrayAdapter(requireContext(), android.R.layout.simple_dropdown_item_1line, heights)
        binding.actvHeight.setAdapter(heightAdapter)
    }

    private fun showSuccessDialog() {
        MaterialAlertDialogBuilder(requireContext())
            .setTitle(getString(R.string.congratulations))
            .setMessage(getString(R.string.signup_success_message))
            .setPositiveButton("Sign-in") { _, _ ->
                // Navigate back to sign-in
                findNavController().navigate(R.id.action_signUpSetup_to_signIn)
            }
            .setCancelable(false)
            .show()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
