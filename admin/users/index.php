<?
require_once('../../application.php');
require_once('../auth.php');

$Page->PageTitle = 'Manage Users';

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = "1=1";
if ($search != '') {
    $where .= " AND (Username LIKE '%" . $DB->escape($search) . "%' OR Email LIKE '%" . $DB->escape($search) . "%' OR Company LIKE '%" . $DB->escape($search) . "%')";
}

$qid = new PagedResultSet("SELECT * FROM users WHERE $where ORDER BY id DESC", 50);

$Admin->showAdminHeader();
?>

<div style="margin-bottom: 15px;">
    <a href="edit.php" class="button" style="padding: 8px 20px; background: #28a745; color: white; text-decoration: none;">+ Add New User</a>
</div>

<form action="<? echo $_SERVER['PHP_SELF']; ?>" method="get" name="FormName">
	<table border="0" cellspacing="0" cellpadding="1">
		<tr>
			<th>Search:</th>
			<td><input type="text" name="search" value="<? echo htmlspecialchars($search); ?>" placeholder="Search by Username, Email, Company"></td>
			<td><input type="submit" value="Search"></td>
			<td><input type="button" value="Reset" onclick="window.open('index.php','_self');"></td>
		</tr>
	</table>
</form>

<table border="0" cellpadding="3" cellspacing="0" width="95%">
	<tr style="background: #f0f0f0;">
		<th>ID</th>
		<th>Username</th>
		<th>Company</th>
		<th>Name</th>
		<th>Email</th>
		<th>Telephone</th>
		<th>Created</th>
		<th>&nbsp;</th>
	</tr>
	<?
	while($row = $qid->fetchObject()) {
	?>
	<tr>
		<td><? echo $row->id; ?></td>
		<td><? echo htmlspecialchars($row->Username); ?></td>
		<td><? echo htmlspecialchars($row->Company); ?></td>
		<td><? echo htmlspecialchars($row->FirstName . ' ' . $row->LastName); ?></td>
		<td><? echo htmlspecialchars($row->Email); ?></td>
		<td><? echo $row->Telephone; ?></td>
		<td><? echo isset($row->CreateTime) ? $row->CreateTime : 'N/A'; ?></td>
		<td>
			<a href="edit.php?id=<? echo $row->id; ?>">Edit</a>
		</td>
	</tr>
	<? } ?>
</table>
<p><? echo $qid->getPageNav($querystring); ?></p>

<? $Admin->showAdminFooter(); ?>
