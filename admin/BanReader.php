<?php
// BanReader.php — sets reader status=0
include("dbcon.php");

$uid = (int)$_GET['id'];
$conn->query("UPDATE readerregistration SET status=0 WHERE userId=" . $uid);

header('location:ViewReaders.php');
?>
