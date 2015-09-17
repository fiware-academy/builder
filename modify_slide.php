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
	include_once 'config/audio.php';
	require_once('getid3/getid3.php');
				
	$msg_error = "";
	$randomNumber = null;
	$permissionArray = getPermissionArray();
	
	if (isset($_GET['id_course'])  && is_numeric($_GET['id_course']) && isset($_GET['id_slide']) && is_numeric($_GET['id_slide'])) {//retrieve info 
		$id_course = $_GET['id_course'];
		$id_slide = $_GET['id_slide'];
		
		//check permission
		$perms = getShareInfo($id_course, $id_user);
		$permission_global = $perms[0];
		if (!($permission_global == $permissionArray[0])) {
		
			$risultato = $mysql->query("SELECT * FROM slides where id_course = $id_course AND id_slide = $id_slide");
			if ($riga = $risultato->fetch_assoc()) {
				$name = $riga["name"];
				$image = $riga["image"];
				$audio = $riga["audio"];
				$text = $riga["text"];
				$randomNumber = preg_replace('/\\.[^.\\s]{3,4}$/', '', $image);
			}else{//id doesn't match
				header("location: edit_course.php");
				exit();
			}
		}else{
			header("location: modify_course.php?id=$id_course");
			exit();
		}
	}else{ 
		header("location: edit_course.php");
		exit();
	}
	
	if (isset($_POST['edit_slide'])){
		$courses_dir = UPLOAD_DIR_ROOT . $id_course . "/";
		$uploads = true;
		
		//upload image JPG
		if(trim($_FILES["image_file"]["name"]) != ""){
			$type = $_FILES["image_file"]["type"];
			$path_file = $courses_dir .$randomNumber .'.jpg';
			
			if ($type == "image/jpeg"){
				if(@is_uploaded_file($_FILES["image_file"]["tmp_name"])) { 
					@move_uploaded_file($_FILES["image_file"]["tmp_name"], $path_file) 
					or die('Impossible to move the file, please check the directory or permission which you are uploading the file'); 									
				} else { 
					die("Problems in uploading process for " . $_FILES["image_file"]["name"] . ' file'); 
				} 
				$uploads = true;
				
			}else{	
				$msg_error = "Error: problem in upload PDF file";
				$uploads = false;	
			}		
		}
		
		//upload AUDIO file MP3
		$upload_mp3 = false;

		if(trim($_FILES["audio_file"]["name"]) != ""){
			$type = $_FILES["audio_file"]["type"];
			$path_file_tmp = $courses_dir .$randomNumber .'_tmp.mp3';
			$path_file = $courses_dir .$randomNumber .'.mp3';
			
			if ($type == "audio/mpeg" || $type == "audio/mp3"){
				if(@is_uploaded_file($_FILES["audio_file"]["tmp_name"])) { 
					@move_uploaded_file($_FILES["audio_file"]["tmp_name"], $path_file_tmp) 
					or die('Impossible to move the file, please check the directory or permission which you are uploading the file'); 	
					$sound = new Audio($path_file_tmp, $randomNumber, $bitrate, $mode, $path_lame);
					$sound->save();
						
					if(file_exists($path_file_tmp)) {//remove tmp file
						unlink($path_file_tmp);
					}								
				} else { 
					die("Problems in uploading process for " . $_FILES["image_file"]["name"] . ' file');
				} 
				$upload_mp3 = true;
				
			}else{				
				$upload_mp3 = false;	
			}	

			$uploads = $uploads && $upload_mp3;
		}

		$name_form = prepareForSQL($_POST['name']);
		
		if (!$upload_mp3){//if not upload MP3 - use Text To Speech			
			$tts = $_POST['tts'];
			
			if (!empty($name_form) && !empty($tts)){
				//create tts with docker
				$tts_silent = '<?xml version="1.0" encoding="UTF-8" ?><speak version="1.0" xmlns="http://www.w3.org/2001/10/synthesis" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
					xsi:schemaLocation="http://www.w3.org/2001/10/synthesis http://www.w3.org/TR/speech-synthesis/synthesis.xsd" xml:lang="en_US">'
					.$tts .'<break time="'.$pause .'s"/></speak>';
				
				$data = array('input[type]' => 'SSML', 'input[locale]' => 'en-US', 'input[content]' => $tts_silent, 'output[type]' => 'AUDIO'
						, 'output[format]' => 'WAVE_FILE', 'voice[gender]' => 'female', 'voice[name]' => 'cmu-clb-hsmm');
				$content = docker_call_post($url_docker, $data);
				
				file_put_contents($courses_dir .$randomNumber .'.wav', $content);
				
				$image = $randomNumber .'.jpg';
				$audio = $randomNumber .'.wav';
				$order = get_next_order($id_course) + 1;
				
				$path = $courses_dir . $audio;
				
				//convert
				$sound = new Audio($path, $randomNumber, $bitrate, $mode, $path_lame);
				$conversion = $sound->save();
				
				if ($conversion == 0){//ok
					$audio = $randomNumber .'.mp3';
					$path = $courses_dir . $audio;
					
					if(file_exists($courses_dir .$randomNumber .'.wav'))//remove audio file
						unlink($courses_dir .$randomNumber .'.wav');
					
					$getID3 = new getID3;
					$mixinfo = $getID3->analyze($path);
					getid3_lib::CopyTagsToComments($mixinfo);
					$play_time_secs = round($mixinfo['playtime_seconds'],2);
					
					$cuepoint = $play_time_secs;
					$text = prepareForSQL($tts);
					$sql = "UPDATE slides set name = '$name_form', text = '$text', cuepoint = '$cuepoint' WHERE id_slide = $id_slide";
					$result = $mysql->query($sql);
					
				}else{//error
					$msg_error = "Error in the TTS functionality : Error_code $conversion";
				}								
				
			}else{
				$msg_error = "Error in name or text fields";
			}	
		}else{//else - upload MP3
			if (!empty($name_form)){
					
				$image = $randomNumber .'.jpg';
				$audio = $randomNumber .'.mp3';
				$order = get_next_order($id_course) + 1;
			
				$path = $courses_dir . $audio;
				$getID3 = new getID3;
				$mixinfo = $getID3->analyze($path);
				getid3_lib::CopyTagsToComments($mixinfo);
				$play_time_secs = round($mixinfo['playtime_seconds'],2);
			
				$cuepoint = $play_time_secs;
				$sql = "UPDATE slides set name = '$name_form', text = '', cuepoint = '$cuepoint' WHERE id_slide = $id_slide";
				$result = $mysql->query($sql);
			}else{
				$msg_error = "Error in name field";
			}
		}
		
		//only update of "name" 
		if (!empty($name_form) && ($name != $name_form)){
			$sql = "UPDATE slides set name = '$name_form' WHERE id_slide = $id_slide";
			$result = $mysql->query($sql);
		}else{
			$msg_error = "The name field is required";
		}
		
		if (!empty($msg_error)){
			header("location: modify_course.php?id=$id_course");
// 		}else{
// 			$msg_error = "Error: problem in upload file";
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
		<td class="tableTdTop"><b><?php echo $labels['menu_courses']; ?> &raquo; <?php echo $labels['menu_courses_edit']; ?> &raquo; <?php echo $labels['menu_courses_edit_slides']; ?></b>
		<br/>
		<br/>
		Edit slide to course
		<form method="post" action="" ENCTYPE="multipart/form-data">
			<table class="tableForm">
			  <tr>
				<td width="180"><b>Name (*):</b></td><td><input type="text" name="name" size="100" class="input" value="<?php echo $name; ?>"></td>
			  </tr>
			  <tr>
				<td><b>JPG Image [670x500 px]:</b></td><td><img src="<?php echo 'uploads/' . $id_course . '/' .$image; ?>" border="1" width="150"/> &nbsp;&nbsp; <input name="image_file" type="file" size="35" class="input"></td>
			  </tr>
			  <tr>
				<td><b>Audio MP3 or Text:</b></td><td><input name="audio_file" type="file" size="35" class="input"></td>
			  </tr>
			  <tr>
			  	<td><b></b></td><td><textarea name="tts" id="textarea" class="textarea"><?php echo $text; ?></textarea></td>
			  </tr>
			  <tr>
				<td colspan="2" align=center>
					<input type="Submit" name="mod" value="<?php echo $labels['btn_save_slide']; ?>" class="submit">
				</td>
			  </tr>
			</table>
			<input type="hidden" name="edit_slide" value="edit_slide">
			<input type="hidden" name="id_course" value="<?php echo $id_course; ?>">
		</form>
		
		<form method="post" action="modify_course.php?id=<?php echo $id_course; ?>">
			<table class="tableForm">
			  <tr>	
				<td align="center"><input type="submit" name="back" value="<?php echo $labels['btn_canc']; ?>" class="submit"></td>
			  </tr>
			</table>
		</form>
	
		Fields with (*) are required<br/><br/>
		<?php
			if (!is_null($msg_error)) {
			   	echo "<span class='msg_error'><p>$msg_error</p></span>";			   
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