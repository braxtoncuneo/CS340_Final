<?php

	error_reporting(E_ALL);
	session_start();

	
	include "play_pages.php";
	include "connectvars.php";
	include "header.php";

	$entries = array(
		new AutoEntry("dummy","text","hidden","1",true)
	);
	$forms = array(
		new AutoForm("worlds","viewPublic",$entries,false)
	);
	$autoGets = array();
	$hasTable = true;
	$hasLog = false;
	


	$page = new AutoPage("Quest Database","View Worlds",$forms,$content,$autoGets,$hasTable,$hasLog);
	
	$page->generatePage();
?>
