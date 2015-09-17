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

	include_once 'check_user.php';
	
	//check to edit users
	if (!$member->IsAdministrator()) {
		$member->RedirectToURL('index.php');
	}	
		
	$msg_error = "";
	$show_registration_form = true;		
		
	if (isset($_POST['insert'])){		
		
		$first_name = prepareForSQL($_POST['first_name']);
		$last_name = prepareForSQL($_POST['last_name']);
		$username = $_POST['username'];
		$password = $_POST['password'];
		$repassword = $_POST['repassword'];
		$type = $_POST['type'];
		
		if((!is_null($first_name)) && (!is_null($last_name)) && (!is_null($username)) && (!is_null($password))) {
			if ($password == $repassword){
				
				$sql = "INSERT INTO users (username, password, first_name, last_name, type) 
					VALUES ('$username','$password','$first_name', '$last_name', '$type')";

				$result = $mysql->query($sql);
				
				if ($result){
					$show_registration_form = false;
				}else{
					$msg_error = "There is already a user with same username!";
				}
				
			}else{
				$msg_error = "The passwords don't match!";
			}		
		}else{
			$msg_error = "All fields with (*) are required!";
		}	
	}	
	
?>

<html>
<?php 
	include_once 'page_fragment/head.php'; 
?>
<!-- body -->
<body>

<!-- menu_top -->
<?php 
	include_once 'page_fragment/logo.php';
	include_once 'page_fragment/menu_top.php'; 
?>
<!-- fine menu_top -->

<table class="tableTop">
	<tr>
		<td class="tableTdMenu"><?php include 'page_fragment/menu.php'; ?></td>
		<td class="tableTdTop"><b><?php echo $labels['menu_users']; ?> &raquo; <?php echo $labels['menu_users_insert']; ?></b>
		<br/>
		<br/>
		<?php 
		if ($show_registration_form){
		?>
		<form method="post" action="">
			<table class="tableForm">
			  <tr>
				<td width="100"><b>First Name (*):</b></td><td><input type="text" name="first_name" size="50" class="input"></td>
			  </tr>  
			  <tr>
				<td><b>Last Name (*):</b></td><td><input type="text" name="last_name" size="50" class="input"></td>
			  </tr>
			  <tr>
				<td><b>Username (*):</b></td><td><input type="text" name="username" size="50" class="input"></td>
			  </tr>
			  <tr>
				<td><b>Password (*):</b></td><td><input type="password" name="password" size="50" class="input"></td>
			  </tr>
			  <tr>
				<td><b>Re-Password (*):</b></td><td><input type="password" name="repassword" size="50" class="input"></td>
			  </tr>
			  <tr>
				<td><b>Role (*):</b></td><td>
					<select name="type" class="select">
						<option value="MANAGER">Manager</option>
						<option value="ADMIN">Admin</option>
					</select>
					</td>
			  </tr>
			  <tr>
				<td colspan="2" align=center><input type="Submit" name="ins" value="<?php echo $labels['btn_insert']; ?>" class="submit"></td>
			  </tr>
			</table>
			<input type="hidden" name="insert" value="insert">
		</form>
		
	
		Fields with (*) are required<br/><br/>
		
		<span>
		<?php
			if (!is_null($msg_error)) {
			   	echo "<p><span class='msg_error'>$msg_error</span></p>";
			}
		?>
		</span>
		
		<?php 
		}else{
			echo "<span class='msg_notify'><p>The user was successfully created</p></span>";
		}
		?>
		
		</td>
	</tr>
</table>
<br/>

</body>
<!-- end body -->
<?php 
	include_once 'page_fragment/footer.php';
?>
</html>