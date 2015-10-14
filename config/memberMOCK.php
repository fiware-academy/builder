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

class MemberMOCK extends MemberAbstract{
		
	public function __construct($connection){
		parent::__construct($connection);
	}

	/*
	 * Overriding of method Authentication()
	 * This make Member customizable at all for each authentication method
	 */
	protected function Authentication ($username, $password) {
		//1st - authentication
		$avaibleusers=array("administrator","manager","user");
		if(!in_array($username,$avaibleusers)){
			return false;
		}
		if($username!="mockuser" || $password!='mockpassword'){
			return false;
		}
		
		
		$_SESSION['username'] = 'mockuser';
		$_SESSION['first_name']  = 'Mock';
		$_SESSION['last_name']  = 'User';
		$_SESSION['user_full_name'] = "Mock User";
		$_SESSION['type']  = 'user';
		$_SESSION['id_user'] = 00;
		$_SESSION['last_time_of_user'] = '';
		$_SESSION['login_forge'] = '';
		
		$this->updateUserInfo($username);
		return true;
	}
}
?>