<?php

	error_reporting(E_ALL);
	session_start();

	
	include "release_pages.php";
	include "connectvars.php";
	include "header.php";

	$entries = array(
		new AutoEntry("username","text","normal","",true),
		new AutoEntry("password","password","normal","",true),
		new AutoEntry("confirm","confirm","normal","",true),
		new AutoEntry("salt","text","hidden","",true)
	);
	$forms = array(
		new AutoForm("Sign Up","signupPlayer",$entries,false)
	);
	$autoGets = array();
	$hasTable = false;
	$hasLog = false;
	


	$page = new AutoPage("Quest Database","Sign Up",$forms,$content,$autoGets,$hasTable,$hasLog);
	
	$page->generatePage();
?>
