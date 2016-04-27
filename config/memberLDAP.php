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
require_once "memberABSTRACT.php";

class MemberLDAP extends MemberAbstract{
	private $ldap_url="ldap.example.com";
		
	public function __construct($connection){
		parent::__construct($connection);
	}

	/*
	 * Overriding of method Authentication()
	 * This make Member customizable at all for each authentication method
	 */
	protected function Authentication ($username, $password) {
		// 1st step - Authentication
		try {
			$ldap = ldap_connect($this->ldap_url);
			if (!($bind = ldap_bind($ldap, $username, $password))) {
				return false;
			}		
		} catch (SoapFault $exception) {
			return false;
		}
		//2nd step - more info from local DB
		$qry = "Select * from users where username='$username'";
		$conn = $this->GetConnection();
		$result = $conn->query($qry);
		
		
		$row = $result->fetch_assoc();
		$_SESSION['username'] = $row['username'];
		$_SESSION['first_name']  = $row['first_name'];
		$_SESSION['last_name']  = $row['last_name'];
		$_SESSION['user_full_name'] = $row['first_name'] ." ".$row['last_name'];
		$_SESSION['type']  = $row['type'];
		$_SESSION['id_user'] = $row['id_user'];
		$_SESSION['last_time_of_user'] = $row['last_time'];
		$_SESSION['login_forge'] = $row['login_forge'];
		
		$this->updateUserInfo($username);
		return true;
	}
}
?>