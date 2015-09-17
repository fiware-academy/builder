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
		
	$msg_error = "";
	$show_registration_form = true;		
		
	if (isset($_POST['insert'])){		
		
		$title = prepareForSQL($_POST['title']);
		
		if($title!=""){
			setlocale(LC_ALL, 'it_IT@euro', 'it_IT', 'it'); 
			$data = date("Y-n-j G:i:s");
			$id_user = $member->GetIdUser();
			$full_name = $member->UserFullName();
			
			start_transaction();
			$sql = "INSERT INTO courses (title, created, full_name) VALUES ('$title','$data', '$full_name')";

			$result = $mysql->query($sql);		
			$uploads = false;
			
			if ($result){			
				$id_course = $mysql->insert_id;	

				$sql = "INSERT INTO shares (id_course, id_user, permission, owner) VALUES ('$id_course','$id_user', 'RW', '1')";				
				$result = $mysql->query($sql);
				
				$courses_dir = UPLOAD_DIR_ROOT . $id_course . "/";
				if (!file_exists($courses_dir)) {
			    	mkdir($courses_dir, 0777, true);
				}
				
				//upload PDF file
				if(trim($_FILES["pdf_file"]["name"]) != ""){
					$type = $_FILES["pdf_file"]["type"];
					$path_file = $courses_dir .'file.pdf';
					//TODO check the right way to allow to PDF file
					if ($type == "application/pdf" || $type == "text/pdf" || $type == "application/x-unknown" || $type == "application/binary" || $type == "binary/octet-stream"){
						if(@is_uploaded_file($_FILES["pdf_file"]["tmp_name"])) { 
							@move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $path_file) 
							or die('Impossible to move the file, please check the directory or permission which you are uploading the file'); 									
						} else { 
							die("Problems in uploading process for " . $_FILES["image_file"]["name"] . ' file'); 
						} 
						$uploads = true;
						$show_registration_form = false;
						commit();
						
					}else{				
						$msg_error = "You must upload a PDF file. The type of your file is " .$type;
						rollback();	
						if (file_exists($courses_dir)) {
							rmdir($courses_dir);
						}
					}		
				}else{//copy file pdf "NO PDF FILE AVAILABLE"
					$destination = $courses_dir .'file.pdf';
					$copy_file = copy("no_pdf_file.pdf", $destination);
					if ($copy_file){
						$uploads = true;
						$show_registration_form = false;
						commit();
					}else{
						$msg_error = "No PDF file found.";
						rollback();
						if (file_exists($courses_dir)) {
							rmdir($courses_dir);
						}
					}
				}					
			
			}else{	
				$msg_error = "The course hasn't been inserted. Maybe the course with your title already exists.";
				rollback();
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
		<td class="tableTdTop"><b><?php echo $labels['menu_courses']; ?> &raquo; <?php echo $labels['menu_courses_insert']; ?></b>
		<br/>
		<br/>
		<?php 
		if ($show_registration_form){
		?>
		<form method="post" action="" ENCTYPE="multipart/form-data">
			<table class="tableForm">
			  <tr>
				<td width="150"><b>Title (*):</b></td><td><input type="Text" name="title" size="100" class="input"></td>
			  </tr>  
			  <tr>
				<td><b>PDF file:</b></td><td><input name="pdf_file" type="file" size="35" class="input"></td>
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
			echo "<span class='msg_notify'><p>The course was successfully created</p></span>";
			echo "Please, use the '<b>" .$labels['menu_courses_edit'] ."</b>' section to edit and add slides.";  
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