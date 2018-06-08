<?php
	
	error_reporting(E_ALL);
	//ob_start();
	session_start();

	//header_remove();
	
	include "home_pages.php";
	include "connectvars.php";
	include "header.php";

	// change later
	$_SESSION["save"] = 26;
	$_SESSION["world"] = 21;	
			
	$pickup = new AutoForm(
		"PICK UP","pickup",array(
		new AutoEntry("item","text","regular","",true),
		new AutoEntry("save","number","autoget","",true),
                new AutoEntry("world","number","autoget","",true)
		),false
	);
	
	$drop = new AutoForm(
		"DROP","dropItem",array(
		new AutoEntry("item","text","regular","",true),
                new AutoEntry("save","number","autoget","",true),
                new AutoEntry("world","number","autoget","",true)
		),false
	);

	$lookat = new AutoForm(
		"LOOK AT","lookAt",array(
		new AutoEntry("thing","text","regular","",true),
		new AutoEntry("save","number","autoget","",true)
		),false
	);

	$govia = new AutoForm(
		"GO VIA","goVia",array(
		new AutoEntry("path","text","regular","",true),
		new AutoEntry("save","number","autoget","",true)
		),false
	);
		

	$forms = array(	$pickup, $drop, $lookat, $govia	);
	$aGets = array(array("username","You need to be logged in to play a game"));
	$hasTable = false;
	$hasLog = true;
	$page = new AutoPage("QUEST DATABASE","Play Game",$forms,$content,$aGets,$hasTable,$hasLog);
	
	$page->generatePage();
	
?>
