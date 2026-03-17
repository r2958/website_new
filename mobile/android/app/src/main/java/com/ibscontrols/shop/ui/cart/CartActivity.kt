package com.ibscontrols.shop.ui.cart

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import com.ibscontrols.shop.adapter.CartAdapter
import com.ibscontrols.shop.databinding.ActivityCartBinding
import com.ibscontrols.shop.model.CartDeleteRequest
import com.ibscontrols.shop.model.CartItem
import com.ibscontrols.shop.model.CartUpdateRequest
import com.ibscontrols.shop.net.RetrofitClient
import com.ibscontrols.shop.ui.checkout.CheckoutActivity
import kotlinx.coroutines.launch

class CartActivity : AppCompatActivity(), CartAdapter.CartItemListener {

    private lateinit var binding: ActivityCartBinding
    private lateinit var adapter: CartAdapter
    private var cartItems: List<CartItem> = emptyList()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCartBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setSupportActionBar(binding.toolbar)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        binding.toolbar.setNavigationOnClickListener { finish() }

        setupRecyclerView()
        setupListeners()
        loadCart()
    }

    private fun setupRecyclerView() {
        adapter = CartAdapter(emptyList(), this)
        binding.recyclerView.layoutManager = LinearLayoutManager(this)
        binding.recyclerView.adapter = adapter
    }

    private fun setupListeners() {
        binding.swipeRefresh.setOnRefreshListener {
            loadCart()
        }

        binding.btnCheckout.setOnClickListener {
            val selectedItems = cartItems.filter { it.selected == 1 }
            if (selectedItems.isEmpty()) {
                Toast.makeText(this, "请先选择商品", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }
            startActivity(Intent(this, CheckoutActivity::class.java))
        }
    }

    private fun loadCart() {
        lifecycleScope.launch {
            try {
                binding.swipeRefresh.isRefreshing = true
                val response = RetrofitClient.apiService.getCartList()

                if (response.isSuccessful) {
                    val apiResponse = response.body()
                    if (apiResponse?.status == "success") {
                        cartItems = apiResponse.data?.items ?: emptyList()
                        adapter.updateItems(cartItems)

                        val summary = apiResponse.data?.summary
                        binding.tvTotalAmount.text = "¥${String.format("%.2f", summary?.selected_amount ?: 0.0)}"

                        binding.tvEmpty.visibility = if (cartItems.isEmpty()) {
                            View.VISIBLE
                        } else {
                            View.GONE
                        }
                    } else {
                        Toast.makeText(this@CartActivity, apiResponse?.message ?: "加载失败", Toast.LENGTH_SHORT).show()
                    }
                } else {
                    Toast.makeText(this@CartActivity, "加载失败: ${response.code()}", Toast.LENGTH_SHORT).show()
                }
            } catch (e: Exception) {
                Toast.makeText(this@CartActivity, "网络错误: ${e.message}", Toast.LENGTH_SHORT).show()
            } finally {
                binding.swipeRefresh.isRefreshing = false
            }
        }
    }

    override fun onQuantityChanged(productId: Int, attributeId: Int, quantity: Int) {
        lifecycleScope.launch {
            try {
                val request = CartUpdateRequest(
                    product_id = productId,
                    attribute_id = attributeId,
                    quantity = quantity
                )
                val response = RetrofitClient.apiService.updateCart(request)

                if (response.isSuccessful && response.body()?.status == "success") {
                    loadCart()
                } else {
                    Toast.makeText(this@CartActivity, "更新失败", Toast.LENGTH_SHORT).show()
                }
            } catch (e: Exception) {
                Toast.makeText(this@CartActivity, "网络错误", Toast.LENGTH_SHORT).show()
            }
        }
    }

    override fun onItemSelected(productId: Int, attributeId: Int, selected: Boolean) {
        lifecycleScope.launch {
            try {
                val request = CartUpdateRequest(
                    product_id = productId,
                    attribute_id = attributeId,
                    quantity = cartItems.find { it.product_id == productId }?.quantity ?: 1,
                    selected = if (selected) 1 else 0
                )
                val response = RetrofitClient.apiService.updateCart(request)

                if (response.isSuccessful && response.body()?.status == "success") {
                    loadCart()
                }
            } catch (e: Exception) {
                e.printStackTrace()
            }
        }
    }

    override fun onItemDelete(productId: Int, attributeId: Int) {
        AlertDialog.Builder(this)
            .setTitle("确认删除")
            .setMessage("确定要从购物车删除该商品吗？")
            .setPositiveButton("删除") { _, _ ->
                doDelete(productId, attributeId)
            }
            .setNegativeButton("取消", null)
            .show()
    }

    private fun doDelete(productId: Int, attributeId: Int) {
        lifecycleScope.launch {
            try {
                val request = CartDeleteRequest(
                    product_id = productId,
                    attribute_id = attributeId
                )
                val response = RetrofitClient.apiService.deleteFromCart(request)

                if (response.isSuccessful && response.body()?.status == "success") {
                    Toast.makeText(this@CartActivity, "已删除", Toast.LENGTH_SHORT).show()
                    loadCart()
                } else {
                    Toast.makeText(this@CartActivity, "删除失败", Toast.LENGTH_SHORT).show()
                }
            } catch (e: Exception) {
                Toast.makeText(this@CartActivity, "网络错误", Toast.LENGTH_SHORT).show()
            }
        }
    }
}
