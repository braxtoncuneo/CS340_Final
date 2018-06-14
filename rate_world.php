<?php
	
	error_reporting(E_ALL);
	//ob_start();
	session_start();

	//header_remove();
	
	include "play_pages.php";
	include "connectvars.php";
	include "header.php";

	$newSave = new AutoForm(
		"RATE WORLD","rateWorld",array(
		new AutoEntry("WORLD_ID","number","regular","",true),
		new AutoEntry("RATING_(1-10)","number","regular","",true),
		new AutoEntry("username","text","autoget","",true),
		),false
	);

	$forms = array(	$newSave );
	$aGets = array(array("username","You need to be logged in to rate a game"));
	$hasTable = false;
	$hasLog = false;
	$page = new AutoPage("QUEST DATABASE","Rate World",$forms,$content,$aGets,$hasTable,$hasLog);
	
	$page->generatePage();
	
?>
