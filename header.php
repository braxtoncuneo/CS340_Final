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
				if(gettype($this->entryValue) === "string"){
					if($this->entryValue === "" && $this->required){
						$this->requiredMessage();
						$this->error = true;
					}
					else{
						$this->clearMessage();
						$this->error = false;
					}
				}
				else{
					$this->typeErrorMessage();
				}
				return $this->error;
			}
			
			function verifyPassword () {
				if($this->entryValue === ""){
					$this->requiredMessage();
					$this->error = true;
				}
				else if(strLen($this->entryValue) < 8) {
					$this->message = "Password must be at least 8 characters";
					$this->error = true;
				}
				else{
					$this->clearMessage();
					$this->error = false;
				}
				return $this->error;

			}
			
			function verifyConfirm ($hostForm) {
				if(gettype($this->entryValue) === "string"){
					foreach($hostForm->entryList as $eItr){
						if($eItr->entryType === "password"){
							if($this->entryValue === $eItr->entryValue){
								$this->clearMessage();
								$this->error = false;
								return $this->error;
							}
							else{
								$this->message = 	"Password confirmation '" .
													$this->entryValue .
													"' does not match password '" .
													$eItr->entryValue . "'";
								$this->error = true;
								return $this->error;
							}
						}
					}
				}
				else{
					$this->typeErrorMessage();
				}
				return $this->error;
			}
			
			function verifyCheckbox() {
				$this->error = true;
				if(gettype($this->entryValue) === "string"){
					
					if($this->entryValue == $this->entryName) {
						$this->entryValue = true;
					}
					else{
						$this->clearMessage();
						$this->entryValue = false;
					}
					
					
					$this->error = false;
				}
				else{
					$this->typeErrorMessage();
				}
				return $this->error;
			}
			
			
			function verifyNumber () {
				$this->error = true;
				if(gettype($this->entryValue) === "string"){
					if($this->entryValue === "" && $this->required){
						$this->requiredMessage();
					}
					else if(is_numeric($this->entryValue)) {
						$this->error = false;
						$this->entryValue = (int) $this->entryValue;
					}
					else{
						$this->valueErrorMessage();
					}
				}
				else{
					$this->typeErrorMessage();
				}
				return $this->error;
			}

			

			function clearMessage(){
				$this->message = "";
			}

			function requiredMessage(){
				$this->message = "Value error: no value for required entry '" .
								  $this->entryName . "'";
			}

			function valueErrorMessage(){
				$this->message =	"Value error: bad value '" .
									$this->entryValue .
									"' used for " . $this->entryType .
									" entry " . $this->entryName;
			}

			function typeErrorMessage(){
				$this->message =	"Type error: bad type '" .
									gettype($this->entryValue) .
									"' used for " . $this->entryType .
									" entry " . $this->entryName;
			}

			function makeSalt(){
				return base64_encode(mcrypt_create_iv(12,MCRYPT_DEV_URANDOM));
			}

			function load() {
				$this->entryValue = trim($_POST[$this->entryName]);
				return false;
			}
			

			function verify() {
				
				if($this->entryType === "text" || $this->entryType === "textarea"){
					return $this->verifyText();
				}
				else if($this->entryType === "password"){
					return $this->verifyPassword();
				}
				else if($this->entryType === "confirm"){
					return $this->verifyPassword($this);
				}
				else if($this->entryType === "checkbox"){
					return $this->verifyCheckbox();
				}
				else if($this->entryType === "number"){
					return $this->verifyNumber();
				}
				return true;
			}
			
			function generate($hostForm) {
				if($this->entryType === "textarea"){
					
					echo "<p>\n";
					echo "<label for='" . $this->entryName . "'>\n" .
							$this->entryName . ":</label>";
					echo "<textarea  rows='4' cols='50' " .
							" name='" . $this->entryName . "'" .
							" form='" . $hostForm->formName . "'";
					$ErrMap = $_SESSION[$hostForm->formName];
					if($ErrMap[$this->entryName]){
						echo " class='bad' ";
					}
					echo ">\n";
					echo $ErrMap[$this->entryName];
					echo "</textarea>\n";
					echo "</p>\n";
					
				}
				else{
					
					$realType;
					if( $this->entryType === "confirm" ){
						$realType = "password";
					}
					else{
						$realType = $this->entryType;
					}
					
					echo "<p>\n";
					echo "<label for='" . $this->entryName . "'>\n" .
							$this->entryName . ":</label>";
					echo "<input" . " type='" . $realType . "'" .
									" name='" . $this->entryName . "'" .
									" id='" . $this->entryName . "'" .
									" title='" . $this->entryName . "'";
					$ErrMap = $_SESSION[$hostForm->formName];
					if($ErrMap[$this->entryName]){
						echo " class='bad' ";
					}
					echo ">\n";
					echo $ErrMap[$this->entryName];
					echo "\n</input>\n";
					echo "</p>\n";
					
						
				}
			}
			

			function __construct($eName,$eType,$eValue,$eRequired,$eHidden) {
				$this->entryName = $eName;
				$this->entryType = $eType;
				$this->entryValue = $eValue;
				$this->required = $eRequired;
				$this->hidden = $eHidden;
				$this->error = false;
				$this->message = "";
			}
			

		}

		

		class AutoForm {

			
			var $formName;
			var $formProc;
			var $entryList;
			var $authorized;
			var $error;
			
			
			function loadValues() {
				$this->error = false;
				foreach($this->entryList as $entry){
					$this->error = $this->error || $entry->load();
				}
			}
			
			
			function verify() {
				$ErrMap = array();
				$this->error = false;
				$locErr;
				foreach($this->entryList as $entry) {
					$locErr = $entry->verify();
					$ErrMap[$entry->entryName] = $locErr;
					$this->error = $this->error || $locErr;
				}
				$_SESSION[$this->formName] = $ErrMap;
			}
			
			function process() {

				$aKey = "alert";
				if($this->error == false) {
					$_SESSION["check"] = "woah";
					
					$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
					
					$call = "CALL " . $this->formProc . " ( ";
					$val;
					$first = true;
					foreach($this->entryList as $entry){
						if($first){
							$first = false;
						}
						else{
							$call .= " , ";
						}
						$val = $entry->entryValue;
						$val =  mysqli_real_escape_string($conn,$val);
						
						if($entry->entryType === "checkbox" ||
							$entry->entryType === "number"){
							$call .= " " . $val . " ";
						}
						else{
							$call .= " \"" . $val . "\" ";
						}
						
					}
					$call .= " ) ";
					$result = mysqli_query($conn, $call);
					$_SESSION[$aKey]="";
					if(!$result){
						$_SESSION[$aKey] = "<script>" .
						"alert(\"" . mysqli_error($conn) . "\");" .
						"</script>";
					}
					$_SESSION[$aKey] .= "<script> alert(\"" . $call . "\");</script>";
					mysqli_close($conn);
					return $result;
					
					return NULL;
				}
				else{
					$_SESSION["check"] = "nope";
					$_SESSION[$aKey] = "";	
					$_SESSION[$aKey] .= " <script> \n";
					foreach($this->entryList as $entry) {
						if($entry->message !== ""){
							$_SESSION[$aKey] .= " alert(\"" . $entry->message . "\");\n";
						}
					}
					$_SESSION[$aKey] .= " </script>\n";
					
					return NULL;
				}
			}
			
			
			function generate() {
				echo "<form method='post' id='" . $this->formName . "'>\n";
				echo "<fieldset>\n";
				foreach($this->entryList as $entry) {
					$entry->generate($this);
				}
				echo "<input hidden type='hidden' name='formName' " . 
					" value='" . $this->formName . "'> </input>";
				echo "</fieldset>\n";
				echo "<p>\n";
				echo "<input type = 'submit'  value = 'submit' />";
				echo "<input type = 'reset'  value = 'reset' />";
				echo "</p>\n";
				echo "</form>";
			}
			
			function __construct($fName,$fProc,$fEntryList,$fAuthorized) {
				$this->formName = $fName;
				$this->formProc = $fProc;
				$this->entryList = $fEntryList;
				$this->authorized = $fAuthorized;
				$this->error = false;
			}
			
		}
		
		class AutoPage {

			var $siteName;
			var $pageName;
			var $formList;
			var $navList;
			var $hasTable;
			var $hasLog;
			
			function getText(){
				$row = mysqli_fetch_row($result);
				return $row["resultText"];
			}

			function appendLog($aText){

				if(isset($_SESSION[$this->pageName . "log"]) && 
						$_SESSION[$this->pageName . "log"] !== NULL){
					$_SESSION[$this->pageName . "log"] .= aText;
				}
				else{
					$_SESSION[$this->pageName . "log"] = aText;
				}
			}

			
			function getActiveForm(){
				$result;
				$fName = $_POST["formName"];
				foreach($this->formList as $fItr){
					if($fItr->formName === $fName){
						$result = $fItr;
						break;
					}
				}
				return $result;
			}
			
			
			function processForm($theForm){
				$result = NULL;
				if($theForm !== NULL){
					if($_POST){
						$_SESSION["check"]="start";
						$theForm->loadValues();
						$theForm->verify();
						$result = $theForm->process();
					}
				}
				return $result;
			}

			
			function generateHeader(){
				echo 	"<header>\n" .
						$this->siteName .
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
				foreach ($this->navList as $page => $location){
					echo	"<li><a href='$location' ".
							($page==$currentpage?" class='active'":"").
							">".$page."</a></li>";
				}
				echo "</ul> </nav>";
			}
			
			function generateTableHeader($theResult){
				$res = "";
				
				$fields_num = mysqli_num_fields($theResult);
				$res .= "<tr>";
				for($i=0; $i<$fields_num; $i++) {
					$field = mysqli_fetch_field($theResult);
					$res .= "<td><b>$field->name</b></td>";
				}
				$res .= "</tr>\n";
				
				return $res;
			}
			
			function generateTableContent($theResult){
				$res = "";
				
				while($row = mysqli_fetch_row($theResult)) {
					$res .= "<tr>";
					foreach($row as $cell){
						$res .= "<td>$cell</td>";
					}
					$res .= "</tr>\n";
				}
				
				return $res;
			}

			function generateTable($theResult) {
				
				if($_POST){
					$table = "";
					if($theResult){
						$table .= "<table id='t01' border='1'>";
						$table .= $this->generateTableHeader($theResult);
						$table .= $this->generateTableContent($theResult);
						$table .= "</table>";

					}
					else{
						$table = "NO RESULT";
					}
					$_SESSION[$this->pageName . "table"] = $table;
				}
				else{
					$table = $_SESSION[$this->pageName . "table"];
					echo $this->pageName . $table;
				}
				

				//mysqli_free_result($result);
			}
			
			function generateContent(){
				
				$reqType = $_SERVER["REQUEST_METHOD"];
				echo $reqType;

				
				if($this->hasLog){
					echo "<div class='left'>";
				}
				else{
					echo "<div class='middle'>";
				}
				foreach($this->formList as $fItr){
					$fItr->generate();
				}
				echo "</div>";
				if($this->hasLog){
					$resultLog = $_SESSION[$this->pageName . "log"];
					$this->appendLog($resultLog);
					echo "<div class='right'>";
					echo $_SESSION[$this->pageName . "log"];
					echo "</div>";
				}
				if($this->hasTable){
					$resultTable = $_SESSION[$this->pageName . "table"];
					$this->generateTable($resultTable);
					if(!$resultTable){
						echo "OH NO";
					}
				}
			}
			

			function generatePage(){
				if($_POST){
					$theForm = $this->getActiveForm();
					$_SESSION["formName"] = $theForm->formName;
					$formResult = $this->processForm($theForm);
					if($formResult !== NULL){
						if($this->hasLog){
							$logText = mysqli_fetch_row($formResult)["logText"];
							$_SESSION[$this->pageName . "log"] = $logText;
						}
						else if($this->hasTable){
							$this->generateTable($formResult);
						}
					}
					header("Location: " . $_SERVER["REQUEST_URI"],true,301);
					exit();
				}
				else{
					echo "<!DOCTYPE html>\n";
					echo "<html>\n";
					echo "<head>\n";
					echo "<title>" . $this->siteName . "</title>";
					echo "<link rel='stylesheet' href='index.css'>";
					echo "</head>\n";
					echo "<body>\n";
					echo "--> " . $_SESSION["formName"] . "<--";
					$this->generateHeader();
					$this->generateNavBar();
					$this->generateContent();
					echo $_SESSION["alert"];
					echo $_SESSION["check"];
					echo "</body>\n";
					echo "</html>\n";
				}
			}

			function __construct($sName,$pName,$fList,$nList,$hTable,$hLog) {
				$this->siteName = $sName;
				$this->pageName = $pName;
				$this->formList = $fList;
				$this->navList = $nList;
				$this->hasTable = $hTable;
				$this->hasLog = $hLog;
            		}
	
	}

	
?>
