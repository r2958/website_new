package com.ibscontrols.shop.ui.login

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.ibscontrols.shop.data.TokenManager
import com.ibscontrols.shop.databinding.ActivityLoginBinding
import com.ibscontrols.shop.model.LoginRequest
import com.ibscontrols.shop.net.RetrofitClient
import com.ibscontrols.shop.ui.main.MainActivity
import kotlinx.coroutines.launch
import android.provider.Settings

class LoginActivity : AppCompatActivity() {

    private lateinit var binding: ActivityLoginBinding
    private lateinit var tokenManager: TokenManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityLoginBinding.inflate(layoutInflater)
        setContentView(binding.root)

        tokenManager = TokenManager(this)
        RetrofitClient.init(this)

        binding.btnLogin.setOnClickListener {
            doLogin()
        }
    }

    private fun doLogin() {
        val username = binding.etUsername.text.toString().trim()
        val password = binding.etPassword.text.toString().trim()

        if (username.isEmpty() || password.isEmpty()) {
            Toast.makeText(this, "请输入用户名和密码", Toast.LENGTH_SHORT).show()
            return
        }

        showLoading(true)

        lifecycleScope.launch {
            try {
                val deviceId = Settings.Secure.getString(contentResolver, Settings.Secure.ANDROID_ID)
                val request = LoginRequest(
                    username = username,
                    password = password,
                    device_id = deviceId,
                    device_name = android.os.Build.MODEL
                )

                val response = RetrofitClient.apiService.login(request)

                if (response.isSuccessful) {
                    val apiResponse = response.body()
                    if (apiResponse?.status == "success") {
                        val loginData = apiResponse.data!!

                        // 保存token
                        tokenManager.saveTokens(
                            loginData.access_token,
                            loginData.refresh_token
                        )
                        tokenManager.saveUserInfo(
                            loginData.user.id,
                            loginData.user.username
                        )

                        Toast.makeText(this@LoginActivity, "登录成功", Toast.LENGTH_SHORT).show()
                        startActivity(Intent(this@LoginActivity, MainActivity::class.java))
                        finish()
                    } else {
                        Toast.makeText(this@LoginActivity, apiResponse?.message ?: "登录失败", Toast.LENGTH_SHORT).show()
                    }
                } else {
                    Toast.makeText(this@LoginActivity, "登录失败: ${response.code()}", Toast.LENGTH_SHORT).show()
                }
            } catch (e: Exception) {
                Toast.makeText(this@LoginActivity, "网络错误: ${e.message}", Toast.LENGTH_SHORT).show()
            } finally {
                showLoading(false)
            }
        }
    }

    private fun showLoading(show: Boolean) {
        binding.progressBar.visibility = if (show) View.VISIBLE else View.GONE
        binding.btnLogin.isEnabled = !show
    }
}
