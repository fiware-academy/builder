<?php

$mysql = new mysqli($host, $dbuname, $dbpwd, $database);

/**
 * -- GESTIONE TRANSAZIONI DATABASE MYSQL --
 */

function start_transaction() {
	global $mysql;
	$mysql->query("SET AUTOCOMMIT=0");
	$mysql->query("START TRANSACTION");
}

function rollback() {
	global $mysql;
	$mysql->query("ROLLBACK");
}

function commit() {
	global $mysql;
	$mysql->query("COMMIT");
}

function close_db() {
	global $mysql;
	$mysql->close();
}

?>
