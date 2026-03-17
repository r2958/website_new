package com.ibscontrols.shop.adapter

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.ibscontrols.shop.databinding.ItemCartBinding
import com.ibscontrols.shop.model.CartItem

class CartAdapter(
    private var items: List<CartItem>,
    private val listener: CartItemListener
) : RecyclerView.Adapter<CartAdapter.CartViewHolder>() {

    interface CartItemListener {
        fun onQuantityChanged(productId: Int, attributeId: Int, quantity: Int)
        fun onItemSelected(productId: Int, attributeId: Int, selected: Boolean)
        fun onItemDelete(productId: Int, attributeId: Int)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): CartViewHolder {
        val binding = ItemCartBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return CartViewHolder(binding)
    }

    override fun onBindViewHolder(holder: CartViewHolder, position: Int) {
        holder.bind(items[position])
    }

    override fun getItemCount() = items.size

    fun updateItems(newItems: List<CartItem>) {
        items = newItems
        notifyDataSetChanged()
    }

    inner class CartViewHolder(
        private val binding: ItemCartBinding
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(item: CartItem) {
            binding.apply {
                tvProductName.text = item.product_name
                tvAttribute.text = item.product_attribute ?: ""
                tvPrice.text = "¥${String.format("%.2f", item.unit_price)}"
                tvQuantity.text = item.quantity.toString()
                cbSelect.isChecked = item.selected == 1

                tvPriceChanged.visibility = if (item.price_changed) {
                    android.view.View.VISIBLE
                } else {
                    android.view.View.GONE
                }

                cbSelect.setOnCheckedChangeListener { _, isChecked ->
                    listener.onItemSelected(item.product_id, 0, isChecked)
                }

                btnMinus.setOnClickListener {
                    if (item.quantity > 1) {
                        listener.onQuantityChanged(item.product_id, 0, item.quantity - 1)
                    }
                }

                btnPlus.setOnClickListener {
                    listener.onQuantityChanged(item.product_id, 0, item.quantity + 1)
                }

                btnDelete.setOnClickListener {
                    listener.onItemDelete(item.product_id, 0)
                }
            }
        }
    }
}
