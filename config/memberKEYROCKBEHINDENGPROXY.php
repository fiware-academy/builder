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

class MemberKEYROCKBEHINDENGPROXY extends MemberAbstract{
		
	public function __construct($connection){
		parent::__construct($connection);
	}

	/*
	 * Overriding of method Authentication()
	 * This make Member customizable at all for each authentication method
	 */
	protected function Authentication ($username, $password) {
		// 1st step - Authentication
		$user = get_user("https://cloud.lab.fiware.org/keystone/v2.0/tokens", $username, $password);
		
			
		//2nd step - more info from local DB
		$qry = "Select * from users where username='$username'";
		$result = $this->_connection->query($qry);
		
		
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
	
	function get_user($url, $email, $password){
	
		$data = array("auth" => ["passwordCredentials" =>[ "username" => $email, "password" => $password]]);
	
		$curl = curl_init($url);
	
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	
		//se passi per il proxy nostro aziendale altrimenti commenta le due righe sotto!
		curl_setopt($curl, CURLOPT_PROXY, "proxy.eng.it:3128");
		curl_setopt($curl, CURLOPT_PROXYUSERPWD, "tuo.username:tua.password");
	
		$headers = array();
		$headers[] = 'Content-type : aplication/json';
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	
		$json_response = curl_exec($curl);
		curl_close($curl);
	
		$obj = json_decode($json_response);
		/*
		var_dump($obj);
	
		if (isset($obj)){
			echo $obj->access->user->username;
			echo '<br>';
			echo $obj->access->user->id;
			echo '<br>';
			echo $obj->access->user->name;
		}else{
			echo 'no user';
		}
		*/
		return isset($obj);
		
	}
	
}
?>