<!DOCTYPE html>
<?php
		$currentpage="Sign Up";
		include "pages.php";

?>
<html>
	<head>
		<title>Sign Up</title>
		<link rel="stylesheet" href="index.css">
		<script type = "text/javascript"  src = "verifyInput.js" > </script>
	</head>
<body>


<?php
	include "header.php";
	$msg = "Create an account";

	include 'connectvars.php';

	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	if (!$conn) {
		die('Could not connect: ' . mysql_error());
	}
	if ($_SERVER["REQUEST_METHOD"] == "POST") {

		$username = mysqli_real_escape_string($conn, $_POST['username']);
		$password = mysqli_real_escape_string($conn, $_POST['password']);
		$salt = makeSalt();

		$querySalt = "SELECT salt FROM Users where username='$username'";
		$resultSalt = mysqli_query($conn, $querySalt);
		if ($row = mysqli_fetch_assoc($resultSalt)) {
			$salt = $row['salt'];
			$queryAct =  "SELECT username FROM Users where username='$username' AND password=MD5('$password$salt')";
			$resultAct = mysqli_query($conn, $queryAct);
			if($rowAct= mysqli_fetch_assoc($resultAct)){
				$msg =  "Successfully logged in. Welcome, $username !<p>";
			} else{
				$msg ="<h2>Log In Failed'</h2> The username/password combination entered does not correspond with our records<p>";
			}
		}
		else {
			$msg ="<h2>Log In Failed</h2> The username/password combination entered does not correspond with our records<p>";
		}
}
// close connection
mysqli_close($conn);

?>
	<section>
    <h2> <?php echo $msg; ?> </h2>

<form method="post" id="loginForm">
<fieldset>
	<legend>Account Information:</legend>
    <p>
        <label for="username">username:</label>
        <input type="text" class="required" name="username" id="username" title="username should be 1-20 characters">
    </p>
    <p>
        <label for="password">password:</label>
        <input type="text" class="required" name="password" id="password" title="password should be 1-20 characters">
    </p>
</fieldset>
      <p>
        <input type = "submit"  value = "Submit" />
        <input type = "reset"  value = "Clear Form" />
      </p>
</form>
</body>
</html>

