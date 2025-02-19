<?php
// Retrieve the userID cookie. If not set, redirect the user to the login page. If it is set, save it as $userID
if(!isset($_COOKIE["userID"])){header("Location: login.php");}
else {$userID = $_COOKIE["userID"];}

$servername = "localhost";
$username = "hackberr_399";
$password = "MarthaBerry!";
$dbname = "hackberr_399";
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {die("Connection failed: " . mysqli_connect_error());}

$newTime = $_POST['newTime']; 
$newInvolvement = $_POST['newInvolvement']; 
$stopTime = strtotime($newTime);


$query = "SELECT * FROM npedigoLog WHERE userID = '$userID' ORDER BY id DESC LIMIT 0,1";
     $result = mysqli_query($conn, $query) or die ("Could not select.");
     while ($row = mysqli_fetch_array($result)){
         extract($row);
     }	

     $query = "UPDATE npedigoLog SET stop = '$stopTime' WHERE id = '$id' ";
     $result = mysqli_query($conn, $query) or die ("Could not update.");

     $query = "UPDATE npedigoLog SET involvement = '$newInvolvement' WHERE id = '$id' ";
     $result = mysqli_query($conn, $query) or die ("Could not update.");

     header("Location: home.php");

?>