<div class="navigatore">
	<table style="width: 100%">
	   <tr>
		  <td>&nbsp;
			 <a href="index.php"><?php echo $labels['menu_top_home']; ?></a> |
			 <?php 
			 	if (!$member->IsLoggedInForge()) {
			 ?> 
			 <a href="change_pwd.php"><?php echo $labels['menu_top_change_pwd']; ?></a> |
			 <?php 
			 	}
			 ?>
			 <?php 
				if ($member->IsAdministrator()){
			 ?>
			 <a href="phpinfo.php"><?php echo $labels['menu_top_phpinfo']; ?></a> | 
			 <?php 
				}
			 ?>
			 
			 <a href="logout.php"><?php echo $labels['menu_top_logout']; ?></a>
		  </td>
		  <td align="right"><b>Last login</b>: <?php echo date("d/m/Y H:i:s", strtotime($member->LastTime())); ?></td>
	   </tr>
	</table>
</div>