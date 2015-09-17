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
	$msg_notify = "";

	if (isset($_GET['action']) && isset($_GET['id'])) {
		$action = $_GET['action'];
		$idn = $_GET['id'];
		if (($action == "delete") && (is_numeric($idn))) {
			$result = $mysql->query("DELETE FROM users WHERE id_user = $idn");
			if($result) {
				$msg_notify = "User deleted with successful";
			}else{
				$msg_error = "User cannot be deleted!";
			}
			
		}	
		
	}
	
	$result = $mysql->query("SELECT * FROM users WHERE login_forge = '0' ORDER BY username ASC");	
	
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
		<td class="tableTdTop"><b><?php echo $labels['menu_users']; ?> &raquo; <?php echo $labels['menu_users_delete']; ?></b>
		<br/>
		<br/>
		<table class="tableList">
			<tr class="headerTable">	
				<td width="30"></td>
				<td>USERNAME</td>
				<td width="400">NAME</td>
				<td width="100">ROLE</td>
			</tr>
		<?php
			$css = "rowEven";
			$count = 0;
			$confirm = 'return confirm("You are going to delete this user, are you sure?");';

			while ($row = $result->fetch_assoc()) {

				$username = stripslashes ($row["username"]);
				if (($count++ % 2) == 1) {
					$css = "rowOdd";
				}else{
					$css = "rowEven";	
				}	
				
		?>
			<tr class="<?php echo $css; ?>">
				<td align="center"><?php echo $count; ?></td>
				<td><a href="?action=delete&id=<?php echo $row['id_user'];?>" onclick='<?php echo $confirm; ?>'><?php echo $username; ?></a></td>
				<td><?php echo $row['first_name']; ?>&nbsp;<?php echo $row['last_name']; ?></td>
				<td><?php echo $row["type"]; ?></td>
			</tr>
		<?php
			}
				
		?>
		
		</table>
		
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