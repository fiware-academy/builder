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
	
	
	if (isset($_GET['id']) && is_numeric($_GET['id'])) {
		$idn = $_GET['id'];

		$risultato = $mysql->query("SELECT * FROM users where id_user = $idn");
		if ($riga = $risultato->fetch_assoc()) {
			$first_name = $riga["first_name"];
			$last_name = $riga["last_name"];
			$username = $riga["username"];
			$type = $riga["type"];
		}else{//id doesn't match
			header("location: edit_user.php");
			exit();
		} 			
	}else{ 
		header("location: edit_user.php");
		exit();
	}	
	
	$msg_error = "";
	$msg_notify = "";

	//edit user
	if (isset($_POST['edit'])){	
		$idn = $_POST['id'];
		$first_name = prepareForSQL($_POST['first_name']);
		$last_name = prepareForSQL($_POST['last_name']);
		$username = $_POST['username'];
		$type = $_POST['type'];
		
		if($first_name!="" && $last_name!="" && $username!=""){			
			$sql = "UPDATE users SET first_name='$first_name', last_name='$last_name', username='$username', type='$type'";
			$sql = $sql . " WHERE id_user = $idn" ;
			$result = $mysql->query($sql);
			$msg_notify = "All fields were successfully edited";					
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
		<td class="tableTdTop"><b><?php echo $labels['menu_users']; ?> &raquo; <?php echo $labels['menu_users_edit']; ?></b>
		<br/>
		<br/>

		<form method="post" action="">
			<table class="tableForm">
			  <tr>
				<td width="100"><b>First Name (*):</b></td><td><input type="text" name="first_name" size="50" value="<?php echo $first_name; ?>" class="input"></td>
			  </tr>
			  <tr>	
				<td><b>Last Name (*):</b></td><td><input type="text" name="last_name" size="50" value="<?php echo $last_name; ?>" class="input"></td>
			  </tr>
			  <tr>	
				<td><b>Username (*):</b></td><td><input type="text" name="username" size="50" value="<?php echo $username; ?>" class="input"></td>
			  </tr>
			  <tr>
				<td><b>Role (*):</b></td><td>
					<select name="type" class="select">
						<option value="MANAGER" <?php if ($type=='MANAGER') echo 'selected'; ?>>Manager</option>
						<option value="ADMIN"  <?php if ($type=='ADMIN') echo 'selected'; ?>>Admin</option>
					</select>
					</td>
			  </tr>
			  <tr>
				<td colspan="2" align=center><input type="Submit" name="upt" value="<?php echo $labels['btn_edit']; ?>" class="submit"></td>
			  </tr>
			</table>
			<input type="hidden" name="edit" value="edit">			
			<input type="hidden" name="id" value="<?php echo $idn; ?>">
		</form>
		
		<span>
			<?php
				if (!is_null($msg_error)) {
				   	echo "<p><span class='msg_error'>$msg_error</span></p>";				   
				}
				if (!is_null($msg_notify)) {
				   	echo "<p><span class='msg_notify'>$msg_notify</span></p>";				   
				}
			?>
		</span>
		
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