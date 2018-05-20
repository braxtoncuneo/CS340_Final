

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

			}

			function verifyNumber () {

			}

			function verify() {
				switch($entryValue){
					case "text":
						return verifyText();
						break;
					case "password":
						return verifyPassword();
						break;
					case "submit":
						return verifySubmit();
						break;
					case "radio":
						return verifyRadio();
						break;
					case "checkbox":
						return verifyCheckbox();
						break;
					case "number":
						return verifyNumber();
						break;
					default :
						return true;
						break;
				}
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

			function addEntry() {

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
					mysqli_close($conn);
					return
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

			}

            function __construct($fName,$fProc,$fEntryList,$fAuthorized) {
				$formName = $fName;
				$formProc = $fProc;
				$entryList = $fEntryList;
				$authorized = $fAuthorized;
				$error = false;
            }

		}

		function makeSalt(){
			return base64_encode(mcrypt_create_iv(12,MCRYPT_DEV_URANDOM));
		}

		function handleRequest($theForm) {
			switch($_SERVER["REQUEST_METHOD"]){
				case "POST"
					$theForm->verify();
					$theForm->process();
					$theForm->generate();
					break;
				case "GET":
					$theForm->generate();
					break;
			}
		}


	?>

