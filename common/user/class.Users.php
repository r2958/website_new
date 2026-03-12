<?php
class Users
{
	var $SessionID;
	var $DB;
	var $UserInfo;

	function Users()
	{
		// Use $GLOBALS for PHP 8+ compatibility
		$this->DB = $GLOBALS['DB'];
		$this->SessionID = $this->getSessionID();
		$this->UserInfo = $this->getUserInfo();
	}

	function getSessionID()
	{
		return session_id();
	}
	/*
            users: [{ 
                username: "admin", 
                password: "123", 
                name: "Alexander Wang", 
                phone: "13800138000", 
                avatar: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80", 
                bio: "Curator of fine things.",
                favorites: [101, 104], 
                addresses: [
                    { id: 1, name: "Alexander", phone: "13800138000", detail: "Plaza 66, Nanjing West Road, Shanghai", isDefault: true },
                    { id: 2, name: "Office", phone: "021-88888888", detail: "Lujiazui Center, Pudong New Area, Shanghai", isDefault: false }
                ] 
            }]
	*/

	function loginnew($Username,$Password){
		$qid = $this->DB->query("SELECT * FROM users WHERE Username = '$Username' and Password = '$Password'");
		$row = $this->DB->fetchObject($qid);
		$p_user = array();
		if($row){
			// login in success
			$p_user["status"]= "success";
			$p_user["code"]= 200;
			$p_user["message"]= "Login successful.";
			$p_user["id"] = $row->id;
			$p_user["name"] = $row->FirstName.' '.$row->LastName;
			$p_user["email"] = $row->Email;
			$p_user["tel"] = $row->Telephone;
			$p_user["avatar"] = "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80";
			$p_user["favorites"] = [101,2];
			//set session
			$_SESSION['user'] = $row;
			$p_user["addresses"] = $this->getUserAddresses();


			return $p_user;
			//var_dump(json_encode($p_user));exit;
		}else{
			$p_user["status"]= "error";
			$p_user["code"]= 401;
			$p_user["message"]= "Username or password incorrect.";
			//unset session
			$_SESSION['user'] = "";
			return $p_user;
			
			var_dump(json_encode($p_user));exit;
			//login failed

			return false;
		}
		
	}

	/**
	 * Register new user to user2 table
	 */
	function registerUser2($data) {
		$username = isset($data['username']) ? trim($data['username']) : '';
		$password = isset($data['password']) ? $data['password'] : '';
		$phone = isset($data['phone']) ? trim($data['phone']) : '';
		$email = isset($data['email']) ? trim($data['email']) : '';
		$password_hint = isset($data['password_hint']) ? trim($data['password_hint']) : '';
		$captcha = isset($data['captcha']) ? trim($data['captcha']) : '';

		// Validation
		if (empty($username)) {
			return ['status' => 'error', 'message' => 'Username is required'];
		}
		if (empty($password)) {
			return ['status' => 'error', 'message' => 'Password is required'];
		}
		if (strlen($password) < 6) {
			return ['status' => 'error', 'message' => 'Password must be at least 6 characters'];
		}
		// Check password complexity: at least 2 of (letters, numbers, special chars)
		$hasLetter = preg_match('/[a-zA-Z]/', $password);
		$hasNumber = preg_match('/\d/', $password);
		$hasSpecial = preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password);
		$types = ($hasLetter ? 1 : 0) + ($hasNumber ? 1 : 0) + ($hasSpecial ? 1 : 0);
		if ($types < 2) {
			return ['status' => 'error', 'message' => 'Password must contain at least 2 types: letters, numbers, special characters'];
		}
		// Verify captcha
		if (empty($captcha)) {
			return ['status' => 'error', 'message' => 'Captcha is required'];
		}
		if (!isset($_SESSION['captcha_code']) || strtoupper($captcha) !== $_SESSION['captcha_code']) {
			return ['status' => 'error', 'message' => 'Invalid captcha code'];
		}
		// Clear captcha after verification
		unset($_SESSION['captcha_code']);

		// Check if username exists in user2
		$checkQid = $this->DB->query("SELECT id FROM user2 WHERE username = '" . $this->DB->escape($username) . "'");
		if ($this->DB->numRows($checkQid) > 0) {
			return ['status' => 'error', 'message' => 'Username already exists'];
		}

		// Check if phone exists
		if (!empty($phone)) {
			$phoneCheck = $this->DB->query("SELECT id FROM user2 WHERE phone = '" . $this->DB->escape($phone) . "'");
			if ($this->DB->numRows($phoneCheck) > 0) {
				return ['status' => 'error', 'message' => 'Phone number already registered'];
			}
		}

		// Check if email exists
		if (!empty($email)) {
			$emailCheck = $this->DB->query("SELECT id FROM user2 WHERE email = '" . $this->DB->escape($email) . "'");
			if ($this->DB->numRows($emailCheck) > 0) {
				return ['status' => 'error', 'message' => 'Email already registered'];
			}
		}

		// Insert new user
		$hashedPassword = md5($password);
		$this->DB->query("INSERT INTO user2 (username, password, phone, email, status, password_hint) 
			VALUES ('" . $this->DB->escape($username) . "', '" . $this->DB->escape($hashedPassword) . "', 
			'" . $this->DB->escape($phone) . "', '" . $this->DB->escape($email) . "', 1, 
			'" . $this->DB->escape($password_hint) . "')");

		$newId = $this->DB->insertID();

		return [
			'status' => 'success', 
			'message' => 'Registration successful',
			'data' => [
				'id' => $newId,
				'username' => $username,
				'phone' => $phone,
				'email' => $email
			]
		];
	}

	/**
	 * Login user from user2 table
	 */
	function loginUser2($username, $password) {
		$hashedPassword = md5($password);
		$qid = $this->DB->query("SELECT * FROM user2 WHERE username = '" . $this->DB->escape($username) . "' AND password = '" . $this->DB->escape($hashedPassword) . "'");
		$row = $this->DB->fetchObject($qid);

		if (!$row) {
			return ['status' => 'error', 'code' => 401, 'message' => 'Invalid username or password'];
		}

		if ($row->status == 0) {
			return ['status' => 'error', 'code' => 403, 'message' => 'Account is disabled'];
		}

		// Set session
		$_SESSION['user2'] = $row;

		return [
			'status' => 'success',
			'code' => 200,
			'message' => 'Login successful',
			'id' => $row->id,
			'name' => $row->username,
			'phone' => $row->phone,
			'email' => $row->email,
			'username' => $row->username
		];
	}

	function get_current_user(){
		// Check user2 first
		if(isset($_SESSION['user2']) && !empty($_SESSION['user2'])){
			$row = $_SESSION['user2'];
			$p_user = array();
			$p_user["id"] = $row->id;
			$p_user["name"] = $row->username;
			$p_user["email"] = $row->email;
			$p_user["tel"] = $row->phone;
			$p_user["username"] = $row->username;
			$p_user["isLoggedIn"] = true;
			$p_user["source"] = "user2";
			$p_user["addresses"] = $this->getUserAddresses();
			return $p_user;
		}
		// Fallback to old users table
		if(isset($_SESSION['user']) && !empty($_SESSION['user'])){
			$row = $_SESSION['user'];
			$p_user = array();
			$p_user["id"] = $row->id;
			$p_user["name"] = $row->FirstName.' '.$row->LastName;
			$p_user["email"] = $row->Email;
			$p_user["tel"] = $row->Telephone;
			$p_user["avatar"] = "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80";
			$p_user["favorites"] = [101,2];
			$p_user["isLoggedIn"] = true;
			$p_user["source"] = "users";
			$p_user["addresses"] = $this->getUserAddresses();
			return $p_user;
		}
		return null;
	}

	function login($Username,$Password){
		$qid = $this->DB->query("SELECT * FROM users WHERE Username = '$Username' and Password = '$Password'");
		$row = $this->DB->fetchObject($qid);
		if($row){
			// login in success
			$_SESSION['user'] = $row;
			return true;
		}else{
			//login failed
			return false;
		}
		
	}



	function getUserAddresses(){
		if(!$this->checkLogin()){
			return [];
		}

		// Use the new getAddresses method which supports both user2 and users tables
		$result = $this->getAddresses();
		if ($result['status'] === 'success' && !empty($result['data'])) {
			// Convert to the format expected by the frontend
			$addresses = [];
			foreach ($result['data'] as $addr) {
				$addresses[] = [
					'id' => $addr['id'],
					'name' => $addr['consignee'],
					'phone' => $addr['phone'],
					'detail' => implode(' ', array_filter([$addr['province'], $addr['city'], $addr['district'], $addr['address']])),
					'province' => $addr['province'],
					'city' => $addr['city'],
					'district' => $addr['district'],
					'address' => $addr['address'],
					'postcode' => $addr['postcode'],
					'isDefault' => $addr['is_default']
				];
			}
			return $addresses;
		}

		// Fallback for old users table session
		if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
			$addresses = [];
			if (!empty($_SESSION['user']->ShippingAddress)) {
				$addresses[] = [
					'id' => 1,
					'name' => $_SESSION['user']->FirstName.' '.$_SESSION['user']->LastName,
					'phone' => $_SESSION['user']->Telephone,
					'detail' => $_SESSION['user']->ShippingAddress,
					'isDefault' => true
				];
			}
			if (!empty($_SESSION['user']->BillingAddress)) {
				$addresses[] = [
					'id' => 2,
					'name' => $_SESSION['user']->FirstName.' '.$_SESSION['user']->LastName,
					'phone' => $_SESSION['user']->Telephone,
					'detail' => $_SESSION['user']->BillingAddress,
					'isDefault' => false
				];
			}
			return $addresses;
		}

		return [];
	}
	
	
	function logout(){
		unset($_SESSION);
		header('/');
	}
	
	function checkLogin(){
		// Check user2 session first (new user2 table)
		if(isset($_SESSION['user2']) && !empty($_SESSION['user2']) && $_SESSION['user2']->username!=null){
			return true;
		}
		// Fallback to old users table session
		if(isset($_SESSION['user']) && !empty($_SESSION['user']) && $_SESSION['user']->Username!=null){
			return true;
		}
		return false;
	}

	/**
	 * Get current user ID (supports both user2 and users tables)
	 */
	function getCurrentUserId(){
		// Check user2 session first
		if(isset($_SESSION['user2']) && !empty($_SESSION['user2']) && isset($_SESSION['user2']->id)){
			return $_SESSION['user2']->id;
		}
		// Fallback to old users table
		if(isset($_SESSION['user']) && !empty($_SESSION['user']) && isset($_SESSION['user']->id)){
			return $_SESSION['user']->id;
		}
		return null;
	}

	function getUserName(){
		if(isset($_SESSION['user'])){
			return $_SESSION['user']->Username;
		}else{
			return false;
		}
	}
	
	function updatePassword($username,$oldpassword,$newpassword){		
		$qid = $this->DB->query("UPDATE users SET Password = '$newpassword' WHERE Username = '$username' and Password = '$oldpassword'");
		$check = mysqli_affected_rows();
		if($check>0){
			return true;
		}else{
			return false;
		}
	}
	
	function changeSettings($UserInfo){
		$userName = $this->getUserName();
		unset($UserInfo['done']);
		$sql = 'UPDATE users set ';
		foreach($UserInfo as $k=>$v){
			$i++;
			if(trim($v)==='' || $k==='Username' || $k==='FirstName' || $k==='ship_to_billing'){
				continue;
			}
			$sql .= "$k='$v' , " ;
		}
		$sql = substr($sql,0,strlen($sql)-2);
		$sql .= ' where Username = '."'$userName'"." and FirstName = '".$UserInfo['FirstName']."'";
		$qid = $this->DB->query($sql);
		//$rows = mysqli_affected_rows();
		if($qid){
			$this->updateUserInfo($UserInfo);
			return true;
		}else{
			return false;
		}
	}

	function &getSiteSettings()
	{
		$qid = $this->DB->query("SELECT * FROM site_settings WHERE id = 1");
		$row = $this->DB->fetchObject($qid);
		if($row->ProductsPerPage < 1) {
			$row->ProductsPerPage = 10;
		}
		return $row;
	}

	function updateUserInfo($frm)
	{
		// Saves the order information into the session variable $_SESSION['userinfo'].
		if($this->checkLogin()){
			foreach($frm as $k=>$v){
				$i++;
				if(trim($v)==='' || $k==='Username' || $k==='FirstName' ||$k==='ship_to_billing'){
					continue;
				}
				$_SESSION['user']->$k=$v;
			}
			return true ;
			
			
		}else{
			return false;
		}
	}


	function getUserInfo()
	{
		if(empty($_SESSION['user'])) {
			return false;
		} else {
			return $_SESSION['user'];
		}
	}
	
	function checkUserName($userName){
		$UserName = stripslashes($userName);
		$qid = $this->DB->query("select Username from users where Username = '$userName' ");
		$count = $this->DB->numRows($qid);
		return $count>0?true:false;
	}


	function doSaveFinalUser($user)
	{
		//global $user;
		$user =  (object)$user;
		$this->DB->query('SET NAMES UTF8'); //设置数据库的编码方式为utf8 (alter database andrew  character set utf8)
		
		$qid = $this->DB->query("
			INSERT INTO users (
				CreateTime,Username,Password,
				Company, Title, FirstName, LastName, Email, Telephone, Extension, Fax,
				BillingAddress, BillingAddress2, BillingCity, BillingState, BillingZip, BillingCountry,
				ShippingCompany, ShippingAddress, ShippingAddress2, ShippingCity, ShippingState, ShippingZip, ShippingCountry,
				MailingList

			) VALUES (
				now(),'$user->Username','$user->Password',
				'$user->Company', '$user->Title', '$user->FirstName', '$user->LastName', '$user->Email', '$user->Telephone', '$user->Extension', '$user->Fax',
				'$user->BillingAddress', '$user->BillingAddress2', '$user->BillingCity','$user->BillingState', '$user->BillingZip', '$user->BillingCountry',
				'$user->ShippingCompany', '$user->ShippingAddress', '$user->ShippingAddress2', '$user->ShippingCity', '$user->ShippingState', '$user->ShippingZip', '$user->ShippingCountry',
				'$user->MailingList'
			)");
		$UserID = $this->DB->insertID();
		if($UserID){
			$this->login($user->Username,$user->Password);
		}
		return $UserID;
	}

	// ========== 用户地址管理 ==========

	/**
	 * 初始化地址表（如果不存在则创建）
	 */
	function initAddressTable()
	{
		$sql = "CREATE TABLE IF NOT EXISTS user_addresses (
			id INT AUTO_INCREMENT PRIMARY KEY,
			user_id INT NOT NULL,
			consignee VARCHAR(100) NOT NULL,
			phone VARCHAR(50) NOT NULL,
			country VARCHAR(100) DEFAULT 'China',
			province VARCHAR(50),
			city VARCHAR(50),
			district VARCHAR(50),
			address VARCHAR(255) NOT NULL,
			postcode VARCHAR(20),
			is_default TINYINT(1) DEFAULT 0,
			create_time DATETIME DEFAULT CURRENT_TIMESTAMP,
			update_time DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			INDEX idx_user_id (user_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
		$this->DB->query($sql);
		
		// 检查并添加 country 字段（兼容旧表）
		$checkColumn = $this->DB->query("SHOW COLUMNS FROM user_addresses LIKE 'country'");
		if ($this->DB->numRows($checkColumn) == 0) {
			$this->DB->query("ALTER TABLE user_addresses ADD COLUMN country VARCHAR(100) DEFAULT 'China'");
		}
	}

	/**
	 * 获取用户地址列表
	 */
	function getAddresses()
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$this->initAddressTable();
		$userId = $this->getCurrentUserId();

		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}

		$qid = $this->DB->query("SELECT * FROM user_addresses WHERE user_id = '$userId' ORDER BY is_default DESC, create_time DESC");
		$addresses = array();
		while ($row = $this->DB->fetchObject($qid)) {
			$addresses[] = array(
				'id' => $row->id,
				'consignee' => $row->consignee,
				'phone' => $row->phone,
				'country' => $row->country ?? 'China',
				'province' => $row->province,
				'city' => $row->city,
				'district' => $row->district,
				'address' => $row->address,
				'postcode' => $row->postcode,
				'is_default' => $row->is_default == 1
			);
		}
		return ['status' => 'success', 'data' => $addresses];
	}

	/**
	 * 添加用户地址
	 */
	function addAddress($data)
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$this->initAddressTable();
		$userId = $this->getCurrentUserId();

		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}

		// 验证必填字段
		if (empty($data['consignee']) || empty($data['phone']) || empty($data['address'])) {
			return ['status' => 'error', 'message' => 'Missing required fields'];
		}

		// 检查地址数量限制（最多10条）
		$countQid = $this->DB->query("SELECT COUNT(*) as cnt FROM user_addresses WHERE user_id = '$userId'");
		$countRow = $this->DB->fetchObject($countQid);
		if ($countRow && $countRow->cnt >= 10) {
			return ['status' => 'error', 'message' => 'Maximum 10 addresses allowed'];
		}

		$consignee = $this->DB->escape($data['consignee']);
		$phone = $this->DB->escape($data['phone']);
		$country = $this->DB->escape($data['country'] ?? 'China');
		$province = $this->DB->escape($data['province'] ?? '');
		$city = $this->DB->escape($data['city'] ?? '');
		$district = $this->DB->escape($data['district'] ?? '');
		$address = $this->DB->escape($data['address']);
		$postcode = $this->DB->escape($data['postcode'] ?? '');
		$isDefault = isset($data['is_default']) && $data['is_default'] ? 1 : 0;

		// 如果设置为默认地址，先取消其他默认地址
		if ($isDefault) {
			$this->DB->query("UPDATE user_addresses SET is_default = 0 WHERE user_id = '$userId'");
		}

		$sql = "INSERT INTO user_addresses (user_id, consignee, phone, country, province, city, district, address, postcode, is_default)
				VALUES ('$userId', '$consignee', '$phone', '$country', '$province', '$city', '$district', '$address', '$postcode', '$isDefault')";
		$this->DB->query($sql);
		$newId = $this->DB->insertID();

		// 如果是第一个地址，自动设为默认
		if (!$isDefault && $countRow && $countRow->cnt == 0) {
			$this->DB->query("UPDATE user_addresses SET is_default = 1 WHERE id = '$newId'");
		}

		return ['status' => 'success', 'message' => 'Address added successfully', 'id' => $newId];
	}

	/**
	 * 更新用户地址
	 */
	function updateAddress($id, $data)
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}
		$id = intval($id);

		// 验证地址属于当前用户
		$checkQid = $this->DB->query("SELECT id FROM user_addresses WHERE id = '$id' AND user_id = '$userId'");
		if (!$this->DB->fetchObject($checkQid)) {
			return ['status' => 'error', 'message' => 'Address not found'];
		}

		$updates = array();
		if (isset($data['consignee'])) {
			$updates[] = "consignee = '" . $this->DB->escape($data['consignee']) . "'";
		}
		if (isset($data['phone'])) {
			$updates[] = "phone = '" . $this->DB->escape($data['phone']) . "'";
		}
		if (isset($data['country'])) {
			$updates[] = "country = '" . $this->DB->escape($data['country']) . "'";
		}
		if (isset($data['province'])) {
			$updates[] = "province = '" . $this->DB->escape($data['province']) . "'";
		}
		if (isset($data['city'])) {
			$updates[] = "city = '" . $this->DB->escape($data['city']) . "'";
		}
		if (isset($data['district'])) {
			$updates[] = "district = '" . $this->DB->escape($data['district']) . "'";
		}
		if (isset($data['address'])) {
			$updates[] = "address = '" . $this->DB->escape($data['address']) . "'";
		}
		if (isset($data['postcode'])) {
			$updates[] = "postcode = '" . $this->DB->escape($data['postcode']) . "'";
		}
		if (isset($data['is_default'])) {
			$isDefault = $data['is_default'] ? 1 : 0;
			if ($isDefault) {
				$this->DB->query("UPDATE user_addresses SET is_default = 0 WHERE user_id = '$userId'");
			}
			$updates[] = "is_default = '$isDefault'";
		}

		if (empty($updates)) {
			return ['status' => 'error', 'message' => 'No fields to update'];
		}

		$sql = "UPDATE user_addresses SET " . implode(', ', $updates) . " WHERE id = '$id' AND user_id = '$userId'";
		$this->DB->query($sql);

		return ['status' => 'success', 'message' => 'Address updated successfully'];
	}

	/**
	 * 删除用户地址
	 */
	function deleteAddress($id)
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}
		$id = intval($id);

		// 验证地址属于当前用户
		$checkQid = $this->DB->query("SELECT id, is_default FROM user_addresses WHERE id = '$id' AND user_id = '$userId'");
		$row = $this->DB->fetchObject($checkQid);
		if (!$row) {
			return ['status' => 'error', 'message' => 'Address not found'];
		}

		$wasDefault = $row->is_default;

		$sql = "DELETE FROM user_addresses WHERE id = '$id' AND user_id = '$userId'";
		$this->DB->query($sql);

		// 如果删除的是默认地址，将第一个地址设为默认
		if ($wasDefault) {
			$this->DB->query("UPDATE user_addresses SET is_default = 1 WHERE user_id = '$userId' ORDER BY create_time ASC LIMIT 1");
		}

		return ['status' => 'success', 'message' => 'Address deleted successfully'];
	}

	/**
	 * 设置默认地址
	 */
	function setDefaultAddress($id)
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}
		$id = intval($id);

		// 验证地址属于当前用户
		$checkQid = $this->DB->query("SELECT id FROM user_addresses WHERE id = '$id' AND user_id = '$userId'");
		if (!$this->DB->fetchObject($checkQid)) {
			return ['status' => 'error', 'message' => 'Address not found'];
		}

		// 先取消所有默认地址
		$this->DB->query("UPDATE user_addresses SET is_default = 0 WHERE user_id = '$userId'");
		// 设置新的默认地址
		$this->DB->query("UPDATE user_addresses SET is_default = 1 WHERE id = '$id' AND user_id = '$userId'");

		return ['status' => 'success', 'message' => 'Default address set successfully'];
	}

	// ========== 订单管理 ==========

	/**
	 * 初始化订单表（如果不存在则创建）
	 */
	function initOrderTable()
	{
		$sql = "CREATE TABLE IF NOT EXISTS user_orders (
			id INT AUTO_INCREMENT PRIMARY KEY,
			order_number VARCHAR(50) NOT NULL UNIQUE,
			user_id INT NOT NULL,
			consignee VARCHAR(100) NOT NULL,
			phone VARCHAR(50) NOT NULL,
			country VARCHAR(100) DEFAULT 'China',
			province VARCHAR(50),
			city VARCHAR(50),
			district VARCHAR(50),
			address VARCHAR(255) NOT NULL,
			postcode VARCHAR(20),
			subtotal DECIMAL(10,2) NOT NULL,
			shipping DECIMAL(10,2) DEFAULT 0,
			tax DECIMAL(10,2) DEFAULT 0,
			total DECIMAL(10,2) NOT NULL,
			payment_method VARCHAR(50),
			status VARCHAR(20) DEFAULT 'pending',
			payment_status VARCHAR(20) DEFAULT 'unpaid',
			order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
			update_time DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			INDEX idx_user_id (user_id),
			INDEX idx_order_number (order_number)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
		$this->DB->query($sql);

		$itemsSql = "CREATE TABLE IF NOT EXISTS user_order_items (
			id INT AUTO_INCREMENT PRIMARY KEY,
			order_id INT NOT NULL,
			product_id INT NOT NULL,
			product_name VARCHAR(255) NOT NULL,
			price DECIMAL(10,2) NOT NULL,
			quantity INT NOT NULL,
			product_image VARCHAR(255),
			attribute_id INT DEFAULT NULL,
			attribute_name VARCHAR(100) DEFAULT NULL,
			INDEX idx_order_id (order_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
		$this->DB->query($itemsSql);
		
		// 检查并添加 attribute_id 和 attribute_name 字段（兼容旧表）
		// 使用 SHOW COLUMNS 检查字段是否存在
		$checkColumn = $this->DB->query("SHOW COLUMNS FROM user_order_items LIKE 'attribute_id'");
		if ($this->DB->numRows($checkColumn) == 0) {
			$this->DB->query("ALTER TABLE user_order_items ADD COLUMN attribute_id INT DEFAULT NULL");
		}
		
		$checkColumn2 = $this->DB->query("SHOW COLUMNS FROM user_order_items LIKE 'attribute_name'");
		if ($this->DB->numRows($checkColumn2) == 0) {
			$this->DB->query("ALTER TABLE user_order_items ADD COLUMN attribute_name VARCHAR(100) DEFAULT NULL");
		}
		
		// 检查并添加 country 字段（兼容旧表）
		$checkCountry = $this->DB->query("SHOW COLUMNS FROM user_orders LIKE 'country'");
		if ($this->DB->numRows($checkCountry) == 0) {
			$this->DB->query("ALTER TABLE user_orders ADD COLUMN country VARCHAR(100) DEFAULT 'China'");
		}
	}

	/**
	 * 生成订单号
	 */
	private function generateOrderNumber()
	{
		return 'ORD-' . date('YmdHis') . rand(1000, 9999);
	}

	/**
	 * 创建订单
	 */
	function createOrder($data)
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$this->initOrderTable();
		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}

		// Get username from session (supports both user2 and users tables)
		$username = '';
		if (isset($_SESSION['user2']) && !empty($_SESSION['user2'])) {
			$username = $_SESSION['user2']->username;
		} elseif (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
			$username = $_SESSION['user']->Username;
		}

		// 验证必填字段
		if (empty($data['items']) || !is_array($data['items'])) {
			return ['status' => 'error', 'message' => 'Order items are required'];
		}
		if (empty($data['consignee']) || empty($data['phone']) || empty($data['address'])) {
			return ['status' => 'error', 'message' => 'Shipping address is required'];
		}
		if (!isset($data['total']) || $data['total'] <= 0) {
			return ['status' => 'error', 'message' => 'Invalid order total'];
		}

		$orderNumber = $this->generateOrderNumber();
		$consignee = $this->DB->escape($data['consignee']);
		$phone = $this->DB->escape($data['phone']);
		$country = $this->DB->escape($data['country'] ?? 'China');
		$province = $this->DB->escape($data['province'] ?? '');
		$city = $this->DB->escape($data['city'] ?? '');
		$district = $this->DB->escape($data['district'] ?? '');
		$address = $this->DB->escape($data['address']);
		$postcode = $this->DB->escape($data['postcode'] ?? '');
		$subtotal = floatval($data['subtotal'] ?? 0);
		$shipping = floatval($data['shipping'] ?? 0);
		$tax = floatval($data['tax'] ?? 0);
		$total = floatval($data['total']);
		$paymentMethod = $this->DB->escape($data['payment_method'] ?? 'online');

		// 插入订单主表
		$orderSql = "INSERT INTO user_orders 
			(order_number, user_id, consignee, phone, country, province, city, district, address, postcode, subtotal, shipping, tax, total, payment_method, status, payment_status)
			VALUES ('$orderNumber', '$userId', '$consignee', '$phone', '$country', '$province', '$city', '$district', '$address', '$postcode', '$subtotal', '$shipping', '$tax', '$total', '$paymentMethod', 'pending', 'unpaid')";
		
		$orderResult = $this->DB->query($orderSql);
		if (!$orderResult) {
			return ['status' => 'error', 'message' => 'Failed to create order: Database error'];
		}
		
		$orderId = $this->DB->insertID();

		if (!$orderId) {
			return ['status' => 'error', 'message' => 'Failed to create order'];
		}

		// 插入订单商品
		foreach ($data['items'] as $item) {
			$productId = intval($item['id']);
			$productName = $this->DB->escape($item['name']);
			$price = floatval($item['price']);
			$quantity = intval($item['qty']);
			$productImage = $this->DB->escape($item['img'] ?? '');
			$attributeId = isset($item['attribute_id']) ? intval($item['attribute_id']) : 'NULL';
			$attributeName = isset($item['attribute_name']) ? $this->DB->escape($item['attribute_name']) : '';

			$itemSql = "INSERT INTO user_order_items (order_id, product_id, product_name, price, quantity, product_image, attribute_id, attribute_name)
				VALUES ('$orderId', '$productId', '$productName', '$price', '$quantity', '$productImage', " . ($attributeId === 'NULL' ? 'NULL' : "'$attributeId'") . ", " . ($attributeName ? "'$attributeName'" : 'NULL') . ")";
			$this->DB->query($itemSql);
		}

		return [
			'status' => 'success',
			'message' => 'Order created successfully',
			'data' => [
				'id' => $orderId,
				'order_number' => $orderNumber,
				'subtotal' => $subtotal,
				'shipping' => $shipping,
				'tax' => $tax,
				'total' => $total,
				'status' => 'pending',
				'order_date' => date('Y-m-d H:i:s')
			]
		];
	}

	/**
	 * 获取用户订单列表
	 */
	function getOrders()
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$this->initOrderTable();
		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}

		$qid = $this->DB->query("SELECT * FROM user_orders WHERE user_id = '$userId' ORDER BY order_date DESC");
		$orders = array();
		while ($row = $this->DB->fetchObject($qid)) {
			// 获取订单商品
			$itemsQid = $this->DB->query("SELECT * FROM user_order_items WHERE order_id = '$row->id'");
			$items = array();
			while ($item = $this->DB->fetchObject($itemsQid)) {
				$items[] = array(
					'id' => $item->product_id,
					'name' => $item->product_name,
					'price' => floatval($item->price),
					'qty' => $item->quantity,
					'img' => $item->product_image,
					'attribute_id' => $item->attribute_id,
					'attribute_name' => $item->attribute_name
				);
			}

			$orders[] = array(
				'id' => $row->id,
				'order_number' => $row->order_number,
				'consignee' => $row->consignee,
				'phone' => $row->phone,
				'address' => [
					'name' => $row->consignee,
					'phone' => $row->phone,
					'province' => $row->province,
					'city' => $row->city,
					'district' => $row->district,
					'address' => $row->address,
					'postcode' => $row->postcode,
					'country' => $row->country ?? ''
				],
				'subtotal' => floatval($row->subtotal),
				'shipping' => floatval($row->shipping),
				'tax' => floatval($row->tax),
				'total' => floatval($row->total),
				'payment_method' => $row->payment_method,
				'status' => $row->status,
				'payment_status' => $row->payment_status ?? 'unpaid',
				'order_date' => $row->order_date,
				'items' => $items
			);
		}
		return ['status' => 'success', 'data' => $orders];
	}

	/**
	 * 获取订单详情
	 */
	function getOrderDetail($orderId)
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}
		$orderId = intval($orderId);

		$qid = $this->DB->query("SELECT * FROM user_orders WHERE id = '$orderId' AND user_id = '$userId'");
		$row = $this->DB->fetchObject($qid);

		if (!$row) {
			return ['status' => 'error', 'message' => 'Order not found'];
		}

		// 获取订单商品
		$itemsQid = $this->DB->query("SELECT * FROM user_order_items WHERE order_id = '$orderId'");
		$items = array();
		while ($item = $this->DB->fetchObject($itemsQid)) {
			$items[] = array(
				'id' => $item->product_id,
				'name' => $item->product_name,
				'price' => floatval($item->price),
				'qty' => $item->quantity,
				'img' => $item->product_image,
				'attribute_id' => $item->attribute_id,
				'attribute_name' => $item->attribute_name
			);
		}

		return [
			'status' => 'success',
			'data' => array(
				'id' => $row->id,
				'order_number' => $row->order_number,
				'consignee' => $row->consignee,
				'phone' => $row->phone,
				'address' => [
					'name' => $row->consignee,
					'phone' => $row->phone,
					'province' => $row->province,
					'city' => $row->city,
					'district' => $row->district,
					'address' => $row->address,
					'postcode' => $row->postcode,
					'country' => $row->country ?? ''
				],
				'subtotal' => floatval($row->subtotal),
				'shipping' => floatval($row->shipping),
				'tax' => floatval($row->tax),
				'total' => floatval($row->total),
				'payment_method' => $row->payment_method,
				'status' => $row->status,
				'payment_status' => $row->payment_status ?? 'unpaid',
				'order_date' => $row->order_date,
				'items' => $items
			)
		];
	}

	/**
	 * 更新订单状态
	 */
	function updateOrderStatus($orderId, $status)
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}
		$orderId = intval($orderId);
		$status = $this->DB->escape($status);

		// 验证订单属于当前用户
		$checkQid = $this->DB->query("SELECT id FROM user_orders WHERE id = '$orderId' AND user_id = '$userId'");
		if (!$this->DB->fetchObject($checkQid)) {
			return ['status' => 'error', 'message' => 'Order not found'];
		}

		$sql = "UPDATE user_orders SET status = '$status' WHERE id = '$orderId' AND user_id = '$userId'";
		$this->DB->query($sql);

		return ['status' => 'success', 'message' => 'Order status updated'];
	}

	/**
	 * 取消订单
	 */
	function cancelOrder($orderId)
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}
		$orderId = intval($orderId);

		// 验证订单属于当前用户
		$checkQid = $this->DB->query("SELECT id, status FROM user_orders WHERE id = '$orderId' AND user_id = '$userId'");
		$row = $this->DB->fetchObject($checkQid);
		if (!$row) {
			return ['status' => 'error', 'message' => 'Order not found'];
		}

		// 只有pending状态的订单可以取消
		if ($row->status !== 'pending') {
			return ['status' => 'error', 'message' => 'Only pending orders can be cancelled'];
		}

		$sql = "UPDATE user_orders SET status = 'cancelled' WHERE id = '$orderId' AND user_id = '$userId'";
		$this->DB->query($sql);

		return ['status' => 'success', 'message' => 'Order cancelled successfully'];
	}

	/**
	 * 更新订单支付状态
	 */
	function updatePaymentStatus($orderId, $status, $paymentMethod = null)
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}
		$orderId = intval($orderId);

		// 验证订单属于当前用户
		$checkQid = $this->DB->query("SELECT id, payment_status FROM user_orders WHERE id = '$orderId' AND user_id = '$userId'");
		$row = $this->DB->fetchObject($checkQid);
		if (!$row) {
			return ['status' => 'error', 'message' => 'Order not found'];
		}

		// 验证支付状态值
		$allowedStatuses = ['unpaid', 'paying', 'paid'];
		if (!in_array($status, $allowedStatuses)) {
			return ['status' => 'error', 'message' => 'Invalid payment status'];
		}

		// 已支付的订单不能更改支付状态
		if ($row->payment_status === 'paid') {
			return ['status' => 'error', 'message' => 'Order is already paid'];
		}

		$status = $this->DB->escape($status);
		
		// 构建更新SQL
		$updateFields = "payment_status = '$status'";
		if ($paymentMethod !== null) {
			$paymentMethod = $this->DB->escape($paymentMethod);
			$updateFields .= ", payment_method = '$paymentMethod'";
		}
		
		$sql = "UPDATE user_orders SET $updateFields WHERE id = '$orderId' AND user_id = '$userId'";
		$this->DB->query($sql);

		return ['status' => 'success', 'message' => 'Payment status updated'];
	}

	/**
	 * 更新订单商品数量
	 */
	function updateOrderItemQuantity($orderId, $itemId, $quantity)
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}
		$orderId = intval($orderId);
		$itemId = intval($itemId);
		$quantity = intval($quantity);

		// 验证订单属于当前用户且状态为 pending
		$checkQid = $this->DB->query("SELECT id, status FROM user_orders WHERE id = '$orderId' AND user_id = '$userId'");
		$row = $this->DB->fetchObject($checkQid);
		if (!$row) {
			return ['status' => 'error', 'message' => 'Order not found'];
		}

		if ($row->status !== 'pending') {
			return ['status' => 'error', 'message' => 'Cannot modify items for non-pending orders'];
		}

		// 验证订单商品存在
		$itemCheckQid = $this->DB->query("SELECT id, price FROM user_order_items WHERE id = '$itemId' AND order_id = '$orderId'");
		$itemRow = $this->DB->fetchObject($itemCheckQid);
		if (!$itemRow) {
			return ['status' => 'error', 'message' => 'Order item not found'];
		}

		if ($quantity <= 0) {
			// 删除商品
			$this->DB->query("DELETE FROM user_order_items WHERE id = '$itemId' AND order_id = '$orderId'");
		} else {
			// 更新数量
			$this->DB->query("UPDATE user_order_items SET quantity = '$quantity' WHERE id = '$itemId' AND order_id = '$orderId'");
		}

		// 重新计算订单金额
		$this->recalculateOrderTotals($orderId);

		return ['status' => 'success', 'message' => 'Item quantity updated'];
	}

	/**
	 * 从订单中删除商品
	 */
	function removeOrderItem($orderId, $itemId)
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}
		$orderId = intval($orderId);
		$itemId = intval($itemId);

		// 验证订单属于当前用户且状态为 pending
		$checkQid = $this->DB->query("SELECT id, status FROM user_orders WHERE id = '$orderId' AND user_id = '$userId'");
		$row = $this->DB->fetchObject($checkQid);
		if (!$row) {
			return ['status' => 'error', 'message' => 'Order not found'];
		}

		if ($row->status !== 'pending') {
			return ['status' => 'error', 'message' => 'Cannot remove items from non-pending orders'];
		}

		// 删除商品
		$this->DB->query("DELETE FROM user_order_items WHERE id = '$itemId' AND order_id = '$orderId'");

		// 检查是否还有商品，如果没有则取消订单
		$itemsQid = $this->DB->query("SELECT COUNT(*) as cnt FROM user_order_items WHERE order_id = '$orderId'");
		$itemsCount = $this->DB->fetchObject($itemsQid);
		if ($itemsCount && $itemsCount->cnt == 0) {
			$this->DB->query("UPDATE user_orders SET status = 'cancelled' WHERE id = '$orderId'");
			return ['status' => 'success', 'message' => 'All items removed, order cancelled'];
		}

		// 重新计算订单金额
		$this->recalculateOrderTotals($orderId);

		return ['status' => 'success', 'message' => 'Item removed from order'];
	}

	/**
	 * 重新计算订单金额
	 */
	private function recalculateOrderTotals($orderId)
	{
		// 计算商品小计
		$subtotalQid = $this->DB->query("SELECT SUM(price * quantity) as subtotal FROM user_order_items WHERE order_id = '$orderId'");
		$subtotalRow = $this->DB->fetchObject($subtotalQid);
		$subtotal = $subtotalRow ? floatval($subtotalRow->subtotal) : 0;

		// 获取当前运费
		$orderQid = $this->DB->query("SELECT shipping FROM user_orders WHERE id = '$orderId'");
		$orderRow = $this->DB->fetchObject($orderQid);
		$shipping = $orderRow ? floatval($orderRow->shipping) : 0;

		// 计算总金额
		$total = $subtotal + $shipping;

		// 更新订单
		$this->DB->query("UPDATE user_orders SET subtotal = '$subtotal', total = '$total' WHERE id = '$orderId'");
	}

	/**
	 * 添加商品到现有订单（仅pending状态）
	 */
	function addItemToOrder($orderId, $itemData)
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}
		$orderId = intval($orderId);

		// 验证订单属于当前用户且状态为 pending
		$checkQid = $this->DB->query("SELECT id, status FROM user_orders WHERE id = '$orderId' AND user_id = '$userId'");
		$row = $this->DB->fetchObject($checkQid);
		if (!$row) {
			return ['status' => 'error', 'message' => 'Order not found'];
		}

		if ($row->status !== 'pending') {
			return ['status' => 'error', 'message' => 'Cannot add items to non-pending orders'];
		}

		// 验证商品数据
		if (empty($itemData['id']) || empty($itemData['name']) || empty($itemData['price']) || empty($itemData['quantity'])) {
			return ['status' => 'error', 'message' => 'Invalid item data'];
		}

		$productId = intval($itemData['id']);
		$productName = $this->DB->escape($itemData['name']);
		$price = floatval($itemData['price']);
		$quantity = intval($itemData['quantity']);
		$productImage = $this->DB->escape($itemData['img'] ?? '');

		// 检查商品是否已存在
		$existQid = $this->DB->query("SELECT id, quantity FROM user_order_items WHERE order_id = '$orderId' AND product_id = '$productId'");
		$existItem = $this->DB->fetchObject($existQid);

		if ($existItem) {
			// 更新数量
			$newQty = $existItem->quantity + $quantity;
			$this->DB->query("UPDATE user_order_items SET quantity = '$newQty' WHERE id = '$existItem->id'");
		} else {
			// 添加新商品
			$itemSql = "INSERT INTO user_order_items (order_id, product_id, product_name, price, quantity, product_image)
				VALUES ('$orderId', '$productId', '$productName', '$price', '$quantity', '$productImage')";
			$this->DB->query($itemSql);
		}

		// 重新计算订单金额
		$this->recalculateOrderTotals($orderId);

		return ['status' => 'success', 'message' => 'Item added to order'];
	}

	// ========== Wishlist 收藏管理 ==========

	/**
	 * 初始化Wishlist表（如果不存在则创建）
	 */
	function initWishlistTable()
	{
		$sql = "CREATE TABLE IF NOT EXISTS user_wishlist (
			id INT AUTO_INCREMENT PRIMARY KEY,
			user_id INT NOT NULL,
			product_id INT NOT NULL,
			product_name VARCHAR(255) NOT NULL,
			product_price DECIMAL(10,2) NOT NULL,
			product_image VARCHAR(255),
			create_time DATETIME DEFAULT CURRENT_TIMESTAMP,
			UNIQUE KEY unique_user_product (user_id, product_id),
			INDEX idx_user_id (user_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
		$this->DB->query($sql);
	}

	/**
	 * 获取用户Wishlist列表
	 */
	function getWishlist()
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$this->initWishlistTable();
		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}

		$qid = $this->DB->query("SELECT * FROM user_wishlist WHERE user_id = '$userId' ORDER BY create_time DESC");
		$items = array();
		while ($row = $this->DB->fetchObject($qid)) {
			$items[] = array(
				'id' => $row->id,
				'product_id' => $row->product_id,
				'product_name' => $row->product_name,
				'product_price' => floatval($row->product_price),
				'product_image' => $row->product_image,
				'create_time' => $row->create_time
			);
		}
		return ['status' => 'success', 'data' => $items];
	}

	/**
	 * 添加商品到Wishlist
	 */
	function addToWishlist($data)
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$this->initWishlistTable();
		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}

		// 验证必填字段
		if (empty($data['product_id'])) {
			return ['status' => 'error', 'message' => 'Product ID is required'];
		}

		$productId = intval($data['product_id']);
		$productName = $this->DB->escape($data['product_name'] ?? '');
		$productPrice = floatval($data['product_price'] ?? 0);
		$productImage = $this->DB->escape($data['product_image'] ?? '');

		// 检查是否已存在
		$checkQid = $this->DB->query("SELECT id FROM user_wishlist WHERE user_id = '$userId' AND product_id = '$productId'");
		if ($this->DB->fetchObject($checkQid)) {
			return ['status' => 'error', 'message' => 'Product already in wishlist'];
		}

		$sql = "INSERT INTO user_wishlist (user_id, product_id, product_name, product_price, product_image)
			VALUES ('$userId', '$productId', '$productName', '$productPrice', '$productImage')";
		$this->DB->query($sql);
		$newId = $this->DB->insertID();

		return ['status' => 'success', 'message' => 'Added to wishlist', 'id' => $newId];
	}

	/**
	 * 从Wishlist移除商品
	 */
	function removeFromWishlist($productId)
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User ID not found', 'needLogin' => true];
		}

		$productId = intval($productId);
		$sql = "DELETE FROM user_wishlist WHERE user_id = '$userId' AND product_id = '$productId'";
		$this->DB->query($sql);

		return ['status' => 'success', 'message' => 'Removed from wishlist'];
	}

	/**
	 * 检查商品是否在Wishlist中
	 */
	function isInWishlist($productId)
	{
		if (!$this->checkLogin()) {
			return false;
		}

		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return false;
		}

		$productId = intval($productId);
		$qid = $this->DB->query("SELECT id FROM user_wishlist WHERE user_id = '$userId' AND product_id = '$productId'");
		return $this->DB->fetchObject($qid) ? true : false;
	}

	/**
	 * 切换Wishlist状态（添加/移除）
	 */
	function toggleWishlist($data)
	{
		if (!$this->checkLogin()) {
			return ['status' => 'error', 'code' => 401, 'message' => 'User not logged in', 'needLogin' => true];
		}

		$productId = intval($data['product_id']);
		$isInWishlist = $this->isInWishlist($productId);

		if ($isInWishlist) {
			$result = $this->removeFromWishlist($productId);
			$result['action'] = 'removed';
			return $result;
		} else {
			$result = $this->addToWishlist($data);
			$result['action'] = 'added';
			return $result;
		}
	}


}
?>
