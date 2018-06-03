<?php

	error_reporting(E_ALL);
	session_start();

	
	include "autoTestPages.php";
	include "connectvars.php";
	include "header.php";

	
	unset($_SESSION["username"]);
	$autoGets = array(array("username","Successfully logged out."));
	$hasTable = false;
	$hasLog = false;
	


	$page = new AutoPage("Quest Database","logout",$forms,$content,$autoGets,$hasTable,$hasLog);
	
	$page->generatePage();
?>
