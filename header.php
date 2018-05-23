

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<header>
		Quest Database - <em>Welcome <span id="username"><?php echo $user;?></span>!</em>
	</header>
	<nav>
		<ul>
		<?php
		foreach ($content as $page => $location){
			echo "<li><a href='$location?user=".$user."' ".($page==$currentpage?" class='active'":"").">".$page."</a></li>";
		}
		?>
		</ul>

	</nav>

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

			var $formList;
			var $hasTable;
			var	$hasLog;


		}


		function getActiveForm(){
			return $_POST['formName'];
		}


		function handleRequest($formList) {
			var $theForm = NULL;
			var $activeForm = getActiveForm();
			foreach($formList as $fItr){
				if($fItr->formName == $activeForm){
					$theForm = $fItr;
					break;
				}
			}
			var $result = NULL;

			if($theForm != NULL){
				var $reqType = $_SERVER["REQUEST_METHOD"];
				if(reqType == "POST"){
					$theForm->loadValues();
					$theForm->verify();
					$result = $theForm->process();
					foreach($formList as $fItr){
						$fItr->generate();
					}
					break;
				}
				else if(reqType == "GET"){
					foreach($formList as $fItr){
						$fItr->generate();
					}
					break;
				}
			}

		}

		function displayTable($tableHeader,$theResult) {
			if (!$theResult) {
				die("Query failed");
			}

			$fields_num = mysqli_num_fields($result);
			echo "<h1>" . $tableHeader . ":</h1>";
			echo "<table id='t01' border='1'><tr>";

			// printing table headers
			for($i=0; $i<$fields_num; $i++) {
				$field = mysqli_fetch_field($result);
				echo "<td><b>$field->name</b></td>";
			}
			echo "</tr>\n";
			while($row = mysqli_fetch_row($result)) {
				echo "<tr>";
				foreach($row as $cell)
					echo "<td>$cell</td>";
				echo "</tr>\n";
			}

			mysqli_free_result($result);

		}

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

	?>

