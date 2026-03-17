package com.ibscontrols.shop.ui.checkout

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.ibscontrols.shop.databinding.ItemCheckoutProductBinding
import com.ibscontrols.shop.model.CartItem

class CheckoutProductAdapter(
    private var items: List<CartItem>
) : RecyclerView.Adapter<CheckoutProductAdapter.ViewHolder>() {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemCheckoutProductBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return ViewHolder(binding)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        holder.bind(items[position])
    }

    override fun getItemCount() = items.size

    fun updateItems(newItems: List<CartItem>) {
        items = newItems
        notifyDataSetChanged()
    }

    inner class ViewHolder(
        private val binding: ItemCheckoutProductBinding
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(item: CartItem) {
            binding.apply {
                tvProductName.text = item.product_name
                tvAttribute.text = item.product_attribute ?: ""
                tvPrice.text = "¥${String.format("%.2f", item.unit_price)}"
                tvQuantity.text = "x${item.quantity}"
            }
        }
    }
}
