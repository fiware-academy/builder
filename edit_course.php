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
	
	if (isset($_GET['id'])) {//delete
		$idn = $_GET['id'];
		//check if user can delete it!
		if (isOwner($idn, $id_user)) //if he is owner he can delete
			delete_course($idn);
	}

	$query = "SELECT c.id_course, c.title, c.created, c.full_name, s.owner, s.permission, 1 as share FROM courses AS c INNER JOIN shares AS s on c.id_course= s.id_course INNER JOIN users AS u on u.id_user = s.id_user ";
	$query .= "WHERE s.id_user = $id_user ";
	
	if ($member->IsAdministrator()){
		$query .= "UNION SELECT c.id_course, c.title, c.created, c.full_name, 0, 'R', 0 FROM courses AS c WHERE c.id_course NOT IN (SELECT c.id_course FROM courses AS c INNER JOIN shares AS s on c.id_course= s.id_course INNER JOIN users AS u on u.id_user = s.id_user WHERE s.id_user = $id_user) ";
	}
	
	$query .= "ORDER BY title ASC";

	$result = $mysql->query($query);	
	
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
		<td class="tableTdTop"><b><?php echo $labels['menu_courses']; ?> &raquo; <?php echo $labels['menu_courses_edit']; ?></b>
		<br/>
		<br/>
		<table class="tableList">
			<tr class="headerTable">	
				<td width="30"></td>
				<td>TITLE</td>
				<td width="150">CREATED</td>
				<td width="200">AUTHOR</td>
				<td width="90">PERMISSION</td>
		<?php 
		if ($member->IsAdministrator()){
			echo "<td width='40'>SHARE</td>";
		}
		?>
				<td width="60">DELETE</td>
			</tr>
		<?php
			$css = "rowEven";
			$count = 0;
			$confirm = 'return confirm("You are going to delete this course, are you sure?");';

			while ($row = $result->fetch_assoc()) {

				$title = stripslashes ($row["title"]);
				if (($count++ % 2) == 1) {
					$css = "rowOdd";
				}else{
					$css = "rowEven";	
				}	
				
				$id_course = $row["id_course"];
				$permission = $row["permission"];
				$owner = $row["owner"];
				
				if ($owner == 1) {
					$delete = "<a href='?id=$id_course' onclick='$confirm'><img src='images/delete.gif' /></a>";
				}else{						
					$delete = "<img src='images/delete_disabled.gif' />";
				}
		?>
			<tr class="<?php echo $css; ?>">
				<td align="center"><?php echo $count; ?></td>
				<td><a href="modify_course.php?id=<?php echo $id_course;?>"><?php echo $title; ?></a></td>
				<td><?php echo date("d/m/Y H:i:s", strtotime($row["created"])); ?></td>
				<td><?php echo $row['full_name']; ?></td>
				<td align="center"><?php echo $permission; ?></td>
		<?php 
				if ($member->IsAdministrator()){
					echo '<td align="center">'; 
					if ($row['share'] == "1") echo "<img src='images/check.gif' />";
					echo '</td>';
				}
		?>
				<td align="center"><?php echo $delete; ?></td>
			</tr>
		<?php
			}
				
		?>
		
		</table>

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