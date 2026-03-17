package com.ibscontrols.shop.net

import com.ibscontrols.shop.model.*
import retrofit2.Response
import retrofit2.http.*

interface ApiService {
    
    // ==================== 认证 ====================
    
    @POST("mobile/api.php?action=login")
    suspend fun login(@Body request: LoginRequest): Response<ApiResponse<LoginResponse>>
    
    @POST("mobile/api.php?action=refreshToken")
    suspend fun refreshToken(@Body request: RefreshTokenRequest): Response<ApiResponse<LoginResponse>>
    
    @POST("mobile/api.php?action=logout")
    suspend fun logout(): Response<ApiResponse<Unit>>
    
    // ==================== 购物车 ====================
    
    @GET("mobile/api.php?action=cartList")
    suspend fun getCartList(): Response<ApiResponse<CartListResponse>>
    
    @GET("mobile/api.php?action=cartSummary")
    suspend fun getCartSummary(): Response<ApiResponse<CartSummary>>
    
    @POST("mobile/api.php?action=cartAdd")
    suspend fun addToCart(@Body request: CartAddRequest): Response<ApiResponse<Map<String, Any>>>
    
    @POST("mobile/api.php?action=cartUpdate")
    suspend fun updateCart(@Body request: CartUpdateRequest): Response<ApiResponse<Map<String, Any>>>
    
    @POST("mobile/api.php?action=cartDelete")
    suspend fun deleteFromCart(@Body request: CartDeleteRequest): Response<ApiResponse<Map<String, Any>>>
    
    @POST("mobile/api.php?action=cartCheckoutPreview")
    suspend fun checkoutPreview(): Response<ApiResponse<CheckoutPreview>>
    
    @POST("mobile/api.php?action=cartCheckout")
    suspend fun checkout(@Body request: CheckoutRequest): Response<ApiResponse<Map<String, Any>>>
}
