<?php
/*
 Copyright (c) 2011, 2014 Engineering Group and others.
 All rights reserved. This program and the accompanying materials
 are made available under the terms of the Eclipse Public License v1.0
 which accompanies this distribution, and is available at
 http://www.eclipse.org/legal/epl-v10.html
 Contributors:
 Engineering Group - Course Builder
 */
session_start();
include_once 'config/config.php';
include_once 'config/database.php';
include_once 'config/functions.php';
$status = new stdClass();

// Whe are here so apache response, right?
$status->httpd="ok";

// check connection to database
if(mysqli_ping($mysql)){
	$status->db="ok";
}else{
	$status->db="ko";
}
// check connection for authentication
$status->auth="ok";

if(isset($_GET['xml']) || 
		isset($_POST['xml'])){
	include ('config/memberObjectAndXML.php');
	//header('Content-Type: application/xml');
	$newobj = new stdClass();
	$newobj->status=$status;
	$objAndXml = new ObjectAndXML();
	/*
	// test
	$objData1 = new stdClass();
	$objData1->records->person = array();
	$person=new stdClass();
	$objData1->records->person[0]=$person;
	$objData1->records->person[0]->name = 'XYZ';
	$objData1->records->person[0]->age = '28';
	$objData1->records->person[0]->gender = 'Male';
	
	$person=new stdClass();
	$objData1->records->person[1]=$person;
	$objData1->records->person[1]->name = 'ABC';
	$objData1->records->person[1]->age = '25';
	$objData1->records->person[1]->gender = 'Male';
	*/
	header('Content-Type: application/xml');
	echo $objAndXml->objToXML($newobj);
	return;
}
header('Content-Type: application/json');
echo json_encode($status);
?>