

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

	<?php

		class AutoEntry {

			var $entryName;
			var $entryType;
			var $entryValue;
			var $required;
			var $hidden;
			var $error;
			var $message;

			function verifyText () {

			}

			function verifyPassword () {

			}

			function verifySubmit () {

			}

			function verifyRadio () {

			}

			function verifyCheckbox() {
				var $error = true;
				if(gettype($entryValue) === "string"){
					if($entryValue == $entryName) {
						$entryValue = true;
					}
					else{
						$entryValue = false;
					}
					$error = false;
				}
				return $error;
			}

			function verifyNumber () {
				var $error = true;
				if(gettype($entryValue) === "string"){
					if(is_numeric($entryValue)) {
						$error = false;
						$entryValue = (int) $entryValue;
					}
				}
				return $error;
			}

			function makeSalt(){
				return base64_encode(mcrypt_create_iv(12,MCRYPT_DEV_URANDOM));
			}

			function verify() {
				if($entryValue == "text"){
					return verifyText();
				}
				else if($entryValue == "password"){
					return verifyPassword();
				}
				else if($entryValue == "submit"){
					return verifySubmit();
				}
				else if($entryValue == "radio"){
					return verifyRadio();
				}
				else if($entryValue == "checkbox"){
					return verifyCheckbox();
				}
				else if($entryValue == "number"){
					return verifyNumber();
				}
				return true;
			}

			function generate() {

			}

			function __construct($eName,$eType,$eValue,$eRequired,$eHidden) {
				$entryName = $eName;
				$entryType = $eType;
				$entryValue = $eValue;
				$required = $eRequired;
				$hidden = $eHidden;
				$error = false;
				$message = "";
			}

		}

		class AutoForm {

			var $formName;
			var $formProc;
			var $entryList;
			var $authorized;
			var $error;

			function loadValues() {
				var $error = false;
				foreach($entryList as $entry){
					$error = $error && $entry->load();
				}
			}

			function verify() {
				$error = false;
				foreach($entryList as $entry) {
					$error = $error && $entry->verify();
				}
			}

			function process() {
				if($error == false) {
					$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
					var $call = "CALL " . $formProc . " ( ";
					var $val;
					var $first = true;
					foreach($entryList as $entry){
						if($first){
							$first = false;
						}
						else{
							$call .= " , ";
						}
						$val = $entry->entryValue;
						$call .= "'$val'";
					}
					$call .= " ); ";
					var $result = mysqli_query($conn, $call)
					mysqli_close($conn);
					return $result;
				}
				else{
					echo " <script> \n";
					foreach($entryList as $entry) {
						if($entryList->message != ""){
							echo " alert(" . $entryList->message . ");\n";
						}
					}
					echo " </script>\n";
					return NULL;
				}
			}

			function generate() {
				foreach($entryList as $entry) {
					$entry->generate();
				}
			}

            function __construct($fName,$fProc,$fEntryList,$fAuthorized) {
				$formName = $fName;
				$formProc = $fProc;
				$entryList = $fEntryList;
				$authorized = $fAuthorized;
				$error = false;
            }

		}

		class AutoPage {

			var $siteName;
			var $formList;
			var $hasTable;
			var	$hasLog;

			function getText(){
				$row = mysqli_fetch_row($result);
				return $row["resultText"];
			}

			function pageHasLog(){
				$_SESSION["pageLog"] = "";
			}

			function pageNoLog(){
				unset($_SESSION["pageLog"]);
			}

			function appendLog($aText){
				if(isset($_SESSION["pageLog"]){
					$_SESSION["pageLog"] .= aText;
				}
			}


			function getActiveForm(){
				var $result;
				var $fName = $_POST['formName'];
				foreach($formList as $fItr){
					if($fItr->formName == $fName){
						$result = $fItr;
						break;
					}
				}
				return $result;
			}

			function processForm($theForm){
				var $result = NULL;
				if($theForm != NULL){
					var $reqType = $_SERVER["REQUEST_METHOD"];
					if(reqType == "POST"){
						$theForm->loadValues();
						$theForm->verify();
						$result = $theForm->process();
						break;
					}
				}
				return $result;
			}


			function generateHeader(){
				echo 	"<header>\n" .
						$siteName .
						"";

				if(isset($_SESSION["username"])){
					echo	" - <em> Welcome <span id='username'>" .
							$_SESSION["username"] .
							"</span>!</em>";
				}
				echo	"</header>";
			}

			function generateNavBar(){
				echo "<nav> <ul> ";
				foreach ($content as $page => $location){
					echo	"<li><a href='$location' ".
							($page==$currentpage?" class='active'":"").
							">".$page."</a></li>";
				}
				echo "</ul> </nav>";
			}

			function generateTableHeader($theResult){
				$fields_num = mysqli_num_fields($theResult);
				echo "<tr>";
				for($i=0; $i<$fields_num; $i++) {
					$field = mysqli_fetch_field($theResult);
					echo "<td><b>$field->name</b></td>";
				}
				echo "</tr>\n";
			}

			function generateTableContent($theResult){
				while($row = mysqli_fetch_row($theResult)) {
					echo "<tr>";
					foreach($row as $cell)
						echo "<td>$cell</td>";
					echo "</tr>\n";
				}
			}

			function generateTable($theResult) {
				if (!$theResult) {
					die("Query failed");
				}
				echo "<table id='t01' border='1'>";
				generateTableHeader($theResult);
				generateTableContent($theResult);
				echo "<\table>";

				mysqli_free_result($result);
			}

			function generateLog(){

			}

			function generateContent(){
				var $formResult = NULL;
				if(reqType == "POST"){
					var $theForm = getActiveForm();
					var $formResult = processForm($theForm);
				}

				if($hasTable){
					generateTable($formResult);
				}
				else{
					if($hasLog){
						echo "<div class='left'>";
					}
					else{
						echo "<div class='middle'>";
					}
					foreach($formList as $fItr){
						$fItr->generate();
					}
					echo "<\div>";
					if($hasLog){
						echo "<div class='right'>";
						echo ($formResult != NULL)? $formResult : "";
						echo "</div>";
					}
				}
			}

			function generatePage(){
				generateHeader();
				generateNavBar();
				generateContent();
			}

			function __construct($sName,$fList,$hTable,$hLog) {
				$siteName = $sName;
				$formList = $fList;
				$hasTable = $hTable;
				$hasLog = $hLog;
            }

		}


	?>
