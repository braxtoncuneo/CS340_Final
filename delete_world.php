<?php

	error_reporting(E_ALL);
	session_start();

	
	include "edit_world_pages.php";
	include "connectvars.php";
	include "header.php";

	$entries = array(
		new AutoEntry("DELETE WORLD?","text","regular","NO",true),
		new AutoEntry("world","number","autoget","",true)
	);
	$forms = array(
		new AutoForm("Delete World","deleteWorld",$entries,false)
	);
	$autoGets = array(
		array("username","Error: You need to be signed in to edit a world."),
		array("world","Error: You need to select a world in order to edit it.")
	);
	$hasTable = false;
	$hasLog = false;
	
	$page = new AutoPage("Quest Database","Delete Item",$forms,$content,$autoGets,$hasTable,$hasLog);
	
	$page->generatePage();
?>
