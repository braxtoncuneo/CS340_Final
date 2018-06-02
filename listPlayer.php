<?php
	
	error_reporting(E_ALL);
	session_start();
	
	include "pages.php";
	include "connectvars.php";
	include "header.php";
	
	$entries = array(
	);
	$forms = array(
		new AutoForm("ListPlayers","listPlayers",$entries,false)
	);


	$hasTable = true;
	$hasLog = false;

	$page = new AutoPage("Quest Database","listPlayers",$forms,$content,$hasTable,$hasLog);
	
	$page->generatePage();

?>
