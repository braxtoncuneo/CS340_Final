<?php

	error_reporting(E_ALL);
	session_start();

	
	include "release_pages.php";
	include "connectvars.php";
	include "header.php";

	$entries = array(
		new AutoEntry("username","text","autoset","",true),
		new AutoEntry("password","password","regular","",true)
	);
	$forms = array(
		new AutoForm("Sign In",NULL,$entries,false)
	);
	$autoGets = array();
	$hasTable = false;
	$hasLog = false;
	
	$page = new AutoPage("Quest Database","Sign Up",$forms,$content,$autoGets,$hasTable,$hasLog);
	
	$page->generatePage();
?>
