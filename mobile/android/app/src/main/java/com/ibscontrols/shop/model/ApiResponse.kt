package com.ibscontrols.shop.model

data class ApiResponse<T>(
    val status: String,
    val code: Int,
    val message: String?,
    val data: T?
)

data class LoginRequest(
    val username: String,
    val password: String,
    val device_id: String? = null,
    val device_name: String? = null
)

data class LoginResponse(
    val access_token: String,
    val refresh_token: String,
    val expires_in: Int,
    val token_type: String,
    val user: UserInfo
)

data class UserInfo(
    val id: String,
    val username: String,
    val email: String?,
    val full_name: String?
)

data class RefreshTokenRequest(
    val refresh_token: String
)

data class CartItem(
    val cart_item_id: Int,
    val product_id: Int,
    val product_name: String,
    val product_attribute: String?,
    val quantity: Int,
    val unit_price: Double,
    val current_price: Double,
    val price_changed: Boolean,
    val selected: Int,
    val subtotal: Double
)

data class CartListResponse(
    val items: List<CartItem>,
    val summary: CartSummary
)

data class CartSummary(
    val total_items: Int,
    val selected_count: Int,
    val selected_amount: Double
)

data class CartAddRequest(
    val product_id: Int,
    val attribute_id: Int = 0,
    val quantity: Int
)

data class CartUpdateRequest(
    val product_id: Int,
    val attribute_id: Int = 0,
    val quantity: Int,
    val selected: Int? = null
)

data class CartDeleteRequest(
    val product_id: Int,
    val attribute_id: Int = 0
)

data class CheckoutPreview(
    val items: List<CartItem>,
    val amount: AmountInfo
)

data class AmountInfo(
    val subtotal: Double,
    val shipping_fee: Double,
    val total: Double
)

data class CheckoutRequest(
    val address_id: Int
)
