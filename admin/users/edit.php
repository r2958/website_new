<?
require_once('../../application.php');
require_once('../auth.php');

$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isNewUser = ($userId == 0);

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_user'])) {
        $Username = $DB->escape($_POST['Username']);
        $Password = $_POST['Password'];
        $FirstName = $DB->escape($_POST['FirstName']);
        $LastName = $DB->escape($_POST['LastName']);
        $Email = $DB->escape($_POST['Email']);
        $Company = $DB->escape($_POST['Company']);
        $Telephone = intval($_POST['Telephone']);
        
        // Additional required fields
        $Title = $DB->escape($_POST['Title']);
        $Extension = $DB->escape($_POST['Extension']);
        $BillingAddress = $DB->escape($_POST['BillingAddress']);
        $BillingAddress2 = $DB->escape($_POST['BillingAddress2']);
        $BillingCity = $DB->escape($_POST['BillingCity']);
        $BillingState = $DB->escape($_POST['BillingState']);
        $BillingZip = $DB->escape($_POST['BillingZip']);
        $BillingCountry = $DB->escape($_POST['BillingCountry']);
        $ShippingAddress = $DB->escape($_POST['ShippingAddress']);
        $ShippingAddress2 = $DB->escape($_POST['ShippingAddress2']);
        $ShippingCity = $DB->escape($_POST['ShippingCity']);
        $ShippingState = $DB->escape($_POST['ShippingState']);
        $ShippingZip = $DB->escape($_POST['ShippingZip']);
        $ShippingCountry = $DB->escape($_POST['ShippingCountry']);
        $MailingList = $DB->escape($_POST['MailingList']);

        // Validation
        if (empty($Username)) {
            $message = '<span style="color:red;">Error: Username is required</span>';
        } elseif (empty($Password)) {
            $message = '<span style="color:red;">Error: Password is required</span>';
        } elseif (strlen($Password) < 4) {
            $message = '<span style="color:red;">Error: Password must be at least 4 characters</span>';
        } else {
            // Check if username already exists
            $checkQid = $DB->query("SELECT id FROM users WHERE Username = '$Username'" . ($isNewUser ? "" : " AND id != '$userId'"));
            if ($DB->numRows($checkQid) > 0) {
                $message = '<span style="color:red;">Error: Username already exists</span>';
            } else {
                if ($isNewUser) {
                    // Create new user
                    $hashedPassword = md5($Password);
                    $DB->query("INSERT INTO users (Username, Password, Company, Title, Extension, FirstName, LastName, Email, Telephone, Fax, BillingAddress, BillingAddress2, BillingCity, BillingState, BillingZip, BillingCountry, ShippingCompany, ShippingAddress, ShippingAddress2, ShippingCity, ShippingState, ShippingZip, ShippingCountry, MailingList, CreateTime) 
                                VALUES ('$Username', '$hashedPassword', '$Company', '$Title', '$Extension', '$FirstName', '$LastName', '$Email', '$Telephone', 0, '$BillingAddress', '$BillingAddress2', '$BillingCity', '$BillingState', '$BillingZip', '$BillingCountry', '$Company', '$ShippingAddress', '$ShippingAddress2', '$ShippingCity', '$ShippingState', '$ShippingZip', '$ShippingCountry', '$MailingList', NOW())");
                    $newUserId = $DB->insertID();
                    $message = '<span style="color:green;">User created successfully!</span>';
                    // Redirect to edit page
                    header('Location: edit.php?id=' . $newUserId);
                    exit;
                } else {
                    // Update existing user
                    $DB->query("UPDATE users SET FirstName='$FirstName', LastName='$LastName', Email='$Email', Company='$Company', Telephone='$Telephone' WHERE id='$userId'");
                    $message = '<span style="color:green;">User profile updated successfully!</span>';
                }
            }
        }

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
            // Hash password (simple md5 for this system)
            $hashedPassword = md5($newPassword);
            $DB->query("UPDATE users SET Password='$hashedPassword' WHERE id='$userId'");
            $message = '<span style="color:green;">Password reset successfully!</span>';
        }
    }
}

$user = null;
if (!$isNewUser) {
    // Get user details
    $userQid = $DB->query("SELECT * FROM users WHERE id = '$userId'");
    $user = $DB->fetchObject($userQid);

    if (!$user) {
        header('Location: index.php');
        exit;
    }
}

$Page->PageTitle = $isNewUser ? 'Add New User' : 'Edit User - ' . htmlspecialchars($user->Username);
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
                    <td><input type="text" name="Username" value="<? echo $user ? htmlspecialchars($user->Username) : ''; ?>" style="width:200px;" <? echo $isNewUser ? '' : 'readonly'; ?>></td>
                </tr>
                <tr>
                    <th align="left">Password <span style="color:red;">*</span>:</th>
                    <td><input type="password" name="Password" value="" style="width:200px;" placeholder="<? echo $isNewUser ? 'Enter password' : 'Leave blank to keep current'; ?>"></td>
                </tr>
                <tr>
                    <th align="left">Title:</th>
                    <td>
                        <select name="Title" style="width:200px;">
                            <option value="Mr." <? echo ($user && $user->Title == 'Mr.') ? 'selected' : ''; ?>>Mr.</option>
                            <option value="Mrs." <? echo ($user && $user->Title == 'Mrs.') ? 'selected' : ''; ?>>Mrs.</option>
                            <option value="Ms." <? echo ($user && $user->Title == 'Ms.') ? 'selected' : ''; ?>>Ms.</option>
                            <option value="Dr." <? echo ($user && $user->Title == 'Dr.') ? 'selected' : ''; ?>>Dr.</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th align="left">First Name:</th>
                    <td><input type="text" name="FirstName" value="<? echo $user ? htmlspecialchars($user->FirstName) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">Last Name:</th>
                    <td><input type="text" name="LastName" value="<? echo $user ? htmlspecialchars($user->LastName) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">Email:</th>
                    <td><input type="text" name="Email" value="<? echo $user ? htmlspecialchars($user->Email) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">Company:</th>
                    <td><input type="text" name="Company" value="<? echo $user ? htmlspecialchars($user->Company) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">Telephone:</th>
                    <td><input type="text" name="Telephone" value="<? echo $user ? $user->Telephone : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">Extension:</th>
                    <td><input type="text" name="Extension" value="<? echo $user ? htmlspecialchars($user->Extension) : ''; ?>" style="width:200px;"></td>
                </tr>
            </table>
        </td>

        <td valign="top" width="50%">
            <h3>Billing Address</h3>
            <table border="0" cellpadding="5" cellspacing="0" width="100%" style="background: #f9f9f9;">
                <tr>
                    <th align="left" width="100">Address:</th>
                    <td><input type="text" name="BillingAddress" value="<? echo $user ? htmlspecialchars($user->BillingAddress) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">Address 2:</th>
                    <td><input type="text" name="BillingAddress2" value="<? echo $user ? htmlspecialchars($user->BillingAddress2) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">City:</th>
                    <td><input type="text" name="BillingCity" value="<? echo $user ? htmlspecialchars($user->BillingCity) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">State:</th>
                    <td><input type="text" name="BillingState" value="<? echo $user ? htmlspecialchars($user->BillingState) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">Zip:</th>
                    <td><input type="text" name="BillingZip" value="<? echo $user ? htmlspecialchars($user->BillingZip) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">Country:</th>
                    <td><input type="text" name="BillingCountry" value="<? echo $user ? htmlspecialchars($user->BillingCountry) : ''; ?>" style="width:200px;"></td>
                </tr>
            </table>
        </td>
    </tr>
    
    <tr>
        <td valign="top" width="50%">
            <h3>Shipping Address</h3>
            <table border="0" cellpadding="5" cellspacing="0" width="100%" style="background: #f9f9f9;">
                <tr>
                    <th align="left" width="100">Address:</th>
                    <td><input type="text" name="ShippingAddress" value="<? echo $user ? htmlspecialchars($user->ShippingAddress) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">Address 2:</th>
                    <td><input type="text" name="ShippingAddress2" value="<? echo $user ? htmlspecialchars($user->ShippingAddress2) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">City:</th>
                    <td><input type="text" name="ShippingCity" value="<? echo $user ? htmlspecialchars($user->ShippingCity) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">State:</th>
                    <td><input type="text" name="ShippingState" value="<? echo $user ? htmlspecialchars($user->ShippingState) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">Zip:</th>
                    <td><input type="text" name="ShippingZip" value="<? echo $user ? htmlspecialchars($user->ShippingZip) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">Country:</th>
                    <td><input type="text" name="ShippingCountry" value="<? echo $user ? htmlspecialchars($user->ShippingCountry) : ''; ?>" style="width:200px;"></td>
                </tr>
                <tr>
                    <th align="left">Mailing List:</th>
                    <td>
                        <select name="MailingList" style="width:200px;">
                            <option value="Yes" <? echo ($user && $user->MailingList == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                            <option value="No" <? echo ($user && $user->MailingList == 'No') ? 'selected' : ''; ?>>No</option>
                        </select>
                    </td>
                </tr>
            </table>
        </td>
        
        <? if (!$isNewUser): ?>
        <td valign="top" width="50%">
            <h3>Reset Password</h3>
            <table border="0" cellpadding="5" cellspacing="0" width="100%" style="background: #f9f9f9;">
                <tr>
                    <th align="left" width="120">New Password:</th>
                    <td><input type="password" name="new_password" value="" style="width:200px;" placeholder="Enter new password"></td>
                </tr>
                <tr>
                    <th align="left">Confirm Password:</th>
                    <td><input type="password" name="confirm_password" value="" style="width:200px;" placeholder="Confirm new password"></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="submit" name="reset_password" value="Reset Password" class="button" style="padding: 5px 15px; background: #dc3545; color: white; border: none;" onclick="return confirm('Are you sure you want to reset this user\'s password?');">
                    </td>
                </tr>
            </table>

            <div style="margin-top: 20px; padding: 10px; background: #fff3cd; border-radius: 4px;">
                <strong>Note:</strong> Password will be stored as MD5 hash.
            </div>
        </td>
        <? endif; ?>
    </tr>
</table>

<div style="margin-top: 20px;">
    <input type="submit" name="save_user" value="<? echo $isNewUser ? 'Create User' : 'Update Profile'; ?>" class="button" style="padding: 10px 30px; font-size: 16px;">
    <a href="index.php" class="button" style="padding: 10px 20px; background: #666; color: white; text-decoration: none; margin-left: 10px;">&laquo; Back to Users</a>
</div>
</form>

<? $Admin->showAdminFooter(); ?>
