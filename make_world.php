<?php

	error_reporting(E_ALL);
	session_start();

	
	include "edit_world_pages.php";
	include "connectvars.php";
	include "header.php";

	$entries = array(
		new AutoEntry("name","text","regular","",true),
		new AutoEntry("username","text","autoget","",true)
	);
	$forms = array(
		new AutoForm("MAKE WORLD","makeWorld",$entries,false)
	);
	$autoGets = array(
		array("username","You need to be signed in to select a world to edit.")
	);
	$hasTable = false;
	$hasLog = false;
	
	$page = new AutoPage("Quest Database","Sign Up",$forms,$content,$autoGets,$hasTable,$hasLog);
	
	$page->generatePage();
?>
