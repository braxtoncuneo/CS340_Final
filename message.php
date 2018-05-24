

<?php
	session_start();
	include "pages.php";
	include "connectvars.php";
	include "header.php";
	
	$entries = array(
		new AutoEntry("sender","text","",true,false),
		new AutoEntry("reciever","text","",true,false),
		new AutoEntry("body","textarea","",true,false),
		new AutoEntry("secretNumber","number","",true,false)
	);
	$forms = array(
		new AutoForm("MakeMessage","makeMessage",$entries,false)
	);
	$hasTable = false;
	$hasLog = false;
	$page = new AutoPage("Quest Database",$forms,$content,$hasTable,$hasLog);
	
	$page->generatePage();
	//echo "<html><body>";
	
	//echo "</body></html>";
?>


