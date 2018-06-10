<?php

	error_reporting(E_ALL);
	session_start();

	
	include "autoTestPages.php";
	include "connectvars.php";
	include "header.php";

	$entries = array(
		new AutoEntry("username","text","autoset","",true)
	);
	$forms = array(
		new AutoForm("worlds",NULL,$entries,false)
	);
	$autoGets = array();
	$hasTable = false;
	$hasLog = true;
	


	$page = new AutoPage("Quest Database","",$forms,$content,$autoGets,$hasTable,$hasLog);
	
	$page->generatePage();
?>
