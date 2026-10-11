<?php
// UnbanReader.php — sets reader status=1
include("dbcon.php");

$uid = (int)$_GET['id'];
$conn->query("UPDATE readerregistration SET status=1 WHERE userId=" . $uid);

header('location:ViewReaders.php');
?>
