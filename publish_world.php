<?php

	error_reporting(E_ALL);
	session_start();

	
	include "edit_world_pages.php";
	include "connectvars.php";
	include "header.php";

	$entries = array(
		new AutoEntry("WORLD_ID","number","regular","",true),
		new AutoEntry("AVAILABILITY","text","regular","","true"),
		new AutoEntry("username","text","autoget","",true)
	);
	$forms = array(
		new AutoForm("Publish World","publishWorld",$entries,false)
	);
	$autoGets = array(
		array("username","You need to be signed in to publish a world.")
	);
	$hasTable = false;
	$hasLog = false;
	
	$page = new AutoPage("Quest Database","Publish World",$forms,$content,$autoGets,$hasTable,$hasLog);
	
	$page->generatePage();
?>
