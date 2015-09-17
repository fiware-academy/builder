<div class="contenitore">
	<div class="titolo"><?php echo $labels['menu_courses']; ?></div>
	<div class="link">
		<a href="insert_course.php"><?php echo $labels['menu_courses_insert']; ?></a>
	</div>
	<div class="link">
		<a href="edit_course.php"><?php echo $labels['menu_courses_edit']; ?></a>
	</div>	
</div>

<?php 

if ($member->IsAdministrator()){

?>
<div class="contenitore">
	<div class="titolo"><?php echo $labels['menu_users']; ?></div>
	<div class="link">
		<a href="insert_user.php"><?php echo $labels['menu_users_insert']; ?></a>
	</div>
	<div class="link">
		<a href="edit_user.php"><?php echo $labels['menu_users_edit']; ?></a>
	</div>
	<div class="link">
		<a href="delete_user.php"><?php echo $labels['menu_users_delete']; ?></a>
	</div>
	<div class="link">
		<a href="view_users.php"><?php echo $labels['menu_users_view']; ?></a>
	</div>	
</div>
<?php 
}
?>