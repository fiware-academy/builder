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
	
	//check to change the password
	if ($member->IsLoggedInForge()) { 
		$member->RedirectToURL('index.php');
	}
	
	$msg_error = "";
	$msg_notify = "";
	$show_registration_form = true;

	//edit user
	if (isset($_POST['edit'])){	
		$idn = $_POST['id'];
		$password = prepareForSQL($_POST['password']);
		$repassword = prepareForSQL($_POST['repassword']);
		
		if($password!="" && $repassword!= ""){	
			if ($password == $repassword){		
				$sql = "UPDATE users SET password='$password'";
				$sql = $sql . " WHERE id_user = $idn" ;
				$result = $mysql->query($sql);
				if ($result)
					$show_registration_form = false;
				$msg_notify = "The password was successfully edited";
			}else{
				$msg_error = "Password and Re-Password fields don't match";
			}					
		}else{
			$msg_error = "All fields are required!";
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
		<td class="tableTdTop"><b><?php echo $labels['menu_top_change_pwd']; ?></b>
		<br/>
		<br/>
		<?php 
		if ($show_registration_form){
		?>
		<form method="post" action="">
			<table class="tableForm">
			  <tr>
				<td><b>Password (*):</b></td><td><input type="password" name="password" size="50" class="input"></td>
			  </tr>
			  <tr>
				<td><b>Re-Password (*):</b></td><td><input type="password" name="repassword" size="50" class="input"></td>
			  </tr>
			  <tr>
				<td colspan="2" align=center><input type="Submit" name="upt" value="<?php echo $labels['btn_edit']; ?>" class="submit"></td>
			  </tr>
			</table>
			<input type="hidden" name="edit" value="edit">			
			<input type="hidden" name="id" value="<?php echo $id_user; ?>">
		</form>
		
		Fields with (*) are required<br/><br/>
		<?php
			if (!is_null($msg_error)) {	
			   	echo "<span class='msg_error'><p>$msg_error</p></span>";
			}
		?>
		
		<?php 
		}else{
			echo "<span class='msg_notify'><p>$msg_notify</p></span>";
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