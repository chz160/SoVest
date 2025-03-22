<?php

// Extract the form data with POST
$newEmail = $_POST['newEmail']; 
$newPass = $_POST['newPass']; 
// Connect to the database
$servername = "localhost";
$username = "hackberr_399";
$password = "MarthaBerry!";
$dbname = "hackberr_399";
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {die("Connection failed: " . mysqli_connect_error());}

// Check to see if the user's e-mail already exists
$isUser = 0;

$query = "SELECT * FROM npedigoUser WHERE email = '$newEmail' AND password = '$newPass'";

$result = mysqli_query($conn, $query) or die ("Could not select.");
while ($row = mysqli_fetch_array($result)){
    extract($row);
    $isUser = 1;
}


// If it does, redirect them back to account page
if($isUser == 1){
    $id = $row['id'];
    header("Location: home.php?userID=" . $id);
}	

// If not, insert into database and redirect them to the login page
else{
    $query = "INSERT INTO npedigoUser (email, password, id) VALUES ('$newEmail', '$newPass', '$newID')";
    $result = mysqli_query($conn, $query) or die ("Could not insert.");
    header("Location: login.php");
}



?>