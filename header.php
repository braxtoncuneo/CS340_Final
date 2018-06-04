<?php
	
		class AutoEntry {

			var $entryName;
			var $entryType;
			var $useType;
			var $entryValue;
			var $required;
			var $error;
			var $message;
			
			
			function isHidden(){
				return ($this->useType === "hidden");
			}

			function isAutoGet(){
				return ($this->useType === "autoget");
			}

			function isAutoSet(){
				return ($this->useType === "autoset");
			}

			function isRegular(){
				return ($this->useType === "regular");
			} 

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

			
			function getEntryByName($hostForm, $name){
				foreach($hostForm->entryList as $entry){
					if($entry->entryName === $name){
						return $entry;
					} 
				}
				return NULL;
			}


			function getEntryByType($hostForm, $type){
				foreach($hostForm->entryList as $entry){
					if($entry->entryType === $type){
						return $entry;
					} 
				}
				return NULL;
			}
			

			function getUserSalt($username){
				$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
				
				$call = "SELECT salt FROM player WHERE username = '" . 
					mysqli_real_escape_string($conn,$username) . "'";
					
				$table = mysqli_query($conn, $call);
				
				$res = NULL;
				if($table){
					$row = mysqli_fetch_row($table);
					//$_SESSION["check"] = $row[0] . "---";
					if($row){
						$res = $row[0] ;
					}
				}
				mysqli_close($conn);
				return $res;
				
			}


			function makeSalt($hostForm){
				$salt = base64_encode(mcrypt_create_iv(8,MCRYPT_DEV_URANDOM));
				$salt = substr($salt,0,8);
				$saltRow = $this->getEntryByName($hostForm,"salt");
				$saltRow->entryValue = $salt;
				return $salt;
			}
		

			
			function isSignup($hostForm){
				$uEntry = $this->getEntryByName($hostForm,"username");
				return (! $uEntry->isAutoSet());
			}

			function getSalt($hostForm){
				$uEntry = $this->getEntryByName($hostForm,"username");
				//$_SESSION["check"] = "??? -> " . $uEntry->entryValue;
				return $this->getUserSalt($uEntry->entryValue);
			}
			

			function verifyPassword ($hostForm) {
				if($this->entryValue === ""){
					$this->requiredMessage();
					$this->error = true;
				}
				else if(strLen($this->entryValue) < 8) {
					$this->message = "Password must be at least 8 characters";
					$this->error = true;
				}
				else{
					if($this->isSignup($hostForm)){
						$salt = $this->getSalt($hostForm);
						if($salt!==NULL){
							$this->message = "The selected username is already being used.";
							$this->error = true;
						}
						else{
							$salt = $this->makeSalt($hostForm);
							$this->entryValue = md5( $this->entryValue . $salt );
							$this->clearMessage();
							$this->error = false;
						}	
					}
					else{
						$salt = $this->getSalt($hostForm);
						if($salt !== NULL){
							$this->entryValue = md5( $this->entryValue . $salt );
							$_SESSION["check"]= "yo";
							$this->clearMessage();
							$this->error = false;
						}
						else{
							$this->message = "Username does not exist";
							$this->error = true;
						}
					}
				}
				return $this->error;

			}
			
			function verifyConfirm ($hostForm) {
				if(gettype($this->entryValue) === "string"){
					$sEnt = $this->getEntryByName($hostForm,"salt");
					$salt = $sEnt->entryValue;
					$this->entryValue = md5( $this->entryValue . $salt );
					$pEnt = $this->getEntryByName($hostForm,"password");
					if($this->entryValue === $pEnt->entryValue){
						$this->clearMessage();
						$this->error = false;
						return $this->error;
					}
					else{
						$this->message ="Password confirmation '" .
								$this->entryValue .
								"' does not match password '" .
								$pEnt->entryValue . "'";
						$this->error = true;
						return $this->error;
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


			function load() {
				if($this->isAutoGet()){
					$this->entryValue = $_SESSION[$this->entryName];
				}
				else{
					$this->entryValue = trim($_POST[$this->entryName]);
				}
				return false;
			}

			function autoSetIfNeeded(){
				if($this->isAutoSet()){
					$_SESSION[$this->entryName] = $this->entryValue;
				}
			}		
			

			function verify($hostform) {
				
				if($this->entryType === "text" || $this->entryType === "textarea"){
					return $this->verifyText();
				}
				else if($this->entryType === "password"){
					return $this->verifyPassword($hostform);
				}
				else if($this->entryType === "confirm"){
					return $this->verifyConfirm($hostform);
				}
				else if($this->entryType === "checkbox"){
					return $this->verifyCheckbox();
				}
				else if($this->entryType === "number"){
					return $this->verifyNumber();
				}
				return true;
			}

			function htmlEntryType($stdEntryType){
				$res = "";
				if( $this->entryType === "confirm" ){
					$res = "password";
				}
				else if($this->isAutoGet() || $this->isHidden()){
					$res = "hidden";
				}
				else{
					$res = $this->entryType;
				}
				return $res;
			}


			function generate($hostForm,$singlet) {
				$res = "";
				if($this->entryType === "textarea"){
					
					if($singlet === NULL){
						$res .= "<p>\n";
						$res .= "<label for='" . $this->entryName . "'>\n" .
							$this->entryName . ":</label>";
					}
					$res .= "<textarea  rows='8' cols='32' " .
							" name='" . $this->entryName . "'" .
							" form='" . $hostForm->formName . "'";
					$ErrMap = $_SESSION[$hostForm->formName];
					if($ErrMap[$this->entryName]){
						$res .= " class='bad' ";
					}
					$res .= ">\n";
					$res .= $ErrMap[$this->entryName];
					$res .= "</textarea>\n";
					if($singlet === NULL){
						$res .= "</p>\n";
					}
					
				}
				else{
					
					$realType = $this->htmlEntryType($this->entryType);
				
					if($singlet === NULL){
						$res .= "<p>\n";
						if(!($this->isHidden() || $this->isAutoGet())){
							$res .= "<label for='" . $this->entryName . "'>\n" .
								$this->entryName . ":</label>";
						}
					}

					$res .= "<input" . " type='" . $realType . "'" .
									" name='" . $this->entryName . "'" .
									" id='" . $this->entryName . "'" .
									" title='" . $this->entryName . "'";
					$ErrMap = $_SESSION[$hostForm->formName];
					if($ErrMap[$this->entryName]){
						$res .= " class='bad' ";
					}
					$res .= ">\n";
					$res .= $ErrMap[$this->entryName];
					$res .= "\n</input>\n";
					if($singlet === NULL){
						$res .= "</p>\n";
					}
					
						
				}
				return $res;
			}
			

			function __construct($eName,$eType,$eUse,$eValue,$eRequired) {
				$this->entryName = $eName;
				$this->entryType = $eType;
				$this->entryValue = $eValue;
				$this->useType = $eUse;
				$this->required = $eRequired;
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
			var $echoText;

			function getSinglet(){
				$res = NULL;
				$count = 0;
				foreach($this->entryList as $entry){
					if(!($entry->isHidden() || $entry->isAutoGet())){
						$count = $count + 1;
						$res = $entry;
					}
				}
				if($count !== 1){
					$res = NULL;
				}
				return $res;
			}


			function isPassive(){
				$res = true;
				foreach($this->entryList as $entry){
					if(!($entry->isHidden() || $entry->isAutoGet())){
						//$_SESSION["check"] = "NOT PASSIVE";
						$res = false;
					}
				}
				return $res;
			}


			function etch($eText){
				$this->echoText .= $eText;
			}
			
			
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
					$locErr = $entry->verify($this);
					$ErrMap[$entry->entryName] = $locErr;
					$this->error = $this->error || $locErr;
				}
				$_SESSION[$this->formName] = $ErrMap;
			}


			function doAutoSets(){
				foreach($this->entryList as $entry) {
					$entry->autoSetIfNeeded();
				}
			}
			

			function process() {

				$aKey = "alert";
				if( ($this->error == false ) && ($this->formProc !== NULL) ) {
					//$_SESSION["check"] = "woah";
					
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
						else if($entry->entryType === "confirm"){
							$first = true;
						}
						else{
							$call .= " \"" . $val . "\" ";
						}
						
					}
					$call .= " ) ";
					$_SESSION["check"] = $call;
					$result = mysqli_query($conn, $call);
					$_SESSION[$aKey]="";
					if(!$result){
						$_SESSION[$aKey] = "<script>" .
						"alert(\"" . mysqli_error($conn) . "\");" .
						"</script>";
					}
					$_SESSION[$aKey] .= "<script> alert(\"" . $call . "\");</script>";
					mysqli_close($conn);
					if($result){
						$_SESSION["check"] = "checks out";
					}
					return $result;
					
				}
				else if(($this->isPassive()) || ($this->error == false)){
					$this->doAutoSets();
					return NULL;
				}
				else{
					//$_SESSION["check"] = "nope";
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
				$singlet = $this->getSinglet();
				$res = "";
				$res .= "<form method='post' id='" . $this->formName . "'>\n";
				$res .= "<fieldset>\n";
				if($singlet){
					$res .= "<input type='submit' value='".
						$this->formName . "' /> ";
				}
				foreach($this->entryList as $entry) {
					$res .= $entry->generate($this,$singlet);
				}
				$res .= "<input hidden type='hidden' name='formName' " . 
					" value='" . $this->formName . "'> </input>";
				$res .= "</fieldset>\n";
				if($singlet === NULL){
					$res .= "<p>\n";
					$res .= "<input type='submit' value='submit' />";
					$res .= "<input type='reset'  value='reset' />";
					$res .= "</p>\n";
				}
				$res .= "</form>";
				return $res;
			}
			
			function __construct($fName,$fProc,$fEntryList,$fAuthorized) {
				$this->formName = $fName;
				$this->formProc = $fProc;
				$this->entryList = $fEntryList;
				$this->authorized = $fAuthorized;
				$this->error = false;
				$this->echoText = "";
			}
			
		}
		
		class AutoPage {

			var $siteName;
			var $pageName;
			var $formList;
			var $navList;
			var $autoGetList;
			var $hasTable;
			var $hasLog;
			var $echoText;


			function isPassive(){
				$result = true;
				//$_SESSION["check"] = "nopass ";
				foreach($this->formList as $form){
					//$_SESSION["check"] = "onepass";
					if( !($form->isPassive())){
						$result = false;
						//$_SESSION["check"] = "DEF NOT PASSIVE";
					}
				}
				return $result;
			}


			function etch($eText){
				$this->echoText .= $eText;
			}
			
			function getText(){
				$row = mysqli_fetch_row($result);
				return $row["resultText"];
			}

			function appendLog($aText){

				if(!isset($_SESSION[$this->pageName . "log"]) && 
					$_SESSION[$this->pageName . "log"] !== NULL){
					$_SESSION[$this->pageName . "log"] .= $aText;
				}
				else{
					$_SESSION[$this->pageName . "log"] = $aText;
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
					if($_POST || $this->isPassive()){
						//$_SESSION["check"]="start";
						$theForm->loadValues();
						$theForm->verify();
						$result = $theForm->process();
					}
				}
				else{
					return true;
				}
				return $result;
			}

			
			function checkAutoGets(){
				$res = "";
				foreach($this->autoGetList as $aGet){
					$gName = $aGet[0];
					$gFail = $aGet[1];
					if(!isset($_SESSION[$gName])){
						$res .= "<br>".$gFail."<br>";
					}
				}
				return $res;
			}

			
					
			function generateHeader(){
				$res = "";
				$res .= "<header>\n" .
						$this->siteName .
						"";

				if(isset($_SESSION["username"])){
					$res .= " - Welcome " . $_SESSION["username"];
				}
				$res .= "</header>";
				return $res;
			}
			

			function generateNavBar(){
				$res = "";
				$res .= "<nav> <ul> ";
				foreach ($this->navList as $page => $location){
					$res .= "<li><a href='$location' ".
							($page==$currentpage?" class='active'":"").
							">".$page."</a></li>";
				}
				$res .= "</ul> </nav>";
				return $res ;
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
				
				$res = "";
				if($_POST || $this->isPassive()){
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
					$res .= $table;
				}
				else {
					$table = $_SESSION[$this->pageName . "table"];
					$res .= $this->pageName . $table;
				}
				return $res;	

			}
			
			function generateContent($formResult){
				
				$res = "";
				$reqType = $_SERVER["REQUEST_METHOD"];
				$res .= $reqType;

				
				if($this->hasLog){
					$res .= "<div class='container'>";
					$res .= "<div class='left'>";
				}
				else{
					$res .= "<div class='middle'>";
				}
				foreach($this->formList as $fItr){
					$res .= $fItr->generate();
				}
				$res .= "</div>";
				
				if($formResult !== NULL){
					if($this->hasLog){
						$logRow = mysqli_fetch_row($formResult);
						$logText = $logRow[1];
						$this->appendLog($logText);
						$res .= "<div class='right'>";
						$res .= $_SESSION[$this->pageName . "log"];
						$res .= "</div>";
					}
					if($this->hasTable){
						$res .= $this->generateTable($formResult);
					}
				}
				if($this->hasLog){
					$res .= "</div>";
				}
				return $res;
			}

			function generateHTML($formResult){
					$agErr = $this->checkAutoGets(); 
					$this->etch("<!DOCTYPE html>\n");
					$this->etch("<html>\n");
					$this->etch("<head>\n");
					$this->etch("<title>" . $this->siteName . "</title>");
					$this->etch("<link rel='stylesheet' href='index.css'>");
					$this->etch("</head>\n");
					$this->etch("<body>\n");
					$this->etch("--> " . $theForm->formName . " <--");
					$this->etch($this->generateHeader());
					$this->etch("<br>");
					$this->etch($this->generateNavBar());
					$this->etch("<br>");
					if($agErr === ""){
						if($this->isPassive()){
							$theForm = $this->formList[0];
							$formResult = $this->processForm($theForm);
							$theForm->doAutoSets();
							$this->etch($this->generateTable($formResult));
						}
						else{
							$this->etch($this->generateContent($formResult));
						}
					}
					else{
						$this->etch($agErr);
					}
					$this->etch($_SESSION["alert"]);
					$this->etch($_SESSION["check"]);
					$this->etch("</body>\n");
					$this->etch("</html>\n");
			}
			

			function generatePage(){
				//$_SESSION["check"]="";
				if($_POST){
					$theForm = $this->getActiveForm();
					$_SESSION["formName"] = $theForm->formName;
					$formResult = $this->processForm($theForm);
					if($formResult !== NULL){
						$theForm->doAutoSets();
						$_SESSION["check"] = "SOME?THING";
					}
					$this->generateHTML($formResult);
					$_SESSION["content"] = $this->echoText;
					//$_SESSION["content"] = "LOL";
					header("Location: " . $_SERVER["REQUEST_URI"],true,301);
					exit();
				}
				else{
					if(isset($_SESSION["content"])){
						$this->etch($_SESSION["content"]);
						//echo $this->echoText;
						echo isset($_SESSION["content"]);
						unset($_SESSION["content"]);
					}
					else{
						$this->generateHTML(NULL);
					}// */
					echo $this->echoText;
					echo $_SESSION["check"];
					//$form0 = $this->formList[0];
					//echo "<br>" . $form0->isPassive();
					//echo "HAHA";
				}
			}

			function __construct($sName,$pName,$fList,$nList,$agList,$hTable,$hLog) {
				$this->siteName = $sName;
				$this->pageName = $pName;
				$this->formList = $fList;
				$this->navList = $nList;
				$this->autoGetList = $agList;
				$this->hasTable = $hTable;
				$this->hasLog = $hLog;
				$this->echoText = "";
            		}
	
	}

	
?>
