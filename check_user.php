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
	
	session_start();

	include_once 'config/config.php';
	include_once 'config/database.php';
	include_once 'config/functions.php';
	
	//object Member
	//echo "include_once 'config/member'.$authenticationmethod.'.php';";
	include_once 'config/member'.$authenticationmethod.'.php';
	$memberclassname="Member$authenticationmethod";
	$member = new $memberclassname($mysql);
	
	if ($member->CheckLogin()){
		$id_user = $member->GetIdUser();
				
	}else{
		$member->RedirectToURL('login.php');
	}
	
?>