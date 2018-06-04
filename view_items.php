<?php

	error_reporting(E_ALL);
	session_start();

	
	include "edit_item_pages.php";
	include "connectvars.php";
	include "header.php";

	$entries = array(
		new AutoEntry("username","text","autoget","",true)
	);
	$forms = array(
		new AutoForm("item","viewItems",$entries,false)
	);
	$autoGets = array(array("username","Error: To view your worlds, you need to be logged in."));
	$hasTable = true;
	$hasLog = false;
	


	$page = new AutoPage("Quest Database","View Items",$forms,$content,$autoGets,$hasTable,$hasLog);
	
	$page->generatePage();
?>
