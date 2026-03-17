package com.ibscontrols.shop.ui.splash

import android.content.Intent
import android.os.Bundle
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.ibscontrols.shop.data.TokenManager
import com.ibscontrols.shop.ui.login.LoginActivity
import com.ibscontrols.shop.ui.main.MainActivity
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch

class SplashActivity : AppCompatActivity() {
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        lifecycleScope.launch {
            delay(1500) // 显示1.5秒启动页
            
            val tokenManager = TokenManager(this@SplashActivity)
            val token = tokenManager.accessToken.first()
            
            if (token != null) {
                startActivity(Intent(this@SplashActivity, MainActivity::class.java))
            } else {
                startActivity(Intent(this@SplashActivity, LoginActivity::class.java))
            }
            finish()
        }
    }
}
