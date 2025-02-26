<?php
setcookie("userID", 0, time() -3600, "/"); // Deletes the cookie
header("Location: index.php");
?> 


