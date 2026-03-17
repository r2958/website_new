package com.ibscontrols.shop.ui.checkout

import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import com.ibscontrols.shop.databinding.ActivityCheckoutBinding
import com.ibscontrols.shop.model.CartItem
import com.ibscontrols.shop.net.RetrofitClient
import kotlinx.coroutines.launch

class CheckoutActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCheckoutBinding
    private lateinit var adapter: CheckoutProductAdapter
    private var checkoutItems: List<CartItem> = emptyList()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCheckoutBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setSupportActionBar(binding.toolbar)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        binding.toolbar.setNavigationOnClickListener { finish() }

        setupRecyclerView()
        setupListeners()
        loadCheckoutPreview()
    }

    private fun setupRecyclerView() {
        adapter = CheckoutProductAdapter(emptyList())
        binding.recyclerView.layoutManager = LinearLayoutManager(this)
        binding.recyclerView.adapter = adapter
    }

    private fun setupListeners() {
        binding.btnConfirm.setOnClickListener {
            submitOrder()
        }
    }

    private fun loadCheckoutPreview() {
        lifecycleScope.launch {
            try {
                val response = RetrofitClient.apiService.checkoutPreview()

                if (response.isSuccessful) {
                    val apiResponse = response.body()
                    if (apiResponse?.status == "success") {
                        val preview = apiResponse.data
                        checkoutItems = preview?.items ?: emptyList()
                        adapter.updateItems(checkoutItems)

                        binding.tvSubtotal.text = "¥${String.format("%.2f", preview?.amount?.subtotal ?: 0.0)}"
                        binding.tvShipping.text = "¥${String.format("%.2f", preview?.amount?.shipping_fee ?: 0.0)}"
                        binding.tvTotal.text = "¥${String.format("%.2f", preview?.amount?.total ?: 0.0)}"
                    } else {
                        Toast.makeText(this@CheckoutActivity, apiResponse?.message ?: "加载失败", Toast.LENGTH_SHORT).show()
                    }
                } else {
                    Toast.makeText(this@CheckoutActivity, "加载失败: ${response.code()}", Toast.LENGTH_SHORT).show()
                }
            } catch (e: Exception) {
                Toast.makeText(this@CheckoutActivity, "网络错误: ${e.message}", Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun submitOrder() {
        AlertDialog.Builder(this)
            .setTitle("确认提交")
            .setMessage("确定要提交订单吗？")
            .setPositiveButton("确认") { _, _ ->
                doSubmit()
            }
            .setNegativeButton("取消", null)
            .show()
    }

    private fun doSubmit() {
        lifecycleScope.launch {
            try {
                binding.btnConfirm.isEnabled = false
                binding.btnConfirm.text = "提交中..."

                // 这里需要传入 address_id，暂时使用 1
                val request = com.ibscontrols.shop.model.CheckoutRequest(address_id = 1)
                val response = RetrofitClient.apiService.checkout(request)

                if (response.isSuccessful) {
                    val apiResponse = response.body()
                    if (apiResponse?.status == "success") {
                        Toast.makeText(this@CheckoutActivity, "订单提交成功", Toast.LENGTH_LONG).show()
                        finish()
                    } else {
                        Toast.makeText(this@CheckoutActivity, apiResponse?.message ?: "提交失败", Toast.LENGTH_SHORT).show()
                    }
                } else {
                    Toast.makeText(this@CheckoutActivity, "提交失败: ${response.code()}", Toast.LENGTH_SHORT).show()
                }
            } catch (e: Exception) {
                Toast.makeText(this@CheckoutActivity, "网络错误: ${e.message}", Toast.LENGTH_SHORT).show()
            } finally {
                binding.btnConfirm.isEnabled = true
                binding.btnConfirm.text = "提交订单"
            }
        }
    }
}
