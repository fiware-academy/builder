<?php

$labels = parse_ini_file("labels.ini");

function prepareForSQL($str){
	global $mysql;
	$str = str_replace('"',"&quot;", $str);
	$str = $mysql->real_escape_string($str);
	return $str;
} 

function getRandomNumber(){	
	return mt_rand(0, 65536);
}

function get_next_order($id_course) {
    global $mysql;
    $result = $mysql->query("SELECT MAX(slide_order) FROM slides WHERE id_course = $id_course");
    $result = $result->fetch_row();
    
    if (!is_null($result[0]))
    	return $result[0];
    else 	
    	return 0;
}

function get_total_slide($id_course) {
    global $mysql;
    $result = $mysql->query("SELECT COUNT(id_slide) FROM slides WHERE id_course = $id_course ORDER BY slide_order ASC");
    $result = $result->fetch_row();
    
    if (!is_null($result[0]))
    	return $result[0];
    else 	
    	return 0;
}

function move_slide_up($id_course, $id_slide) {
	global $mysql;
	$result = $mysql->query("SELECT slide_order FROM slides WHERE id_slide = $id_slide");
	$result = $result->fetch_row();
	$slide_order = $result[0];
	
	$new_slide_order = $slide_order - 1;
	
	$result = $mysql->query("SELECT id_slide FROM slides WHERE id_course = $id_course AND slide_order = $new_slide_order");
	$result = $result->fetch_row();
	$new_id_slide = $result[0];
	
	$result = $mysql->query("UPDATE slides SET slide_order = $new_slide_order WHERE id_slide = $id_slide");
	$result = $mysql->query("UPDATE slides SET slide_order = $slide_order WHERE id_slide = $new_id_slide");
	
	commit();
}

function move_slide_down($id_course, $id_slide) {
	global $mysql;
	$result = $mysql->query("SELECT slide_order FROM slides WHERE id_slide = $id_slide");
	$result = $result->fetch_row();
	$slide_order = $result[0];
	
	$new_slide_order = $slide_order + 1;
	
	$result = $mysql->query("SELECT id_slide FROM slides WHERE id_course = $id_course AND slide_order = $new_slide_order");
	$result = $result->fetch_row();
	$new_id_slide = $result[0];
	
	$result = $mysql->query("UPDATE slides SET slide_order = $new_slide_order WHERE id_slide = $id_slide");
	$result = $mysql->query("UPDATE slides SET slide_order = $slide_order WHERE id_slide = $new_id_slide");
	
	commit();
}

function remove_slide($id_course, $id_slide) {
	global $mysql;
	$result = $mysql->query("SELECT image, audio, slide_order FROM slides WHERE id_slide = $id_slide");
	$result = $result->fetch_row();
	$image = $result[0];
	$audio = $result[1];
	$slide_order = $result[2];

	$ok = $mysql->query("DELETE FROM slides WHERE id_slide = $id_slide");
	
	if($ok) {		
		//update slide_order
		$result = $mysql->query("UPDATE slides SET slide_order = slide_order - 1 WHERE id_course = $id_course AND slide_order > $slide_order");
		
		//remove audio and image
		$file_audio = UPLOAD_DIR_ROOT . $id_course .'/' .$audio;
		if(file_exists($file_audio)) {
        	unlink($file_audio);
        }
		$file_image = UPLOAD_DIR_ROOT . $id_course .'/' .$image;
		if(file_exists($file_image)) {
        	unlink($file_image);
        }
	}
}

function delete_course($id_course) {
	global $mysql;
	$ok = $mysql->query("DELETE FROM courses WHERE id_course = $id_course");
	$rows = $mysql->affected_rows; //rows deleted
	if($ok && $rows > 0) {//remove dir within files
		$dirPath = UPLOAD_DIR_ROOT . $id_course .'/';
		deleteDirectory($dirPath); 
	}
}

function clean($string) {
   $string = str_replace(' ', '_', $string); // Replaces all spaces with underscore.

   return preg_replace('/[^A-Za-z0-9\_-]/', '', $string); // Removes special chars.
}

function formatSizeUnits($bytes) {
   if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
   }elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
   }elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
   }elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
   }elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
   }else {
        $bytes = '0 bytes';
   }

	return $bytes;
}

function addFileToZip($zipFile, $filename, $localname){
	$zip = new ZipArchive;
	if ($zip->open($zipFile) === TRUE) {
		$zip->addFile($filename, $localname);
    	$zip->close();
	}
}

function CopyFiles($source, $destination){
	
	if (!is_dir($destination) === true)	{
		mkdir($destination, 0777, true);
	}
	
	$isWin = isWinOS();
	
	if (is_dir($source) === true) {
		$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
			
		foreach ($files as $file) {
			$file = str_replace('\\', '/', $file);
				
			// Ignore "." and ".." folders
			if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
				continue;
				
			if (is_dir($file) === true)	{
				if (!$isWin)
					$newdir = $destination .str_replace($source . '/', '', $file);
				else 
					$newdir = $destination .str_replace($source . '/', '', '/'.$file);
				
				if (!file_exists($newdir)) {
					mkdir($newdir, 0777, true);
				}
			} else if (is_file($file) === true)	{
				if (!$isWin)
					$newfile = $destination .str_replace($source . '/', '', $file);
				else 
					$newfile = $destination .str_replace($source . '/', '', '/'.$file);
				copy($file, $newfile);
			}
		}
	}
	
	return true;
} 

function Zip($source, $destination, $include_dir = false) {	

	if (!extension_loaded('zip') || !file_exists($source)) {
		return false;
	}

	if (file_exists($destination)) {
		unlink ($destination);
	}

	$zip = new ZipArchive();
	if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
		return false;
	}
	$source = str_replace('\\', '/', realpath($source));

	if (is_dir($source) === true) {
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

		if ($include_dir) {
			$arr = explode("/",$source);
			$maindir = $arr[count($arr)- 1];

			$source = "";
			for ($i=0; $i < count($arr) - 1; $i++) {
				$source .= '/' . $arr[$i];
			}

			$source = substr($source, 1);

			$zip->addEmptyDir($maindir);

		}
		
		$isWin = isWinOS();

		foreach ($files as $file) {
			$file = str_replace('\\', '/', $file);

			// Ignore "." and ".." folders
			if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
			continue;

			if (!$isWin)
				$file = realpath($file); //to comment for win

			if (is_dir($file) === true)	{
				if (!$isWin)
					$zip->addEmptyDir(str_replace($source . '/', '', $file . '/')); //to comment for win
				else
					$zip->addEmptyDir(str_replace($source . '/', '', $file)); //to remove for win
			} else if (is_file($file) === true)	{
				if (!$isWin)
					$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file)); //to comment for win
				else
					$zip->addFromString(str_replace($source . '/', '', '/'.$file), file_get_contents($file)); //to remove for win
			}
		}
	}else if (is_file($source) === true) {
		$zip->addFromString(basename($source), file_get_contents($source));
	}
	
	return $zip->close();
}

function CombineMultipleMP3sTo($FilenameOut, $FilenamesIn) {

	foreach ($FilenamesIn as $nextinputfilename) {
		if (!is_readable($nextinputfilename)) {
			//echo 'Cannot read "'.$nextinputfilename.'"<BR>';
			return false;
		}
	}
	if (!is_writeable($FilenameOut)) {
		//echo 'Cannot write "'.$FilenameOut.'"<BR>';
		return false;
	}

	//require_once('../getid3/getid3.php');
	ob_start();
	if ($fp_output = fopen($FilenameOut, 'wb')) {

		ob_end_clean();
		// Initialize getID3 engine
		$getID3 = new getID3;
		foreach ($FilenamesIn as $nextinputfilename) {

			$CurrentFileInfo = $getID3->analyze($nextinputfilename);
			if ($CurrentFileInfo['fileformat'] == 'mp3') {

				ob_start();
				if ($fp_source = fopen($nextinputfilename, 'rb')) {

					ob_end_clean();
					$CurrentOutputPosition = ftell($fp_output);

					// copy audio data from first file
					fseek($fp_source, $CurrentFileInfo['avdataoffset'], SEEK_SET);
					while (!feof($fp_source) && (ftell($fp_source) < $CurrentFileInfo['avdataend'])) {
						fwrite($fp_output, fread($fp_source, 32768));
					}
					fclose($fp_source);

					// trim post-audio data (if any) copied from first file that we don't need or want
					$EndOfFileOffset = $CurrentOutputPosition + ($CurrentFileInfo['avdataend'] - $CurrentFileInfo['avdataoffset']);
					fseek($fp_output, $EndOfFileOffset, SEEK_SET);
					ftruncate($fp_output, $EndOfFileOffset);

				} else {

					$errormessage = ob_get_contents();
					ob_end_clean();
					//echo 'failed to open '.$nextinputfilename.' for reading';
					fclose($fp_output);
					return false;

				}

			} else {
				//echo $nextinputfilename.' is not MP3 format';
				fclose($fp_output);
				return false;
			}
		}

	} else {

		$errormessage = ob_get_contents();
		ob_end_clean();
		//echo 'failed to open '.$FilenameOut.' for writing';
		return false;

	}

	fclose($fp_output);
	return true;
}


function isWinOS() {
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		//echo 'This is a server using Windows!';
		return true;
	} else {
		//echo 'This is a server not using Windows!';
		return false;
	}
}

function getSharedArray(){
	return array('No','Yes');
}

function getShareInfo($id_course, $id_user){
	global $mysql;
	$result = $mysql->query("SELECT permission, owner FROM shares WHERE id_course = $id_course AND id_user = $id_user");

	$rows = $result->fetch_assoc();
	if($rows > 0)
		return array($rows['permission'],$rows['owner']);
	else //default
		return array('R', 0);
}

function getPermissionArray(){
	return array('R','RW');
}

function isOwner($id_course, $id_user){
	global $mysql;
	$result = $mysql->query("SELECT owner FROM shares WHERE id_course = $id_course AND id_user = $id_user");
	
	$rows = $result->fetch_assoc();
	return $rows['owner'];
}

function deleteDirectory($dirPath) {//remove dir within files
	if (is_dir($dirPath)) {
		$objects = scandir($dirPath);
		foreach ($objects as $object) {
			if ($object != "." && $object !="..") {
				if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
					deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
				} else {
					unlink($dirPath . DIRECTORY_SEPARATOR . $object);
				}
			}
		}
		reset($objects);
		rmdir($dirPath);
	}
}

function createImsmanifest($id_course, $title){
	$imsmanifest_filename = "uploads/" .$id_course ."/imsmanifest.xml";
	
	//create imsmanifest.xml file
	$doc = new DOMDocument('1.0', 'utf-8');
	$ele = $doc->createElement( 'manifest' );
	$doc->appendChild( $ele );
	
	//add attributes to manifest tag
	$ele->setAttribute('xmlns', 'http://www.imsproject.org/xsd/imscp_rootv1p1p2');
	$ele->setAttribute('xmlns:adlcp', 'http://www.adlnet.org/xsd/adlcp_rootv1p2');
	$ele->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
	$ele->setAttribute('identifier', 'fiware'.$id_course);	
	$ele->setAttribute('version', '1.1');
	$ele->setAttribute('xsi:schemaLocation', 'http://www.imsproject.org/xsd/imscp_rootv1p1p2 imscp_rootv1p1p2.xsd http://www.imsglobal.org/xsd/imsmd_rootv1p2p1 imsmd_rootv1p2p1.xsd http://www.adlnet.org/xsd/adlcp_rootv1p2 adlcp_rootv1p2.xsd');
	
	//add element organizations to manifest tag
	$e = $doc->createElement( 'organizations' );
	$e->setAttribute('default', 'FIWARE');
	
	$sub = $doc->createElement( 'organization' );
	$sub->setAttribute('identifier', 'FIWARE');
	$sub->setAttribute('structure', 'heirarchical');
	
	$subsub = $doc->createElement( 'title' , 'FIWARE' );
	$sub->appendChild($subsub);
	
	$subsub = $doc->createElement( 'item' );
	$subsub->setAttribute('identifier', 'INDEX');
	$subsub->setAttribute('isvisible', 'true');
	
	
	$subsubsub = $doc->createElement( 'title' , $title );
	$subsub->appendChild($subsubsub);
	
	$subsubsub = $doc->createElement( 'item' );
	$subsubsub->setAttribute('identifier', 'INDEX_ID');
	$subsubsub->setAttribute('identifierref', 'INDEX_IDREF');
	$subsubsub->setAttribute('isvisible', 'true');
	
	$subsubsubsub = $doc->createElement( 'title' , 'Web Seminar' );
	$subsubsub->appendChild($subsubsubsub);
	$subsub->appendChild($subsubsub);
	
	$sub->appendChild($subsub);
	$e->appendChild($sub);
	
	$ele->appendChild($e);
	
	
	//add element resources to manifest tag
	$e = $doc->createElement( 'resources' );
	$sub = $doc->createElement( 'resource' );
	$sub->setAttribute('identifier', 'INDEX_IDREF');
	$sub->setAttribute('type', 'webcontent');
	$sub->setAttribute('href', 'index.html');
	$sub->setAttribute('adlcp:scormtype', 'sco');
	
	$subsub = $doc->createElement( 'file' );
	$sub->appendChild($subsub);
	$subsub->setAttribute('href', 'index.html');
	
	$e->appendChild($sub);
	
	$ele->appendChild($e);
	
	$doc->formatOutput = true;
	$doc->save($imsmanifest_filename);
	
	return $imsmanifest_filename;
}

function docker_call_post($url, $data){
	
	$options = array(
			'http' => array(
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'method'  => 'POST',
					'content' => http_build_query($data),
			),
	);
	
	$context  = stream_context_create($options);
	return file_get_contents($url."/say", false, $context);
}

?>