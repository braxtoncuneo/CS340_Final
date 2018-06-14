<?php

	error_reporting(E_ALL);
	session_start();

	
	include "home_pages.php";
	include "connectvars.php";
	include "header.php";

	
	session_unset();
	$autoGets = array(array("username","Successfully logged out."));
	$hasTable = false;
	$hasLog = false;
	


	$page = new AutoPage("Quest Database","Log Out",$forms,$content,$autoGets,$hasTable,$hasLog);
	
	$page->generatePage();
?>
