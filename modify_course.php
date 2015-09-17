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
	require_once('getid3/getid3.php');
	$upload_max_filesize = ini_get('upload_max_filesize');
	
	$permissionArray = getPermissionArray(); 
	
	if (isset($_GET['id'])) {
		$idn = $_GET['id'];
		
		$perms = getShareInfo($idn, $id_user);
		
		$permission_global = $perms[0];
		
		$query = "SELECT c.id_course, c.title, c.created, c.full_name, s.owner FROM courses AS c INNER JOIN shares AS s on c.id_course= s.id_course INNER JOIN users AS u on u.id_user = s.id_user ";
		$query .= "WHERE c.id_course = $idn ";
		$query .= "AND s.id_user = $id_user";

		$risultato = $mysql->query($query);
		if ($riga = $risultato->fetch_assoc()) {
			$title = $riga["title"];
			$id_course = $riga["id_course"];
			$owner_full_name = $riga["full_name"];
			$owner =  $riga["owner"];
		}else if ($member->IsAdministrator()) {//if admin - admin user can be display all courses
			$query = "SELECT c.id_course, c.title, c.created, c.full_name FROM courses AS c INNER JOIN shares AS s on c.id_course= s.id_course INNER JOIN users AS u on u.id_user = s.id_user ";
			$query .= "WHERE c.id_course = $idn ";
			$risultato = $mysql->query($query);
			if ($riga = $risultato->fetch_assoc()) {
				$title = $riga["title"];
				$id_course = $riga["id_course"];
				$owner_full_name = $riga["full_name"];
				$owner =  0; //default
			}
		}else {	//id doesn't match
			header("location: edit_course.php");
			exit();
		} 			
	}else{ 
		header("location: edit_course.php");
		exit();
	}	
	
	//move slides
	if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['id_slide'])) {
		//check permission
		if (!($permission_global == $permissionArray[0])) {
			$action = $_GET['action'];
			$idn = $_GET['id'];
			$id_slide = $_GET['id_slide'];
			if (($action == "up") && (is_numeric($idn)) && (is_numeric($id_slide))) {
				move_slide_up($idn, $id_slide);
			}
			if (($action == "down") && (is_numeric($idn)) && (is_numeric($id_slide))) {
				move_slide_down($idn, $id_slide);
			}
			if (($action == "delete") && (is_numeric($idn)) && (is_numeric($id_slide))) {
				remove_slide($idn, $id_slide);
			}
			header("location: modify_course.php?id=$idn");
		}
	}

	
	//query -> get all slides
	$query = "SELECT * FROM slides WHERE id_course = $idn ORDER BY slide_order ASC";	
	$slides = $mysql->query($query);
		
	$msg_error = "";
	$msg_notify = "";

	//save course
	if (isset($_POST['save'])){	
		$idn = $_POST['id'];
				
		if (isset($_SESSION['save_course_' .$idn])){
			$_SESSION['save_course_' .$idn] = NULL;
		
		}else{
			
			$title = prepareForSQL($_POST['title']);
			
			if($title!=""){			
				$sql = "UPDATE courses SET title='$title'";
				$sql = $sql . " WHERE id_course = $idn" ;
				$result = $mysql->query($sql);
				$msg_notify = "All fields were successfully saved";	
				$_SESSION['save_course_' .$idn] = "save_course";
			}else{
				$msg_error = "The field 'Title' is required!";
			}
		}		
	}
	
	//upload PDF file
	if (isset($_POST['upload_pdf'])){	
		$error = $_FILES['pdf_file']['error'];
		if(trim($_FILES["pdf_file"]["name"]) != ""){
			$type = $_FILES["pdf_file"]["type"];
			$path_file = UPLOAD_DIR_ROOT . $id_course . "/file.pdf";
				//TODO check the right way to allow to PDF file
				if ($type == "application/pdf" || $type == "text/pdf" || $type == "application/x-unknown" || $type == "application/binary" || $type == "binary/octet-stream"){								
						if(@is_uploaded_file($_FILES["pdf_file"]["tmp_name"])) { 
							@move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $path_file) 
							or die('Impossible to move the file, please check the directory or permission which you are uploading the file'); 	
							$msg_notify = "The 'PDF File' has been uploaded with successful";								
						} else { 
							$msg_error("There is a problem in file uploading"); 
						} 								
				}else{				
					$msg_error = "Please, you must upload a PDF file.";
				}	

		}else{
			$msg_error = "The field 'PDF File' is required!";
		}	
	}else if (isset($_GET['upload_pdf'])) {
		$msg_error = "PDF File size must be less than $max_upload_size";
	}	
	
	//preview
	if (isset($_POST['preview'])){
		$path_preview = '/preview/';
		$source = 'package';
		
		$destination = 'uploads/' .$id_course .$path_preview;
		
		//remove di old preview directory and all contents
		deleteDirectory($destination);
		
		if (CopyFiles($source, $destination)){
			
			$cuepoint_filename = "uploads/" .$id_course .$path_preview ."/cuepoints.xml";
			//create cuepoint.xml file
			$doc = new DOMDocument('1.0', 'utf-8');
			$ele = $doc->createElement( 'texts' );
			$doc->appendChild( $ele );
			$ele->setAttribute('title', $title);
			$cuepoint_time = 0;
				
			$file_audio = "uploads/".$id_course. $path_preview. "/audio.mp3";
				
			if(!file_exists($file_audio)){
				$fp = fopen($file_audio,"w");
				fwrite($fp,"0");
				fclose($fp);
			}
			$time = 0;
			$FilenamesIn = array();
			$query = "SELECT * FROM slides WHERE id_course = $idn ORDER BY slide_order ASC";
			$slides_preview = $mysql->query($query);
			
			while ($row = $slides_preview->fetch_assoc()) {
			
				//add images
				$filename = "uploads/" .$id_course .'/' .$row["image"];
				$localname = "uploads/" .$id_course .$path_preview ."slide/".$row["slide_order"] .".jpg";
				copy($filename, $localname);
			
				//add cuepoint for each slide
				$e = $doc->createElement( 'text' );
				$e->setAttribute('cuepoint', $cuepoint_time);
				$e->setAttribute('name', $row["name"]);
				$ele->appendChild($e);
				 
				$cuepoint_time = $cuepoint_time + $row["cuepoint"] + $time;
				$audio = "uploads/" .$id_course .'/' .$row["audio"];;
				array_push($FilenamesIn, $audio);
			}
				
			$ele->setAttribute('time', $cuepoint_time);
			$doc->formatOutput = true;
			$doc->save($cuepoint_filename);
				
			//add audio to ZIP
			CombineMultipleMP3sTo($file_audio, $FilenamesIn);
				
			//add PDF
			$pdf_filename = "uploads/" .$id_course ."/file.pdf";
			$pdf_filename_dst = "uploads/" .$id_course .$path_preview ."/print/file.pdf";
			copy($pdf_filename, $pdf_filename_dst);
			
			//header("location: uploads/" .$id_course .$path_preview ."index.html");
			//redirect to another tab 
			print 
			"<script>
                 window.open('uploads/" .$id_course .$path_preview ."index.html', '_newtab');
             </script>";
		}
		
	}
	
	//create packager for downloading
	if (isset($_POST['create_download'])){
		$source = 'package';
		$title = $_POST['title'];
		$destination = clean($title) . '.zip';
		
		if (Zip($source, $destination)){
								
			$cuepoint_filename = "uploads/" .$id_course ."/cuepoints.xml";
			//create cuepoint.xml file
			$doc = new DOMDocument('1.0', 'utf-8');
		    $ele = $doc->createElement( 'texts' );
		    $doc->appendChild( $ele );
	    	$ele->setAttribute('title', $title);
			$cuepoint_time = 0;
			
			$file_audio = "uploads/".$id_course."/audio.mp3";	
			
			if(!file_exists($file_audio)){
				$fp = fopen($file_audio,"w"); 
			    fwrite($fp,"0"); 
			    fclose($fp); 
			}
			$time = 0;			
			$FilenamesIn = array();
	    	
			while ($row = $slides->fetch_assoc()) {
				
				//add images to ZIP
				$filename = "uploads/" .$id_course .'/' .$row["image"];
				$localname = "slide/".$row["slide_order"] .".jpg";				
				addFileToZip($destination, $filename, $localname);				
				
				//add cuepoint for each slide
				$e = $doc->createElement( 'text' );
		    	$e->setAttribute('cuepoint', $cuepoint_time);
		    	$e->setAttribute('name', $row["name"]);
		    	$ele->appendChild($e);
		    	
		    	$cuepoint_time = $cuepoint_time + $row["cuepoint"] + $time;		    	
		    	$audio = "uploads/" .$id_course .'/' .$row["audio"];;
		    	array_push($FilenamesIn, $audio);
			}
			
	    	$ele->setAttribute('time', $cuepoint_time);
			$doc->formatOutput = true;
		    $doc->save($cuepoint_filename);		    
			
		    //add cuepoint file to ZIP
			addFileToZip($destination, $cuepoint_filename, "cuepoints.xml");
			
			//add audio to ZIP
			CombineMultipleMP3sTo($file_audio, $FilenamesIn);
			addFileToZip($destination, $file_audio, "audio.mp3");
			
			//add PDF to ZIP
			$pdf_filename = "uploads/" .$id_course ."/file.pdf";
			addFileToZip($destination, $pdf_filename, "print/file.pdf");

			// add imsmanifest to ZIP
			$imsmanifest_filename = createImsmanifest($id_course, $title);
			addFileToZip($destination, $imsmanifest_filename, "imsmanifest.xml");

			header('Content-type: application/zip'); 
			header('Content-disposition: attachment; filename='.$destination);
			header('Content-Length: ' . filesize($destination));
			
			ob_clean();
		    flush();
			if (readfile($destination)){
				unlink($destination);
			}
	
		}else{
			echo 'Please, change your permission with chmod 777';
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
		<td class="tableTdTop"><b><?php echo $labels['menu_courses']; ?> &raquo; <?php echo $labels['menu_courses_edit']; ?></b>
		<br/>
		<br/>
		<?php 
		
			$pdf_file = "uploads/" . $id_course . "/file.pdf";	
			$full_pdf_path = UPLOAD_DIR_ROOT . $id_course . "/file.pdf";
			
			$mode = 'save';
			if (isset($_SESSION['save_course_' .$idn])){
				$mode = 'edit';
			}
		?>
		<form method="post" action="">
			<table class="tableForm border">
			  <tr>
				<td width="150"><b>Title (*):</b></td>
				<td colspan="2">
				<?php 
				if ($mode == 'edit' || ($permission_global == $permissionArray[0])){
				?>
					<b><?php echo $title; ?></b>
				<?php
				}else{
				?>
					<input type="text" name="title" size="100" value="<?php echo $title; ?>" class="input">
				<?php
				} 
				?>
				</td>
			  </tr>
			  <tr>
				<td><b>Owner:</b></td>
				<td colspan="2"><b><?php echo $owner_full_name; ?></b></td>
			  </tr>		
			  <tr>
				<td><b>Share with:</b></td>
				<td>
				<?php 
				$query = "SELECT u.first_name, u.last_name, s.permission, s.owner FROM users AS u INNER JOIN shares as s on u.id_user = s.id_user WHERE s.id_course = $id_course AND s.owner != 1";
				$users = $mysql->query($query);
				
				$users_list = null;
					
				while ($row = $users->fetch_assoc()) {
					$users_list .= "<b>" .$row['first_name'] . " " .$row['last_name'] ."</b> [" .$row['permission'] ."] - ";
				}
				
				if (!is_null($users_list)) 
					echo substr($users_list, 0, strlen($users_list)-3);
				else 
					echo "None";				
				?>						
				</td>
				<td width="20">
				<?php 
				if ($owner == 1) {//if owner then he can share with other users
				?>
					<a href='share_course.php?id=<?php echo $id_course; ?>'><img src='images/share.gif' /></a>
				<?php 	
				}
				?>
				</td>
			  </tr>
			  <?php 
			  if (!($permission_global == $permissionArray[0])){
			  ?>
			  <tr>
				<td colspan="3" align="center"><input type="submit" name="mod" value="<?php echo $labels['btn_'.$mode]; ?>" class="submit"></td>
			  </tr>
			  <?php 
			  }
			  ?>
			</table>
			<input type="hidden" name="save" value="save">			
			<input type="hidden" name="id" value="<?php echo $idn; ?>">
		</form>

		<form method="post" action="" ENCTYPE="multipart/form-data">
			<table class="tableForm border">
			  <tr>
				<td width="180"><b>PDF file [Max size <?php echo $upload_max_filesize; ?>] (*):</b></td>
				<td width="650">Last update <b><?php echo date("j/m/Y H:i:s",filemtime($full_pdf_path)); ?></b> | 
				Size <b><?php echo formatSizeUnits(filesize($full_pdf_path)); ?></b> |
				Download PDF <a href='<?php echo $pdf_file;?>' target="_blank"><img src="images/pdf.gif" /></a> 
				&nbsp;&nbsp;&nbsp;&nbsp; 
				<?php 
				if (!($permission_global == $permissionArray[0])){
				?>
				<input name="pdf_file" type="file" size="35" class="input">
				<?php 
				}
				?>
				</td>
			  </tr>
			  <?php 
			  if (!($permission_global == $permissionArray[0])){
			  ?>
			  <tr>	
				<td colspan="2" align="center"><input type="submit" name="upt" value="<?php echo $labels['btn_update_pdf']; ?>" class="submit"></td>
			  </tr>
			  <?php 
			  }
			  ?>
			</table>
			<input type="hidden" name="upload_pdf" value="upload_pdf">			
			<input type="hidden" name="id" value="<?php echo $idn; ?>">
		</form>
		
		<form method="post" action="edit_course.php">
			<table class="tableForm">
			  <tr>	
				<td align="center"><input type="submit" name="back" value="<?php echo $labels['btn_canc']; ?>" class="submit"></td>
			  </tr>
			</table>
		</form>
		
		<span>
			<?php
				if (!is_null($msg_error)) {
				   	echo "<span class='msg_error'>$msg_error</span>";				   
				}
				if (!is_null($msg_notify)) {
				   	echo "<span class='msg_notify'>$msg_notify</span>";				   
				}
			?>
		</span>
			
		<p><b>Slides attached to this course</b></p>
		
			<table class="tableList">
				<tr class="headerTable">
					<td width="40">Order</td><td>Name</td>
					<td width="100">Image [size]</td><td width="100">Audio [sec]</td>
					<td width="40">Move</td><td width="40">Edit</td><td width="40">Delete</td>				
				</tr>
		<?php 
			$num_slides = get_total_slide($id_course);
			$count = 0;
			$confirm = 'return confirm("Are you sure?");';
			
			while ($row = $slides->fetch_assoc()) {
				$name = $row["name"];
				$image = $row["image"];	
				$slide_order = $row["slide_order"];	
				$id_slide = $row["id_slide"];	
				$audio = $row["audio"];	
				$full_image_path = UPLOAD_DIR_ROOT . $id_course . "/" .$image;
				$image_file = "uploads/" . $id_course . "/" .$image;	
				$audio_file = "uploads/" . $id_course . "/" .$audio;

				//set default all images disabled
				$up = "<img src='images/up_disabled.gif' />";
				$down = "<img src='images/down_disabled.gif' />";
				$edit = "<img src='images/edit_disabled.gif' />";
				$delete = "<img src='images/delete_disabled.gif' />";
				
				if (!($permission_global == $permissionArray[0])){
					$up = "<a href='?id=$id_course&action=up&id_slide=$id_slide'><img src='images/up.gif' /></a>";
					$down = "<a href='?id=$id_course&action=down&id_slide=$id_slide'><img src='images/down.gif' /></a>";
					$edit = "<a href='modify_slide.php?id_course=$id_course&id_slide=$id_slide'><img src='images/edit.gif' /></a>";
					$delete = "<a href='?id=$id_course&action=delete&id_slide=$id_slide' onclick='$confirm'><img src='images/delete.gif' /></a>";
				}
		?>
				<tr class="rowEven">
					<td align="center"><?php echo $slide_order; ?></td><td><?php echo $name; ?></td>
					<td><a href='<?php echo $image_file;?>' target="_blank"><img src="images/image.gif" /></a> [<?php echo formatSizeUnits(filesize($full_image_path)); ?>]</td>
					<td><a href='<?php echo $audio_file;?>' target="_blank"><img src="images/audio.gif" /></a> [<?php echo $row["cuepoint"]?>]</td>
					<td align="center"><?php if ($count > 0) echo $up; ?> <?php if ($count < $num_slides - 1) echo $down; ?></td>
					<td align="center"><?php echo $edit; ?></td><td align="center"><?php echo $delete; ?></td>
				</tr>
		<?php
				$count++; 
			}

			?>  
			</table>
			<?php 
			$disabled = '';
			$cssdisabled = '';
			if ($num_slides == '0'){
				$disabled = 'disabled="disabled"';
				$cssdisabled = ' disabled';
			}
			
			?>
			<table class="tableCenter">
				<tr>
				<?php 
				if (!($permission_global == $permissionArray[0])){
				?>
					<td>
						<form method="post" action="add_slide.php">
							<input type="hidden" name="id_course" value="<?php echo $id_course; ?>">
							<input type="Submit" name="mod" value="<?php echo $labels['btn_add_slide']; ?>" class="submit">
						</form>
					</td>
				<?php 
				}
				?>
					<td>
						<form method="post" action="">
							<input type="hidden" name="id_course" value="<?php echo $id_course; ?>">
							<input type="Submit" name="preview" value="<?php echo $labels['btn_preview']; ?>" class="submit<?php echo $cssdisabled;?>" <?php echo $disabled;?>>
						</form>
					</td>
					<td>
						<form method="post" action="">
							<input type="hidden" name="title" value="<?php echo $title; ?>">
							<input type="Submit" name="create_download" value="<?php echo $labels['btn_download_zip']; ?>" class="submit<?php echo $cssdisabled;?>" <?php echo $disabled;?>>
						</form>
					</td>
				</tr>
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