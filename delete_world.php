<?php

	error_reporting(E_ALL);
	session_start();

	
	include "edit_world_pages.php";
	include "connectvars.php";
	include "header.php";

	$entries = array(
		new AutoEntry("WORLD","number","regular","",true),
		new AutoEntry("CONFIRM","text","regular","",true),
		new AutoEntry("username","text","autoget","",true)
	);
	$forms = array(
		new AutoForm("Delete World","deleteWorld",$entries,false)
	);
	$autoGets = array(
		array("username","Error: You need to be signed in to edit a world.")
	);
	$hasTable = false;
	$hasLog = false;
	
	$page = new AutoPage("Quest Database","Delete Item",$forms,$content,$autoGets,$hasTable,$hasLog);
	
	$page->generatePage();
?>
