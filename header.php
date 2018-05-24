
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
				if(gettype($this->$entryValue) === "string"){
					if($this->$entryValue === "" && $this->$required){
						$this->requiredMessage();
						$this->$error = true;
					}
					else{
						$this->clearMessage();
						$this->$error = false;
					}
				}
				else{
					$this->typeErrorMessage();
				}
				return true;
			}
			
			function verifyPassword () {
				if($this->$entryValue === ""){
					$this->requiredMessage();
					$this->$error = true;
				}
				else if(strLen($this->$entryValue) < 8) {
					$this->$message = "Password must be at least 8 characters";
					$this->$error = true;
				}
				else{
					$this->clearMessage();
					$this->$error = false;
				}
				return $this->$error;

			}
			
			function verifyConfirm ($hostForm) {
				if(gettype($this->$entryValue) === "string"){
					foreach($hostForm->$entryList as $eItr){
						if($eItr->$entryType === "password"){
							if($this->$entryValue === $eItr->$entryValue){
								$this->clearMessage();
								$this->$error = false;
								return $this->$error;
							}
							else{
								$this->$message = 	"Password confirmation '" .
													$this->$entryValue .
													"' does not match password '" .
													$eItr->$entryValue . "'";
								$this->$error = true;
								return $this->$error;
							}
						}
					}
				}
				else{
					$this->typeErrorMessage();
				}
				return $this->$error;
			}
			
			function verifyCheckbox() {
				$this->$error = true;
				if(gettype($this->$entryValue) === "string"){
					
					if($this->$entryValue == $this->$entryName) {
						$this->$entryValue = true;
					}
					else{
						$this->clearMessage();
						$this->$entryValue = false;
					}
					
					
					$this->$error = false;
				}
				else{
					$this->typeErrorMessage();
				}
				return $this->$error;
			}
			
			
			function verifyNumber () {
				$this->$error = true;
				if(gettype($this->$entryValue) === "string"){
					if($this->$entryValue === "" && $this->$required){
						$this->requiredMessage();
					}
					else if(is_numeric($this->$entryValue)) {
						$this->$error = false;
						$this->$entryValue = (int) $this->$entryValue;
					}
					else{
						$this->valueErrorMessage();
					}
				}
				else{
					$this->typeErrorMessage();
				}
				return $error;
			}

			

			function clearMessage(){
				$this->message = "";
			}

			function requiredMessage(){
				$this->message = "Value error: no value for required entry '" .
								  $this->$entryName . "'";
			}

			function valueErrorMessage(){
				$this->$message =	"Value error: bad value '" .
									$this->$entryValue .
									"' used for " . $this->$entryType .
									" entry " . $this->$entryName;
			}

			function typeErrorMessage(){
				$this->$message =	"Type error: bad type '" .
									gettype($this->$entryValue) .
									"' used for " . $this->$entryType .
									" entry " . $this->$entryName;
			}

			function makeSalt(){
				return base64_encode(mcrypt_create_iv(12,MCRYPT_DEV_URANDOM));
			}

			function load() {
				$this->$entryValue = preg_replace(	'/\s+/', '',
													$_POST[$this->$entryName]);
			}
			

			function verify() {
				if($this->$entryValue == "text" || $this->$entryValue == "textarea"){
					return $this->verifyText();
				}
				else if($this->$entryValue == "password"){
					return $this->verifyPassword();
				}
				else if($this->$entryValue == "confirm"){
					return $this->verifyPassword($this);
				}
				else if($this->$entryValue == "checkbox"){
					return $this->verifyCheckbox();
				}
				else if($this->$entryValue == "number"){
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
					if($this->error){
						echo " class='bad' ";
					}
					echo ">\n";
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
					if($this->error){
						echo " class='bad' ";
					}
					echo ">\n";
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
				$this->$error = false;
				foreach($this->$entryList as $entry){
					$this->$error = $this->$error && $entry->load();
				}
			}
			
			
			function verify() {
				$this->$error = false;
				foreach($this->$entryList as $entry) {
					$this->$error = $this->$error && $entry->verify();
				}
			}
			
			function process() {
				if($this->$error == false) {
					
					$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
					
					$call = "CALL " . $formProc . " ( ";
					$val;
					$first = true;
					foreach($this->$entryList as $entry){
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
					$result = mysqli_query($conn, $call);
					mysqli_close($conn);
					return $result;
					
					return NULL;
				}
				else{
					
					echo " <script> \n";
					foreach($this->$entryList as $entry) {
						if($this->$entry->message !== ""){
							echo " alert(" . $entry->message . ");\n";
						}
					}
					echo " </script>\n";
					
					return NULL;
				}
			}
			
			
			function generate() {
				echo "<form method='post' id='" . $this->formName . "'>\n";
				echo "<fieldset>\n";
				foreach($this->entryList as $entry) {
					$entry->generate($this);
				}
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
			var $formList;
			var $navList;
			var $hasTable;
			var $hasLog;
			
			function getText(){
				$row = mysqli_fetch_row($result);
				return $row["resultText"];
			}

			function appendLog($aText){

				if(isset($_SESSION["log"]) && $_SESSION["log"] !== NULL){
					$_SESSION["log"] .= aText;
				}
				else{
					$_SESSION["log"] = aText;
				}
			}

			
			function getActiveForm(){
				$result;
				$fName = $_POST['formName'];
				foreach($this->$formList as $fItr){
					if($fItr->formName == $fName){
						$result = $fItr;
						break;
					}
				}
				return $result;
			}
			
			
			function processForm($theForm){
				$result = NULL;
				if($theForm != NULL){
					$reqType = $_SERVER["REQUEST_METHOD"];
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
				$this->generateTableHeader($theResult);
				$this->generateTableContent($theResult);
				echo "</table>";

				mysqli_free_result($result);
			}
			
			function generateContent(){
				$formResult = NULL;
				if(reqType == "POST"){
					$theForm = $this->getActiveForm();
					$formResult = $this->processForm($theForm);
				}

				if($this->hasTable){
					$this->generateTable($formResult);
				}
				else{
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
						$this->appendLog($formResult);
						echo "<div class='right'>";
						echo $_SESSION["log"];
						echo "</div>";
					}
				}
			}
			

			function generatePage(){
				echo "<!DOCTYPE html>\n";
				echo "<html>\n";
				echo "<head>\n";
				echo "<title>" . $this->siteName . "</title>";
				echo "<link rel='stylesheet' href='index.css'>";
				echo "</head>\n";
				echo "<body>\n";
				$this->generateHeader();
				$this->generateNavBar();
				$this->generateContent();
				echo "</body>\n";
				echo "</html>\n";
			}

			function __construct($sName,$fList,$nList,$hTable,$hLog) {
				$this->siteName = $sName;
				$this->formList = $fList;
				$this->navList = $nList;
				$this->hasTable = $hTable;
				$this->hasLog = $hLog;
            		}
	
	}

	
	?>

