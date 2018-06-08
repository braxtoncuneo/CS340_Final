<?php

	error_reporting(E_ALL);
	session_start();

	if($_SERVER['SERVER_PORT'] !== 443 &&
   		(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) {
  		header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  		exit;
	}

	
	include "home_pages.php";
	include "connectvars.php";
	include "header.php";

	$entries = array(
		new AutoEntry("username","text","autoset","",true),
		new AutoEntry("password","password","regular","",true)
	);
	$forms = array(
		new AutoForm("Sign In","signIn",$entries,false)
	);
	$autoGets = array();
	$hasTable = false;
	$hasLog = false;
	
	$page = new AutoPage("Quest Database","Sign Up",$forms,$content,$autoGets,$hasTable,$hasLog);
	
	$page->generatePage();
?>
