<?php
include("dbcon.php");

$bid = $_GET['id'];
$sql = "UPDATE authorregistration SET status=3 WHERE authid=" . $bid;
$conn->query($sql);

header('location:viewusers.php');
?>
