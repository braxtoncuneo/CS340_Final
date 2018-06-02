<?php
	
	error_reporting(E_ALL);
	//ob_start();
	session_start();

	//header_remove();
	
	include "pages.php";
	include "connectvars.php";
	include "header.php";

	if(isset($_SESSION["username"])){
		$pickup = new AutoForm(
			"PickUp","pickup",array(
			new AutoEntry("PICK UP","text","",true,false),
			new AutoEntry("username","autoget",true,true),
			new AutoEntry("save",
			),false
		);
		$entries = array(
			new AutoEntry("sender","text","",true,false),
			new AutoEntry("reciever","text","",true,false),
			new AutoEntry("body","textarea","",true,false),
			new AutoEntry("secretNumber","number","",true,false)
		);
		$forms = array(
			new AutoForm("MakeMessage","makeMessage",$entries,false)
		);
		$hasTable = true;
		$hasLog = false;
	}
	else{
		$entries = array();
		$forms = array();
		$hasTable = false;
		$hasLog = false;
	}

	$page = new AutoPage("Quest Database","message",$forms,$content,$hasTable,$hasLog);
	
	$page->generatePage();

?>
