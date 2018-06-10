<?php

	error_reporting(E_ALL);
	session_start();

	
	include "autoTestPages.php";
	include "connectvars.php";
	include "header.php";


	$entries = array(
		new AutoEntry("username","text","autoget","",true)
	);
	$forms = array(
		new AutoForm("worlds","viewWorlds",$entries,false)
	);
	$autoGets = array(array("username","Error: To view your worlds, you need to be logged in."));
	$hasTable = true;
	$hasLog = false;
	


	$page = new AutoPage("Quest Database","userWorlds",$forms,$content,$autoGets,$hasTable,$hasLog);
	
	$page->generatePage();
?>
