<?php
	
	error_reporting(E_ALL);
	//ob_start();
	session_start();

	//header_remove();
	
	include "pages.php";
	include "connectvars.php";
	include "header.php";

	/*
	if($_POST){

		if (headers_sent() === false){
			header('Location: ' . $_SERVER["REQUEST_URI"], true, 301);
		}
	}
	*/

	//include "pages.php";
	//include "connectvars.php";
	//include "header.php";
	
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
	


	$page = new AutoPage("Quest Database","message",$forms,$content,$hasTable,$hasLog);
	
	$page->generatePage();
	//echo "<html><body>";
	
	//echo "</body></html>";
	//ob_end_flush();
?>
