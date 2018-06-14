<?php

	error_reporting(E_ALL);
	session_start();

	
	include "edit_path_pages.php";
	include "connectvars.php";
	include "header.php";

	$entries = array(
		new AutoEntry("world","number","autoget","",true)
	);
	$forms = array(
		new AutoForm("worlds","viewPaths",$entries,false)
	);
	$autoGets = array(	array("username","Error: To view your worlds, you need to be logged in."),
				array("world","Error: To view the paths of a world, you need to have that world selected"));
	$hasTable = true;
	$hasLog = false;
	


	$page = new AutoPage("Quest Database","View Paths",$forms,$content,$autoGets,$hasTable,$hasLog);
	
	$page->generatePage();
?>
