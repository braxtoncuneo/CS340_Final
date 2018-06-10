<?php
	
	error_reporting(E_ALL);
	//ob_start();
	session_start();

	//header_remove();
	
	include "play_pages.php";
	include "connectvars.php";
	include "header.php";

	if(isset($_SESSION["SAVE_NAME"]) && isset($_SESSION["username"])){
		$_SESSION["save"] = fetchSaveID($_SESSION["username"],$_SESSION["SAVE_NAME"]);
		$_SESSION["SAVE_WORLD"] = fetchSaveWorld($_SESSION["save"]);
		//echo $_SESSION["SAVE_NAME"];
		//echo "->" . $_SESSION["save"] . "<-";
	}
	
			
	$pickup = new AutoForm(
		"PICK UP","pickUp",array(
		new AutoEntry("item","text","regular","",true),
		new AutoEntry("save","number","autoget","",true),
		new AutoEntry("SAVE_WORLD","number","autoget","",true)
		),false
	);
	
	$drop = new AutoForm(
		"DROP","dropItem",array(
		new AutoEntry("item","text","regular","",true),
		new AutoEntry("save","number","autoget","",true),
		new AutoEntry("SAVE_WORLD","number","autoget","",true)
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
		new AutoEntry("save","number","autoget","",true),
		new AutoEntry("SAVE_WORLD","number","autoget","",true)
		),false
	);
	
	$inventory = new AutoForm(
		"INVENTORY","showInventory",array(
		new AutoEntry("save","number","autoget","",true),
		new AutoEntry("SAVE_WORLD","number","autoget","",true)
		),false
	);
	
	$currLocation = new AutoForm(
		"CURRENT ROOM","showLocation",array(
		new AutoEntry("save","number","autoget","",true),
		new AutoEntry("SAVE_WORLD","number","autoget","",true)
		),false
	);

	$load = new AutoForm(
		"LOAD","loadGame",array(
		new AutoEntry("SAVE_NAME","text","autoset","",true),
		new AutoEntry("username","text","autoget","",true)
		),false
	);

	

	$forms = array( $pickup, $drop, $lookat, $govia, $load, $currLocation, $inventory );
	
	$aGets = array(array("username","You need to be logged in to play a game"));
	$hasTable = false;
	$hasLog = true;
	$page = new AutoPage("QUEST DATABASE","Play Game",$forms,$content,$aGets,$hasTable,$hasLog);
	
	$page->generatePage();
	
?>
