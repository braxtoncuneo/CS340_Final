<?php
	
	error_reporting(E_ALL);
	//ob_start();
	session_start();

	//header_remove();
	
	include "play_pages.php";
	include "connectvars.php";
	include "header.php";

	$newSave = new AutoForm(
		"DELETE GAME","deleteState",array(
		new AutoEntry("SAVE_NAME","text","regular","",true),
		new AutoEntry("username","text","autoget","",true)
		),false
	);

	$forms = array(	$newSave );
	$aGets = array(array("username","You need to be logged in to delete a save"));
	$hasTable = false;
	$hasLog = false;
	$page = new AutoPage("QUEST DATABASE","Play Game",$forms,$content,$aGets,$hasTable,$hasLog);
	
	$page->generatePage();
	
?>
