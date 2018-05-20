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
		$firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
		$lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
		$email = mysqli_real_escape_string($conn, $_POST['email']);
		$password = mysqli_real_escape_string($conn, $_POST['password']);
		$age = mysqli_real_escape_string($conn, $_POST['age']);
		$salt = makeSalt();

		if($username != ""){
			$queryIn = "SELECT * FROM Users where username='$username' ";
			$resultIn = mysqli_query($conn, $queryIn);
			if (mysqli_num_rows($resultIn)> 0) {
				$msg ="<h2>Can't Add to Table</h2> The username '$username' is already taken<p>";
			} else {
				$query = "INSERT INTO Users (username, firstName, lastName, email, password, age, salt) VALUES ('$username', '$firstName', '$lastName', '$email', MD5('$password$salt'), '$age', '$salt')";
				if(mysqli_query($conn, $query)){
					$msg =  "Record added successfully.<p>";
				} else{
					echo "ERROR: Could not able to execute $query. " . mysqli_error($conn);
				}
			}
		}


}
// close connection
mysqli_close($conn);

?>
	<section>
    <h2> <?php echo $msg; ?> </h2>

<form method="post" id="signupForm">
<fieldset>
	<legend>Account Information:</legend>
    <p>
        <label for="username">username:</label>
        <input type="text" required class="required" name="username" id="username" title="username should be 1-20 characters">
    </p>
    <p>
        <label for="firstName">firstName:</label>
        <input type="text" required class="required" name="firstName" id="firstName" title="firstName should be 1-20 characters">
    </p>
    <p>
        <label for="lastName">lastName:</label>
        <input type="text" required class="required" name="lastName" id="lastName" title="lastName should be 1-20 characters">
    </p>
    <p>
        <label for="email">email:</label>
        <input type="text" required class="required" name="email" id="email" title="email should be 1-20 characters">
    </p>
    <p>
        <label for="password">password:</label>
        <input type="text" required class="required" name="password" id="password" title="password should be 1-20 characters">
    </p>

    <p>
        <label for="age">age:</label>
        <input type="number" class="required" name="age" id="age" title="age should be 1-20 characters">
    </p>
</fieldset>

      <p>
        <input type = "submit"  value = "Submit" />
        <input type = "reset"  value = "Clear Form" />
      </p>
</form>
</body>
</html>
