<?php
// Simple test script to run verify_db_schema.php and capture its output
$output = shell_exec('php verify_db_schema.php');
echo "Output from verify_db_schema.php:\n\n";
echo str_replace('<br>', "\n", $output);
?>
