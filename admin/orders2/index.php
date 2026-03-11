<?
require_once('../../application.php');
require_once('../auth.php');

$Page->PageTitle = 'Manage User Orders';

$OrderBy = isset($_GET['OrderBy']) ? $_GET['OrderBy'] : 'id DESC';
$OrderStatus = isset($_GET['OrderStatus']) ? $_GET['OrderStatus'] : '';
$PaymentStatus = isset($_GET['PaymentStatus']) ? $_GET['PaymentStatus'] : '';
$FromDate = isset($_GET['FromDate']) ? $_GET['FromDate'] : '';
$ToDate = isset($_GET['ToDate']) ? $_GET['ToDate'] : '';

// Build WHERE clause
$where = "1=1";
if ($OrderStatus != '' && $OrderStatus != 'All') {
    $where .= " AND status = '" . $DB->escape($OrderStatus) . "'";
}
if ($PaymentStatus != '' && $PaymentStatus != 'All') {
    $where .= " AND payment_status = '" . $DB->escape($PaymentStatus) . "'";
}
if ($FromDate != '' && $ToDate != '') {
    $where .= " AND (order_date >= '$FromDate' AND order_date <= '$ToDate')";
}

// Get user_orders data
$qid = new PagedResultSet("SELECT id, order_number, user_id, consignee, phone, subtotal, shipping, tax, total, payment_method, status, payment_status, order_date FROM user_orders WHERE $where ORDER BY $OrderBy", 50);

$Page->LoadJSCalendar = 'Yes';
$Admin->showAdminHeader();
?>
<form action="<? echo $_SERVER['PHP_SELF']; ?>" method="get" name="FormName">
	<table border="0" cellspacing="0" cellpadding="1">
		<tr>
			<th colspan="2">Order Date:</th>
			<th></th>
			<th>Order Status</th>
			<th></th>
			<th>Payment Status</th>
			<th></th>
			<th></th>
		</tr>
		<tr>
			<td>From:<input type="text" name="FromDate" id="sel1" size="11" value="<? echo htmlspecialchars($FromDate); ?>" onfocus="return showCalendar('sel1', 'y-mm-dd');"></td>
			<td>To:<input type="text" name="ToDate" id="sel2" size="11" value="<? echo htmlspecialchars($ToDate); ?>" onfocus="return showCalendar('sel2', 'y-mm-dd');"></td>
			<td>&nbsp;&nbsp;</td>
			<td><select name="OrderStatus" size="1">
				<option value="">All</option>
				<option value="pending" <? echo $OrderStatus == 'pending' ? 'selected' : ''; ?>>Pending</option>
				<option value="processing" <? echo $OrderStatus == 'processing' ? 'selected' : ''; ?>>Processing</option>
				<option value="shipped" <? echo $OrderStatus == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
				<option value="delivered" <? echo $OrderStatus == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
				<option value="cancelled" <? echo $OrderStatus == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
			</select></td>
			<td>&nbsp;&nbsp;</td>
			<td><select name="PaymentStatus" size="1">
				<option value="">All</option>
				<option value="unpaid" <? echo $PaymentStatus == 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
				<option value="paying" <? echo $PaymentStatus == 'paying' ? 'selected' : ''; ?>>Paying</option>
				<option value="paid" <? echo $PaymentStatus == 'paid' ? 'selected' : ''; ?>>Paid</option>
			</select></td>
			<td>&nbsp;&nbsp;</td>
			<td><input type="submit" name="submitButtonName" value="Filter"></td>
			<td><input type="reset" name="resetButtonName" value="Reset" onclick="window.open('index.php','_self');"></td>
		</tr>
	</table>
</form>

<table border="0" cellpadding="3" cellspacing="0" width="95%" class="sortable" id="orderTable">
	<tr>
		<th valign="bottom">Order ID</th>
		<th valign="bottom">Order Number</th>
		<th valign="bottom">Customer</th>
		<th valign="bottom">Phone</th>
		<th valign="bottom">Order Date</th>
		<th valign="bottom">Order Status</th>
		<th valign="bottom">Payment Status</th>
		<th valign="bottom">Subtotal</th>
		<th valign="bottom">Shipping</th>
		<th valign="bottom">Tax</th>
		<th valign="bottom">Total</th>
		<th valign="bottom">&nbsp;</th>
	</tr>
	<?
	$statusColors = array(
		'pending' => '#FFA500',
		'processing' => '#4169E1',
		'shipped' => '#9370DB',
		'delivered' => '#28A745',
		'cancelled' => '#DC3545'
	);
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
	
	while($row = $qid->fetchObject()) {
		$statusColor = isset($statusColors[$row->status]) ? $statusColors[$row->status] : '#666';
		$paymentStatus = $row->payment_status ?: 'unpaid';
		$paymentStatusColor = isset($paymentStatusColors[$paymentStatus]) ? $paymentStatusColors[$paymentStatus] : '#666';
		$paymentStatusLabel = isset($paymentStatusLabels[$paymentStatus]) ? $paymentStatusLabels[$paymentStatus] : ucfirst($paymentStatus);
	?>
	<tr>
		<td align="center"><? echo $row->id; ?></td>
		<td><? echo htmlspecialchars($row->order_number); ?></td>
		<td><? echo htmlspecialchars($row->consignee); ?></td>
		<td><? echo htmlspecialchars($row->phone); ?></td>
		<td><? echo $row->order_date; ?></td>
		<td><span style="background-color: <? echo $statusColor; ?>; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px;"><? echo strtoupper($row->status); ?></span></td>
		<td><span style="background-color: <? echo $paymentStatusColor; ?>; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px;"><? echo $paymentStatusLabel; ?></span></td>
		<td align="right">$<? echo number_format($row->subtotal, 2); ?></td>
		<td align="right">$<? echo number_format($row->shipping, 2); ?></td>
		<td align="right">$<? echo number_format($row->tax, 2); ?></td>
		<td align="right"><strong>$<? echo number_format($row->total, 2); ?></strong></td>
		<td align="center" nowrap="nowrap"><a href="edit.php?id=<? echo $row->id; ?>">View Details</a></td>
	</tr>
	<? } ?>
</table>
<p><? echo $qid->getPageNav($querystring); ?></p>
<? $Admin->showAdminFooter(); ?>
