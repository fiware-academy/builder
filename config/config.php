<?php
/*
 * DATABASE
 */

$host = "localhost";
$dbuname = "root";
$dbpwd = "";
$database = "course_builder";
define("ADMIN_USERNAME","admin");

define("UPLOAD_DIR_ROOT","c:/Program Files/eclipse/workspace/course_builder/uploads/");

//tts configuration
$url_docker= "http://192.168.59.103:59125";

$bitrate = 144; //8,16,24,32,40,48,56,64,80,96,112,128,144,160
$mode = "j"; //m/s/j/f/a
$path_lame = "c:/"; //path lame

$pause = 2; //pause in seconds between two audio files

/*
 * This is the suffix to add to the page memberauthusing***.php
 * to customize most the authentication criteria.
 * SEE: memberABSTRACT.php and other meber$authenticationmethod.php"
 * Now available are: DB FUSIONFORGE KEYROCK
 */
$authenticationmethod="KEYROCKBEHINDENGPROXY";
?>
