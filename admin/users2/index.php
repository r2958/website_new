<?
require_once('../../application.php');
require_once('../auth.php');

$Page->PageTitle = 'Manage Users2';

// Initialize user2 table if not exists
$DB->query("CREATE TABLE IF NOT EXISTS user2 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE COMMENT '用户名',
    password VARCHAR(128) NOT NULL COMMENT '密码(MD5)',
    phone VARCHAR(20) COMMENT '手机号',
    email VARCHAR(128) COMMENT '邮箱',
    status TINYINT(1) DEFAULT 1 COMMENT '状态: 1正常 0禁用',
    password_hint VARCHAR(128) COMMENT '密码提示词',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_phone (phone),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = "1=1";
if ($search != '') {
    $where .= " AND (username LIKE '%" . $DB->escape($search) . "%' OR email LIKE '%" . $DB->escape($search) . "%' OR phone LIKE '%" . $DB->escape($search) . "%')";
}

$qid = new PagedResultSet("SELECT * FROM user2 WHERE $where ORDER BY id DESC", 50);

$Admin->showAdminHeader();
?>

<div style="margin-bottom: 15px;">
    <a href="edit.php" class="button" style="padding: 8px 20px; background: #28a745; color: white; text-decoration: none;">+ Add New User</a>
</div>

<form action="<? echo $_SERVER['PHP_SELF']; ?>" method="get" name="FormName">
	<table border="0" cellspacing="0" cellpadding="1">
		<tr>
			<th>Search:</th>
			<td><input type="text" name="search" value="<? echo htmlspecialchars($search); ?>" placeholder="Search by Username, Email, Phone"></td>
			<td><input type="submit" value="Search"></td>
			<td><input type="button" value="Reset" onclick="window.open('index.php','_self');"></td>
		</tr>
	</table>
</form>

<table border="0" cellpadding="3" cellspacing="0" width="95%">
	<tr style="background: #f0f0f0;">
		<th>ID</th>
		<th>Username</th>
		<th>Phone</th>
		<th>Email</th>
		<th>Status</th>
		<th>Created</th>
		<th>&nbsp;</th>
	</tr>
	<?
	while($row = $qid->fetchObject()) {
        $statusColor = $row->status ? '#28a745' : '#dc3545';
        $statusText = $row->status ? 'Active' : 'Disabled';
	?>
	<tr>
		<td><? echo $row->id; ?></td>
		<td><? echo htmlspecialchars($row->username); ?></td>
		<td><? echo htmlspecialchars($row->phone); ?></td>
		<td><? echo htmlspecialchars($row->email); ?></td>
		<td><span style="background-color: <? echo $statusColor; ?>; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px;"><? echo $statusText; ?></span></td>
		<td><? echo $row->created_at; ?></td>
		<td>
			<a href="edit.php?id=<? echo $row->id; ?>">Edit</a>
		</td>
	</tr>
	<? } ?>
</table>
<p><? echo $qid->getPageNav($querystring); ?></p>

<? $Admin->showAdminFooter(); ?>
