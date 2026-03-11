<?
require_once('../../application.php');
require_once('../auth.php');

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($orderId == 0) {
    header('Location: index.php');
    exit;
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'];
    $DB->query("UPDATE user_orders SET status = '" . $DB->escape($newStatus) . "' WHERE id = '$orderId'");
    $message = "Order status updated successfully!";
}

// Handle payment status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_payment_status'])) {
    $newPaymentStatus = $_POST['payment_status'];
    $DB->query("UPDATE user_orders SET payment_status = '" . $DB->escape($newPaymentStatus) . "' WHERE id = '$orderId'");
    $message = "Payment status updated successfully!";
    // Refresh order data
    $orderQid = $DB->query("SELECT * FROM user_orders WHERE id = '$orderId'");
    $order = $DB->fetchObject($orderQid);
}

// Get order details
$orderQid = $DB->query("SELECT * FROM user_orders WHERE id = '$orderId'");
$order = $DB->fetchObject($orderQid);

if (!$order) {
    header('Location: index.php');
    exit;
}

// Get order items
$itemsQid = $DB->query("SELECT * FROM user_order_items WHERE order_id = '$orderId'");

$Page->PageTitle = 'Order #' . htmlspecialchars($order->order_number);
$Admin->showAdminHeader();
?>

<? if (isset($message)): ?>
<div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
    <? echo $message; ?>
</div>
<? endif; ?>

<table border="0" cellpadding="5" cellspacing="0" width="95%">
    <tr>
        <td valign="top">
            <h3>Order Information</h3>
            <table border="0" cellpadding="5" cellspacing="0" width="100%" style="background: #f9f9f9;">
                <tr>
                    <th align="left" width="150">Order ID:</th>
                    <td><? echo $order->id; ?></td>
                </tr>
                <tr>
                    <th align="left">Order Number:</th>
                    <td><? echo htmlspecialchars($order->order_number); ?></td>
                </tr>
                <tr>
                    <th align="left">Order Date:</th>
                    <td><? echo $order->order_date; ?></td>
                </tr>
                <tr>
                    <th align="left">User ID:</th>
                    <td><? echo $order->user_id; ?></td>
                </tr>
            </table>
            
            <h3>Shipping Address</h3>
            <table border="0" cellpadding="5" cellspacing="0" width="100%" style="background: #f9f9f9;">
                <tr>
                    <th align="left" width="150">Consignee:</th>
                    <td><? echo htmlspecialchars($order->consignee); ?></td>
                </tr>
                <tr>
                    <th align="left">Phone:</th>
                    <td><? echo htmlspecialchars($order->phone); ?></td>
                </tr>
                <tr>
                    <th align="left">Address:</th>
                    <td>
                        <? echo htmlspecialchars($order->province); ?>
                        <? echo htmlspecialchars($order->city); ?>
                        <? echo htmlspecialchars($order->district); ?>
                        <? echo htmlspecialchars($order->address); ?>
                        <? if ($order->postcode): ?>
                            (<? echo htmlspecialchars($order->postcode); ?>)
                        <? endif; ?>
                    </td>
                </tr>
                <? if ($order->country): ?>
                <tr>
                    <th align="left">Country:</th>
                    <td><? echo htmlspecialchars($order->country); ?></td>
                </tr>
                <? endif; ?>
            </table>
        </td>
        
        <td valign="top">
            <h3>Order Status</h3>
            <form method="post" action="">
                <table border="0" cellpadding="5" cellspacing="0" width="100%" style="background: #f9f9f9;">
                    <tr>
                        <th align="left" width="150">Current Status:</th>
                        <td>
                            <?
                            $statusColors = array(
                                'pending' => '#FFA500',
                                'processing' => '#4169E1',
                                'shipped' => '#9370DB',
                                'delivered' => '#28A745',
                                'cancelled' => '#DC3545'
                            );
                            $statusColor = isset($statusColors[$order->status]) ? $statusColors[$order->status] : '#666';
                            ?>
                            <span style="background-color: <? echo $statusColor; ?>; color: white; padding: 3px 10px; border-radius: 3px;">
                                <? echo strtoupper($order->status); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th align="left">Update Status:</th>
                        <td>
                            <select name="status" style="padding: 5px;">
                                <option value="pending" <? echo $order->status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <? echo $order->status == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <? echo $order->status == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <? echo $order->status == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <? echo $order->status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <input type="submit" name="update_status" value="Update" class="button" style="padding: 5px 15px; margin-left: 10px;">
                        </td>
                    </tr>
                </table>
            </form>
            
            <h3>Payment Information</h3>
            <form method="post" action="">
                <table border="0" cellpadding="5" cellspacing="0" width="100%" style="background: #f9f9f9;">
                    <tr>
                        <th align="left" width="150">Payment Method:</th>
                        <td><? echo htmlspecialchars($order->payment_method ?: 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <th align="left">Current Payment Status:</th>
                        <td>
                            <?
                            $paymentStatusColors = array(
                                'unpaid' => '#DC3545',
                                'paying' => '#FFC107',
                                'paid' => '#28A745'
                            );
                            $paymentStatusLabels = array(
                                'unpaid' => 'Unpaid',
                                'paying' => 'Paying',
                                'paid' => 'Paid'
                            );
                            $paymentStatus = $order->payment_status ?: 'unpaid';
                            $paymentStatusColor = isset($paymentStatusColors[$paymentStatus]) ? $paymentStatusColors[$paymentStatus] : '#666';
                            $paymentStatusLabel = isset($paymentStatusLabels[$paymentStatus]) ? $paymentStatusLabels[$paymentStatus] : ucfirst($paymentStatus);
                            ?>
                            <span style="background-color: <? echo $paymentStatusColor; ?>; color: white; padding: 3px 10px; border-radius: 3px;">
                                <? echo $paymentStatusLabel; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th align="left">Update Payment Status:</th>
                        <td>
                            <select name="payment_status" style="padding: 5px;">
                                <option value="unpaid" <? echo $paymentStatus == 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                                <option value="paying" <? echo $paymentStatus == 'paying' ? 'selected' : ''; ?>>Paying</option>
                                <option value="paid" <? echo $paymentStatus == 'paid' ? 'selected' : ''; ?>>Paid</option>
                            </select>
                            <input type="submit" name="update_payment_status" value="Update" class="button" style="padding: 5px 15px; margin-left: 10px;">
                        </td>
                    </tr>
                    <tr>
                        <th align="left">Subtotal:</th>
                        <td>$<? echo number_format($order->subtotal, 2); ?></td>
                    </tr>
                    <tr>
                        <th align="left">Shipping:</th>
                        <td>$<? echo number_format($order->shipping, 2); ?></td>
                    </tr>
                    <tr>
                        <th align="left">Tax:</th>
                        <td>$<? echo number_format($order->tax, 2); ?></td>
                    </tr>
                    <tr>
                        <th align="left">Total:</th>
                        <td><strong>$<? echo number_format($order->total, 2); ?></strong></td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>

<h3>Order Items</h3>
<table border="0" cellpadding="5" cellspacing="0" width="95%">
    <tr style="background: #f0f0f0;">
        <th align="left">Product ID</th>
        <th align="left">Product Name</th>
        <th align="center">Attribute</th>
        <th align="center">Quantity</th>
        <th align="right">Price</th>
        <th align="right">Subtotal</th>
    </tr>
    <?
    $itemCount = 0;
    while($item = $DB->fetchObject($itemsQid)) {
        $itemCount++;
        $itemSubtotal = $item->price * $item->quantity;
    ?>
    <tr>
        <td><? echo $item->product_id; ?></td>
        <td>
            <a href="../products/attributes.php?ProductID=<? echo $item->product_id; ?>" style="color: #0066cc; text-decoration: underline;" title="Click to manage product attributes">
                <? echo htmlspecialchars($item->product_name); ?>
            </a>
            <? if ($item->product_image): ?>
                <br><img src="<? echo $item->product_image; ?>" style="max-width: 50px; max-height: 50px; margin-top: 5px;">
            <? endif; ?>
        </td>
        <td align="center">
            <? if ($item->attribute_name): ?>
                <span style="background-color: #e9ecef; padding: 3px 8px; border-radius: 3px; font-size: 12px;">
                    <? echo htmlspecialchars($item->attribute_name); ?>
                </span>
            <? else: ?>
                <span style="color: #999; font-size: 12px;">-</span>
            <? endif; ?>
        </td>
        <td align="center"><? echo $item->quantity; ?></td>
        <td align="right">$<? echo number_format($item->price, 2); ?></td>
        <td align="right">$<? echo number_format($itemSubtotal, 2); ?></td>
    </tr>
    <? } ?>
    <? if ($itemCount == 0): ?>
    <tr>
        <td colspan="6" align="center" style="padding: 20px; color: #999;">No items found</td>
    </tr>
    <? endif; ?>
</table>

<div style="margin-top: 20px;">
    <a href="index.php" class="button" style="padding: 8px 20px; background: #666; color: white; text-decoration: none;">&laquo; Back to Orders</a>
</div>

<? $Admin->showAdminFooter(); ?>
