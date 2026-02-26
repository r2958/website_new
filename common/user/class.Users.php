<?
class Users
{
	var $SessionID;
	var $DB;
	var $UserInfo;

	function Users()
	{
		global  $DB;
		$this->DB =& $DB;
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

	function get_current_user(){
		if($this->checkLogin()){
			$row = $_SESSION['user'];
			$p_user = array();
			$p_user["id"] = $row->id;
			$p_user["name"] = $row->FirstName.' '.$row->LastName;
			$p_user["email"] = $row->Email;
			$p_user["tel"] = $row->Telephone;
			$p_user["avatar"] = "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80";
			$p_user["favorites"] = [101,2];
			$p_user["addresses"] = $this->getUserAddresses();
			return $p_user;
		}else{
			return null;
		}
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
			return false;
		}
		$addresses = array();
		$addresses[] = array(
			'id' => 1,
			'name' => $_SESSION['user']->FirstName.' '.$_SESSION['user']->LastName,
			'phone' => $_SESSION['user']->Telephone,
			'detail' => $_SESSION['user']->ShippingAddress,
			'isDefault' => true
		);
		$addresses[]= array(
			'id' => 2,
			'name' => $_SESSION['user']->FirstName.' '.$_SESSION['user']->LastName,
			'phone' => $_SESSION['user']->Telephone,
			'detail' => $_SESSION['user']->BillingAddress,
			'isDefault' => false
		);

		return $addresses;

		/*
		$username = $this->getUserName();
		$qid = $this->DB->query("SELECT * FROM user_addresses WHERE Username = '$username'");
		$addresses = array();
		while($row = $this->DB->fetchObject($qid)){
			$addresses[] = $row;
		}
		return $addresses;
		*/
	}
	
	
	function logout(){
		unset($_SESSION);
		header('/');
	}
	
	function checkLogin(){
		if(isset($_SESSION['user']) && !empty($_SESSION['user']) && $_SESSION['user']->Username!=null){
			return true;
		}else{
			return false;
		}
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


}
?>
