<?php

	error_reporting(E_ALL);
	session_start();

	
	include "messagePages.php";
	include "connectvars.php";
	include "header.php";

	$entries = array(
		new AutoEntry("sender","text","normal","",true),
		new AutoEntry("reciever","text","normal","",true),
		new AutoEntry("body","textarea","normal","",true),
		new AutoEntry("secretNumber","number","normal","",true)
	);
	$forms = array(
		new AutoForm("MakeMessage","makeMessage",$entries,false)
	);
	$autoGets = array();
	$hasTable = false;
	$hasLog = true;
	


	$page = new AutoPage("Quest Database","message",$forms,$content,$autoGets,$hasTable,$hasLog);
	
	$page->generatePage();
?>
