package com.example.myapplication

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.navigation.fragment.findNavController
import com.example.myapplication.databinding.FragmentDashboardBinding
import com.example.myapplication.utils.SharedPrefsManager

class DashboardFragment : Fragment() {

    private var _binding: FragmentDashboardBinding? = null
    private val binding get() = _binding!!
    
    private lateinit var prefsManager: SharedPrefsManager

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentDashboardBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        
        prefsManager = SharedPrefsManager(requireContext())
        
        setupUserInfo()
        setupClickListeners()
    }

    private fun setupUserInfo() {
        val username = prefsManager.getUsername() ?: "Unknown"
        val userRole = prefsManager.getUserRole() ?: "Unknown"
        
        binding.tvUsername.text = "Username: $username"
        binding.tvUserRole.text = "Role: $userRole"
    }

    private fun setupClickListeners() {
        binding.btnLogout.setOnClickListener {
            logout()
        }
    }

    private fun logout() {
        // Clear authentication data
        prefsManager.clearAuthData()
        
        // Navigate back to sign-in
        findNavController().navigate(R.id.action_dashboard_to_signIn)
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
