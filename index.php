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
		<td class="tableTdTop">Welcome <b><?php echo $member->UserFullName(); ?></b>,
		<br/>
		<br/>
		You are in the "<b>manager</b>" section of Course Builder project!<br/>		
		What you can do:
	
		<ul class="ul_home">		
			<li><b><?php echo $labels['menu_courses']; ?></b> - section to manage courses
				<ul class="ul_home_sl">
				<li><b><?php echo $labels['menu_courses_insert']; ?></b> - section to create a new course</li>
				<li><b><?php echo $labels['menu_courses_edit']; ?></b> - section to edit a course, then to add, edit and delete slides to the course</li>
				</ul>
			</li>	
		</ul>
		
		<?php 
		if ($member->IsAdministrator()){
		?>
		<ul class="ul_home">		
			<li><b><?php echo $labels['menu_users']; ?></b> - section to manage users
				<ul class="ul_home_sl">
				<li><b><?php echo $labels['menu_users_insert']; ?></b> - section to create a new user</li>
				<li><b><?php echo $labels['menu_users_edit']; ?></b> - section to edit the user</li>
				<li><b><?php echo $labels['menu_users_delete']; ?></b> - section to delete the user</li>
				</ul>
			</li>	
		</ul>
		
		<?php 
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