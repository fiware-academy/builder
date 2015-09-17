<!--  
   Copyright (c) 2011, 2014 Engineering Group and others.
   All rights reserved. This program and the accompanying materials
   are made available under the terms of the Eclipse Public License v1.0
   which accompanies this distribution, and is available at
   http://www.eclipse.org/legal/epl-v10.html
 
   Contributors:
       Engineering Group - Course Builder
 -->
 
 <?php

abstract class MemberAbstract {
		
	private $_connection;
	private $_error_message;
	
	private $_sessionvar = "user_logged";
	
	public function Member($connection){
		$this->_connection = $connection;
	}

	function Login() {
		if(empty($_POST['username'])) {
			$this->HandleError("The username field is empty!");
			return false;
		}

		if(empty($_POST['password'])) {
			$this->HandleError("The password field is empty!");
			return false;
		}

		$username = trim($_POST['username']);
		$password = trim($_POST['password']);

		if(!isset($_SESSION))
			session_start();
		//
		if($this->SanitizeForSQL($username)==ADMIN_USERNAME){
			if(MemberAbstract::AuthenticateAdmin($this, $username, $password)){
				$_SESSION[$this->_sessionvar] = $username;
				return true;
			}
			return false;
		}
		// 1st step - Authenticaton
		//Here whould works the specific method for authentication
		if(!$this->Authentication($this->SanitizeForSQL($username),$password))return false;

		$_SESSION[$this->_sessionvar] = $username;
		return true;
	}
	
	function CheckLogin() {	
						
		if(empty($_SESSION[$this -> _sessionvar])) {
			return false;
		}
		return true;
	}
	
	function LogOut() {
		$_SESSION[$this->_sessionvar] = NULL;
		unset($_SESSION[$this->_sessionvar]);
		unset($_SESSION['username']);
		unset($_SESSION['first_name']);
   		unset($_SESSION['last_name']);
   		unset($_SESSION['user_full_name']);
   		unset($_SESSION['id_user']);
   		unset($_SESSION['type']);
   		unset($_SESSION['last_time_of_user']);
   		unset($_SESSION['login_forge']);
	}
	
	function Username() {
		return isset($_SESSION['username'])?$_SESSION['username']:'';
	}
	
	function UserFullName() {
		return isset($_SESSION['user_full_name'])?$_SESSION['user_full_name']:'';
	}
	
	function GetIdUser() {
		return isset($_SESSION['id_user'])?$_SESSION['id_user']:'';
	}
	
	function IsAdministrator(){
		return (isset($_SESSION['type']) && $_SESSION['type'] == 'ADMIN');
	}
	
	function IsManager(){
		return (isset($_SESSION['type']) && $_SESSION['type'] == 'MANAGER');
	}
	
	function LastTime() {
		return isset($_SESSION['last_time_of_user'])?$_SESSION['last_time_of_user']:'';
	}
	
	function IsLoggedInForge(){
		return (isset($_SESSION['login_forge'])?$_SESSION['login_forge']:'');
	}
    
	function GetSelfScript() {
		return htmlentities($_SERVER['PHP_SELF']);
	}

	function SafeDisplay($value_name) {
		if(empty($_POST[$value_name])) {
			return'';
		}
		return htmlentities($_POST[$value_name]);
	}
	
	function RedirectToURL($url) {
		header("Location: $url");
		exit;
	}
	
	function SanitizeForSQL ($str) {
		return mysqli_escape_string($this->_connection, $str);
	}
	
	function GetErrorMessage() {
		if(empty($this->_error_message)) {
			return '';
		}
		//$errormsg = nl2br(htmlentities($this->_error_message));
		return $this->_error_message;
	}

	function HandleError($err) {
		$this->_error_message .= $err."\r\n";
	}
	
	function updateUser($username, $firstname, $lastname){	
		$qry = "INSERT INTO users (username, first_name, last_name, login_forge) VALUES ('$username', '$firstname', '$lastname', 1)
		 ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name), login_forge = VALUES(login_forge)";
		$result = $this->_connection->query($qry);		
	}
	
	function updateUserInfo($username){
		$now = date("Y-n-j G:i:s");
		$qry = "UPDATE users SET last_time='$now' WHERE username='$username'";
		$result = $this->_connection->query($qry);
	}
	
	protected function GetConnection(){
		return $this->_connection;
	}
	
	protected abstract function Authentication ($username, $password);
	
	public static function AuthenticateAdmin(MemberAbstract $member,$username,$password){
		//1st - authentication
		$connection = $member->GetConnection();
		$qry = "Select * from users where username='$username' and password='$password'";
		$result = $member->_connection->query($qry);
			
		if(!$result || $result->num_rows <= 0) {
			$member->HandleError("Login error. Username or Password doesn't match!");
			return false;
		}
		//2nd step - more info from local DB
		$qry = "Select * from users where username='$username'";
		$result = $member->_connection->query($qry);
		
		
		$row = $result->fetch_assoc();
		$_SESSION['username'] = $row['username'];
		$_SESSION['first_name']  = $row['first_name'];
		$_SESSION['last_name']  = $row['last_name'];
		$_SESSION['user_full_name'] = $row['first_name'] ." ".$row['last_name'];
		$_SESSION['type']  = $row['type'];
		$_SESSION['id_user'] = $row['id_user'];
		$_SESSION['last_time_of_user'] = $row['last_time'];
		$_SESSION['login_forge'] = $row['login_forge'];
		
		$member->updateUserInfo($username);
		return true;
	}
	
}

?>