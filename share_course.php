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
	$permissionArray = getPermissionArray();
	$shareArray = getSharedArray();

	if (isset($_GET['id'])) {
		$idn = $_GET['id'];//id_course		
		if (is_numeric($idn)) {
			//check permission if he is owner
			if (isOwner($idn, $id_user)){			
			
				$query = "SELECT u.*, (case when s.permission is NULL then 'R' else s.permission end) permission, "; 
				$query .= "(case when s.owner is NULL then '0' else s.owner end) owner, ";
				$query .= "(case when s.permission is NULL then 0 else 1 end) share	";
				$query .= "FROM users AS u LEFT JOIN shares AS s ON (s.id_user = u.id_user AND s.id_course = $idn) "; 				
				$query .= "WHERE u.id_user != $id_user ";
				$query .= "ORDER BY username ASC";

				$result = $mysql->query($query);
			
			}else{//no owner
				header("location: modify_course.php?id=$idn");
				exit();
			}
			
		}else{
			header("location: edit_course.php");
			exit();
		}
		
	}else{ 
		header("location: edit_course.php");
		exit();
	}	
	
	
	//save share
	if (isset($_POST['save'])){
		$idn = $_POST['id'];
		
		if (isset($_POST['id_user'])){
		
			$query = "DELETE FROM shares WHERE id_user != '$id_user' AND owner != 1 AND id_course = $idn";
			$res = $mysql->query($query);

			$id_users = array();
			
			$i = 0;
			foreach ($_POST['id_user'] as $user) {
				if (strcmp($_POST['shared'][$i],"Yes") == 0){//if shared is 'Yes'
					$sh = $_POST['permission'][$i];
					$ow = $permissionArray[$_POST['owner'][$i]];
					$id_users[] = "'".$idn."','".$user."', '$sh', '$ow'";
				}
				$i++;
			}
			
			// Now join them together in an SQL syntax
			$query_joined = join('), (', $id_users);
			
			// Now they can safely be used in the query
			if (sizeof($id_users) > 0){
				$query = "INSERT INTO shares (id_course, id_user, permission, owner) VALUES ($query_joined)";
				$res = $mysql->query($query);
			}
		//echo ' - ' .$query;
			header("location: modify_course.php?id=$idn");
			exit();
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
		<td class="tableTdTop"><b><?php echo $labels['menu_courses']; ?> &raquo; <?php echo $labels['menu_courses_edit']; ?>  &raquo; <?php echo $labels['menu_courses_share']; ?></b>
		<br/>
		<br/>
		<form method="post" action="">
			<table class="tableList">
				<tr class="headerTable">	
					<td width="30"></td>
					<td>USERNAME</td>
					<td width="400">NAME</td>
					<td width="100">SHARE</td>
					<td width="100">PERMISSION</td>
				</tr>
			<?php
				$css = "rowEven";
				$count = 0;
	
				while ($row = $result->fetch_assoc()) {
	
					$username = stripslashes ($row["username"]);
					$id_user_temp = stripslashes ($row["id_user"]);
					
					if (($count++ % 2) == 1) {
						$css = "rowOdd";
					}else{
						$css = "rowEven";	
					}
						
					$permission = $row["permission"];
					$owner = $row["owner"];
					$share = $shareArray[$row["share"]];
				
			?>
				<tr class="<?php echo $css; ?>">
					<td align="center"><?php echo $count; ?><input type="hidden" name="owner[]" value="<?php echo $owner; ?>" <?php if ($owner == 1) echo 'disabled="disabled"'; ?>></td>
					<td><?php echo $username; ?><input type="hidden" name="id_user[]" value="<?php echo $id_user_temp; ?>" <?php if ($owner == 1) echo 'disabled="disabled"'; ?>></td>
					<td><?php echo $row['first_name']; ?>&nbsp;<?php echo $row['last_name']; ?></td>
					<td align="center">
						<select name="shared[]" class="select" <?php if ($owner == 1) echo 'disabled="disabled"'; ?>>
							<?php 
							$items = $shareArray;
							for ($i=0; $i<sizeof($items); $i++){
								if ($items[$i] == $share){
									echo "<option selected>".$items[$i]."</option>";
								}else{
									echo "<option>".$items[$i]."</option>";
								}
							}
							?>
						</select>
					</td>
					<td align="center">
						<select name="permission[]" class="select" <?php if ($owner == 1) echo 'disabled="disabled"'; ?>>
							<?php
							$items = $permissionArray;							
							for ($i=0; $i<sizeof($items); $i++){
								if ($items[$i] == $permission){
									echo "<option selected>".$items[$i]."</option>";
								}else{
									echo "<option>".$items[$i]."</option>";
								}
							}
							?>
						</select>
					</td>
				</tr>
					
			<?php
				}
					
			?>
				
			</table>
			
			<table class="tableForm">
			  <tr>	
				<td align="center"><a onclick="javascript:history.back();" class="back no_hover">Back</a> &nbsp; <input type="Submit" name="upt" value="<?php echo $labels['btn_edit']; ?>" class="submit"></td>
			  </tr>
			</table>
			
			<input type="hidden" name="save" value="save">			
			<input type="hidden" name="id" value="<?php echo $idn; ?>">
		</form>
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