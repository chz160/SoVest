<?php

// Extract the form data with POST
$tryEmail = $_POST['tryEmail'];
$tryPass = $_POST['tryPass'];

// Connect to the database
$servername = "localhost";
$username = "hackberr_399";
$password = "MarthaBerry!";
$dbname = "hackberr_399";
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {die("Connection failed: " . mysqli_connect_error());}

// Check to see if the user's e-mail is in the database
$isUser = 0;

$query = "SELECT * FROM npedigoUser WHERE email = '$tryEmail' ";
$result = mysqli_query($conn, $query) or die ("Could not select.");
while ($row = mysqli_fetch_array($result)){
    extract($row);
    $isUser = 1;
}
// If not, insert into database and redirect them to the login page
if($isUser == 0){
    header("Location: login.php");
}

// If it does, compare the guessed password with the saved password
if($tryPass == $password){
    setcookie("userID", $id, time() + (86400 * 30), "/"); // Sets cookie for 30 days
    header("Location: home.php");
}

// If the guess does not match the password, redirect them to the login page
else{
    header("Location: login.php");
}

// If it does match, redirect them to the home page



?>