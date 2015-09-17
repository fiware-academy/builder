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
	include_once 'config/audio.php';
		
	$msg_error = "";
	
	if (isset($_POST['id_course'])){
		$id_course = $_POST['id_course'];
	}else{ 
		header("location: edit_course.php");
		exit();
	}		
	
	if (isset($_POST['add_slide']) && isset($_POST['id_course'])){
		$id_course = $_POST['id_course'];
		$courses_dir = UPLOAD_DIR_ROOT . $id_course . "/";
		
		if (!file_exists($courses_dir)) {
	    	mkdir($courses_dir, 0777, true);
		}
		
		$upload_jpg = false;
		$randomNumber = getRandomNumber();
		
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
				$upload_jpg = true;
				
			}else{				
				$upload_jpg = false;
				$msg_error = "Please use a jpeg file for a JPG Image field";
			}		
		}else{
			$uploads = false;
			$msg_error = "The JPG Image field is required";
		}
		
		//upload audio file
		$upload_mp3 = false;
		
		if(trim($_FILES["audio_file"]["name"]) != ""){//not required
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
				}else{ 
					die("Problems in uploading process for " . $_FILES["image_file"]["name"] . ' file');
				} 
				$upload_mp3 = true;				
			}else{
				$upload_mp3 = false;
				$msg_error = "The Audio MP3 field is required";
			}			
		}
		
		//create audio via TTS
		$tts_mp3 = false;
		
		if (!$upload_mp3){//set the TTS
			$name = prepareForSQL($_POST['name']);
			$tts = $_POST['tts'];
			
			if (!empty($name) && !empty($tts)){
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

					//remove audio file
					if(file_exists($courses_dir .$randomNumber .'.wav')) {
						unlink($courses_dir .$randomNumber .'.wav');
					}
					
					$getID3 = new getID3;
					$mixinfo = $getID3->analyze($path);
					getid3_lib::CopyTagsToComments($mixinfo);
					$play_time_secs = round($mixinfo['playtime_seconds'],2);
					
					$cuepoint = $play_time_secs;
					$text = prepareForSQL($tts);
					$sql = "INSERT INTO slides (name, image, audio, text, cuepoint, slide_order, id_course) VALUES ('$name','$image','$audio', '$text', $cuepoint, '$order', '$id_course')";
					$result = $mysql->query($sql);
					$tts_mp3 = true;
					
				}else{//error
					$msg_error = "Error in the TTS functionality : Error_code $conversion";
				}	
			}else{
				$msg_error = "The (*) fields are required";
			}	
		}else{//set info into DB
			$name = prepareForSQL($_POST['name']);
			if (!empty($name)){				
			
				$image = $randomNumber .'.jpg';
				$audio = $randomNumber .'.mp3';
				$order = get_next_order($id_course) + 1;
				
				$path = $courses_dir . $audio;
				$getID3 = new getID3;
				$mixinfo = $getID3->analyze($path);
				getid3_lib::CopyTagsToComments($mixinfo);
				$play_time_secs = round($mixinfo['playtime_seconds'],2);
				
				$cuepoint = $play_time_secs;
				$sql = "INSERT INTO slides (name, image, audio, cuepoint, slide_order, id_course) VALUES ('$name','$image','$audio', $cuepoint, '$order', '$id_course')";
				$result = $mysql->query($sql);
			}else{
				$msg_error = "The Name field is required";
			}
		}
		
		if ($upload_jpg && ($upload_mp3 || $tts_mp3)){
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
		<td class="tableTdTop"><b><?php echo $labels['menu_courses']; ?> &raquo; <?php echo $labels['menu_courses_edit']; ?> &raquo; <?php echo $labels['menu_courses_add_slides']; ?></b>
		<br/>
		<br/>
		
		Add slide to course
		<form method="post" action="" ENCTYPE="multipart/form-data">
			<table class="tableForm">
			  <tr>
				<td width="180"><b>Name (*):</b></td><td><input type="text" name="name" size="100" class="input"></td>
			  </tr>
			  <tr>
				<td><b>JPG Image [670x500 px] (*):</b></td><td><input name="image_file" type="file" size="35" class="input"></td>
			  </tr>	
			  <tr>
				<td><b>Audio MP3 or Text (*):</b></td><td><input name="audio_file" type="file" size="35" class="input"></td>
			  </tr>
			  <tr>
			  	<td><b></b></td><td><textarea name="tts" id="textarea" class="textarea"></textarea></td>
			  </tr>
			  <tr>
				<td colspan="2" align=center>
					<input type="Submit" name="mod" value="<?php echo $labels['btn_add_slide']; ?>" class="submit">
				</td>
			  </tr>
			</table>
			<input type="hidden" name="add_slide" value="add_slide">
			<input type="hidden" name="id_course" value="<?php echo $id_course; ?>">
		</form>
		
		<form method="post" action="modify_course.php?id=<?php echo $id_course; ?>">
			<table class="tableForm">
			  <tr>	
				<td align="center"><input type="submit" name="back" value="<?php echo $labels['btn_back']; ?>" class="submit"></td>
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