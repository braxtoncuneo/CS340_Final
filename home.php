<?php

error_reporting(E_ALL);
session_start();


include "home_pages.php";
include "connectvars.php";
include "header.php";

//unset($_SESSION["world"]);

$_SESSION["log"]="";

$forms = array();
$hasTable = false;
$hasLog = false;

$autoGets = array(
	array("dummy",
	"<div id='container'>".
	"Quest Database takes the text adventure games of the 1980's ".
	"and brings it to a web-based experience. With an account, ".
	"you can make and play your own games and share them with others.".
	"<br><br>".
	"Happy playing!".
	"</div>"
	)
);	


$page = new AutoPage("Quest Database","",$forms,$content,$autoGets,$hasTable,$hasLog);

$page->generatePage();
?>
