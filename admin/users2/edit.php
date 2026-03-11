<?
require_once('../../application.php');
require_once('../auth.php');

$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isNewUser = ($userId == 0);

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_user'])) {
        $username = $DB->escape($_POST['username']);
        $phone = $DB->escape($_POST['phone']);
        $email = $DB->escape($_POST['email']);
        $status = isset($_POST['status']) ? 1 : 0;
        $password_hint = $DB->escape($_POST['password_hint']);
        $newPassword = $_POST['new_password'];

        // Validation
        if (empty($username)) {
            $message = '<span style="color:red;">Error: Username is required</span>';
        } else {
            // Check if username already exists
            $checkQid = $DB->query("SELECT id FROM user2 WHERE username = '$username'" . ($isNewUser ? "" : " AND id != '$userId'"));
            if ($DB->numRows($checkQid) > 0) {
                $message = '<span style="color:red;">Error: Username already exists</span>';
            } else {
                if ($isNewUser) {
                    // Create new user - password required
                    if (empty($newPassword)) {
                        $message = '<span style="color:red;">Error: Password is required for new user</span>';
                    } else {
                        $hashedPassword = md5($newPassword);
                        $DB->query("INSERT INTO user2 (username, password, phone, email, status, password_hint) 
                                    VALUES ('$username', '$hashedPassword', '$phone', '$email', '$status', '$password_hint')");
                        $newUserId = $DB->insertID();
                        $message = '<span style="color:green;">User created successfully!</span>';
                        header('Location: edit.php?id=' . $newUserId);
                        exit;
                    }
                } else {
                    // Update existing user
                    if (!empty($newPassword)) {
                        $hashedPassword = md5($newPassword);
                        $DB->query("UPDATE user2 SET password='$hashedPassword' WHERE id='$userId'");
                    }
                    $DB->query("UPDATE user2 SET username='$username', phone='$phone', email='$email', status='$status', password_hint='$password_hint' WHERE id='$userId'");
                    $message = '<span style="color:green;">User updated successfully!</span>';
                }
            }
        }

    } elseif (isset($_POST['toggle_status']) && !$isNewUser) {
        // Toggle user status
        $DB->query("UPDATE user2 SET status = NOT status WHERE id = '$userId'");
        $message = '<span style="color:green;">Status updated!</span>';
    } elseif (isset($_POST['reset_password']) && !$isNewUser) {
        // Reset password
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if (empty($newPassword)) {
            $message = '<span style="color:red;">Error: Password cannot be empty</span>';
        } elseif ($newPassword !== $confirmPassword) {
            $message = '<span style="color:red;">Error: Passwords do not match</span>';
        } elseif (strlen($newPassword) < 4) {
            $message = '<span style="color:red;">Error: Password must be at least 4 characters</span>';
        } else {
            $hashedPassword = md5($newPassword);
            $DB->query("UPDATE user2 SET password='$hashedPassword' WHERE id='$userId'");
            $message = '<span style="color:green;">Password reset successfully!</span>';
        }
    } elseif (isset($_POST['delete_user']) && !$isNewUser) {
        // Delete user
        if (confirm('Are you sure you want to delete this user?')) {
            $DB->query("DELETE FROM user2 WHERE id = '$userId'");
            header('Location: index.php');
            exit;
        }
    }
}

$user = null;
if (!$isNewUser) {
    $userQid = $DB->query("SELECT * FROM user2 WHERE id = '$userId'");
    $user = $DB->fetchObject($userQid);

    if (!$user) {
        header('Location: index.php');
        exit;
    }
}

$Page->PageTitle = $isNewUser ? 'Add New User' : 'Edit User - ' . htmlspecialchars($user->username);
$Admin->showAdminHeader();
?>

<? if ($message): ?>
<div style="background-color: #f8f9fa; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
    <? echo $message; ?>
</div>
<? endif; ?>

<form method="post" action="">
<table border="0" cellpadding="5" cellspacing="0" width="95%">
    <tr>
        <td valign="top" width="50%">
            <h3><? echo $isNewUser ? 'Create New User' : 'User Information'; ?></h3>
            <table border="0" cellpadding="5" cellspacing="0" width="100%" style="background: #f9f9f9;">
                <tr>
                    <th align="left" width="120">Username <span style="color:red;">*</span>:</th>
                    <td><input type="text" name="username" value="<? echo $user ? htmlspecialchars($user->username) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">Phone:</th>
                    <td><input type="text" name="phone" value="<? echo $user ? htmlspecialchars($user->phone) : ''; ?>" style="width:200px;" placeholder="Mobile number"></td>
                </tr>
                <tr>
                    <th align="left">Email:</th>
                    <td><input type="text" name="email" value="<? echo $user ? htmlspecialchars($user->email) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">Password Hint:</th>
                    <td><input type="text" name="password_hint" value="<? echo $user ? htmlspecialchars($user->password_hint) : ''; ?>" style="width:200px;" placeholder="e.g. Pet name"></td>
                </tr>
                <? if (!$isNewUser): ?>
                <tr>
                    <th align="left">Status:</th>
                    <td>
                        <label>
                            <input type="checkbox" name="status" value="1" <? echo ($user && $user->status) ? 'checked' : ''; ?>>
                            Active
                        </label>
                    </td>
                </tr>
                <? endif; ?>
            </table>
        </td>

        <td valign="top" width="50%">
            <h3><? echo $isNewUser ? 'Password' : 'Change Password'; ?></h3>
            <table border="0" cellpadding="5" cellspacing="0" width="100%" style="background: #f9f9f9;">
                <tr>
                    <th align="left" width="120">
                        <? echo $isNewUser ? 'Password <span style="color:red;">*</span>:' : 'New Password:'; ?>
                    </th>
                    <td><input type="password" name="new_password" value="" style="width:200px;" placeholder="<? echo $isNewUser ? 'Enter password' : 'Leave blank to keep'; ?>"></td>
                </tr>
                <tr>
                    <th align="left">Confirm:</th>
                    <td><input type="password" name="confirm_password" value="" style="width:200px;" placeholder="Confirm password"></td>
                </tr>
                <? if (!$isNewUser): ?>
                <tr>
                    <td colspan="2">
                        <input type="submit" name="save_user" value="Save" class="button" style="padding: 8px 20px;">
                        <input type="submit" name="reset_password" value="Reset Password" class="button" style="padding: 5px 15px; background: #dc3545; color: white; border: none;" onclick="return confirm('Reset password?');">
                    </td>
                </tr>
                <? endif; ?>
            </table>

            <? if (!$isNewUser): ?>
            <div style="margin-top: 20px; padding: 10px; background: #fff3cd; border-radius: 4px;">
                <strong>Note:</strong> Password stored as MD5 hash.<br>
                Created: <? echo $user->created_at; ?>
            </div>
            <? endif; ?>
        </td>
    </tr>
</table>

<div style="margin-top: 20px;">
    <input type="submit" name="save_user" value="<? echo $isNewUser ? 'Create User' : 'Update Profile'; ?>" class="button" style="padding: 10px 30px; font-size: 16px;">
    <a href="index.php" class="button" style="padding: 10px 20px; background: #666; color: white; text-decoration: none; margin-left: 10px;">&laquo; Back to Users</a>
</div>
</form>

<? $Admin->showAdminFooter(); ?>
