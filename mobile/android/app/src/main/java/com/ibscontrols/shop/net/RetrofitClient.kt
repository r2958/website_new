package com.ibscontrols.shop.net

import android.content.Context
import com.ibscontrols.shop.data.TokenManager
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.runBlocking
import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory

object RetrofitClient {
    
    // 修改为你的服务器地址
    private const val BASE_URL = "http://your-domain.com/"
    
    private lateinit var tokenManager: TokenManager
    
    fun init(context: Context) {
        tokenManager = TokenManager(context)
    }
    
    private fun createAuthInterceptor(): Interceptor {
        return Interceptor { chain ->
            val request = chain.request()
            
            // 获取token
            val token = runBlocking {
                tokenManager.accessToken.first()
            }
            
            val newRequest = if (token != null) {
                request.newBuilder()
                    .header("Authorization", "Bearer $token")
                    .build()
            } else {
                request
            }
            
            chain.proceed(newRequest)
        }
    }
    
    private val loggingInterceptor = HttpLoggingInterceptor().apply {
        level = HttpLoggingInterceptor.Level.BODY
    }
    
    private val client by lazy {
        OkHttpClient.Builder()
            .addInterceptor(createAuthInterceptor())
            .addInterceptor(loggingInterceptor)
            .build()
    }
    
    val apiService: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .client(client)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }
}
