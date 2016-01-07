<div align="center">
			<font size="+1"><b>Welcome <?php echo $_POST['Username']?></b></font>
			<p>Thank you for signing up for an account with Neturf Solutions! Your account information is listed below:</p>
				<table>
					<tbody><tr>
						<td><b>Username:</b></td>
						<td><b><?php echo $_POST['Username']?></b></td>
					</tr>
					<tr>
						<td><b>Password:</b></td>
						<td><b><?php echo $_POST['Password']?></b></td>
					</tr>
				</tbody></table>
			<p>You can either continue shopping, or <a href="/checkout/index.php">check out</a>.</p>
		</div>