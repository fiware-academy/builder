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
	
	include_once 'config/member'.$authenticationmethod.'.php';
	$memberclassname="Member$authenticationmethod";
	$member = new $memberclassname($mysql);

	if(isset($_POST['submitted'])) {
		if($member->Login()) {
			$member->RedirectToURL('index.php');
		} 
	}
?>

<html>
<?php
	include_once 'page_fragment/head.php';
?>
	<script type='text/javascript' src='scripts/validatorv31.js'></script>
<!-- body -->
<body>

	<!-- menu -->
<?php
	include_once 'page_fragment/logo.php';

?>
	<!-- fine menu -->

	<br> 
	<div class="box">
		<br />
		<div align="center">
			<br /><br />
			<form id='login' action='<?php echo $member->GetSelfScript(); ?>' method='post' accept-charset='UTF-8'>
				<input type='hidden' name='submitted' id='submitted' value='1' />
				<div class="contenitoreSmall">
					<div class="titolo">LOGIN</div>
					<div>
						<table class="tableForm">
							<tr>
								<td colspan="2"></td>
							</tr>
							<tr>
								<td>Username (*):</td>
								<td width="auto"><input type='text' name='username' id='username' value='<?php echo $member->SafeDisplay('username') ?>' maxlength="50" class='input' /></td>
							</tr>
							<tr>
								<td colspan="2"><span id='login_username_errorloc' class='error'></span>
								</td>
							</tr>
							<tr>
								<td>Password (*):</td>
								<td><input type='password' name='password' id='password' maxlength="50" class='input' /></td>
							</tr>
							<tr>
								<td colspan="2"><span id='login_password_errorloc' class='error'></span>
								</td>
							</tr>
							<tr>
								<td colspan="2"><span>(*) mandatory fields</span>
								</td>
							</tr>
							<tr>
								<td colspan="2"><span class='error'><?php echo $member->GetErrorMessage(); ?></span></td>
							</tr>
							<tr align="center">
								<td colspan="2"><input type='submit' name='Submit' value='Enter' class="submit" /></td>
							</tr>
						</table>
					</div>
				</div>
			</form>
			<script type='text/javascript'>
			// <![CDATA[			
				var frmvalidator  = new Validator("login");
				frmvalidator.EnableOnPageErrorDisplay();
				frmvalidator.EnableMsgsTogether();			
				frmvalidator.addValidation("username","req","Please, type your username");				
				frmvalidator.addValidation("password","req","Please, type your password");			
			// ]]>
			</script>
		</div>
	</div>


</body>
<!-- end body -->
<?php 
	include_once 'page_fragment/footer.php';
?>
</html>
