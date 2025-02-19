<?php
$id = $_GET['id'];

$servername = "localhost";
$username = "hackberr_399";
$password = "MarthaBerry!";
$dbname = "hackberr_399";
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {die("Connection failed: " . mysqli_connect_error());}

$query = "DELETE FROM npedigoLog WHERE id = '$id'";
$result = mysqli_query($conn, $query) or die ("Could not update.");

header("Location: deleteWork.php");

?>