package com.ibscontrols.shop.ui.main

import android.content.Intent
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.ibscontrols.shop.data.TokenManager
import com.ibscontrols.shop.databinding.ActivityMainBinding
import com.ibscontrols.shop.net.RetrofitClient
import com.ibscontrols.shop.ui.cart.CartActivity
import com.ibscontrols.shop.ui.login.LoginActivity
import kotlinx.coroutines.launch

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private lateinit var tokenManager: TokenManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        tokenManager = TokenManager(this)

        setSupportActionBar(binding.toolbar)

        binding.cardCart.setOnClickListener {
            startActivity(Intent(this, CartActivity::class.java))
        }

        binding.btnLogout.setOnClickListener {
            doLogout()
        }

        loadUserInfo()
        loadCartSummary()
    }

    override fun onResume() {
        super.onResume()
        loadCartSummary()
    }

    private fun loadUserInfo() {
        lifecycleScope.launch {
            val userName = tokenManager.userName.first()
            binding.tvWelcome.text = "欢迎，${userName ?: "用户"}"
        }
    }

    private fun loadCartSummary() {
        lifecycleScope.launch {
            try {
                val response = RetrofitClient.apiService.getCartSummary()
                if (response.isSuccessful) {
                    val apiResponse = response.body()
                    if (apiResponse?.status == "success") {
                        val summary = apiResponse.data
                        binding.tvCartCount.text = "${summary?.selected_count ?: 0} 件商品"
                        binding.tvCartAmount.text = "¥${String.format("%.2f", summary?.selected_amount ?: 0.0)}"
                    }
                }
            } catch (e: Exception) {
                e.printStackTrace()
            }
        }
    }

    private fun doLogout() {
        lifecycleScope.launch {
            try {
                RetrofitClient.apiService.logout()
            } catch (e: Exception) {
                // 忽略网络错误
            }

            tokenManager.clearTokens()
            Toast.makeText(this@MainActivity, "已退出登录", Toast.LENGTH_SHORT).show()
            startActivity(Intent(this@MainActivity, LoginActivity::class.java))
            finish()
        }
    }
}
