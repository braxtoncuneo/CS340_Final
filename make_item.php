<?php

	error_reporting(E_ALL);
	session_start();

	
	include "edit_item_pages.php";
	include "connectvars.php";
	include "header.php";

	$entries = array(
		new AutoEntry("NAME","text","regular","",true),
		new AutoEntry("LOCATION","text","regular","",true),
		new AutoEntry("DESCRIPTION","textarea","regular","",true),
		new AutoEntry("REQUIREMENT","text","regular","",false),
		new AutoEntry("SUCCESS_TEXT","textarea","regular","",false),
		new AutoEntry("FAILURE_TEXT","textarea","regular","",false),
		new AutoEntry("world","number","autoget","",true)
	);
	$forms = array(
		new AutoForm("Make Item","makeItem",$entries,false)
	);
	$autoGets = array(
		array("username","Error: You need to be signed in to select a world to edit."),
		array("world","Error: You need to select a world in order to edit it.")
	);
	$hasTable = false;
	$hasLog = false;
	
	$page = new AutoPage("Quest Database","Add Item",$forms,$content,$autoGets,$hasTable,$hasLog);
	
	$page->generatePage();
?>
