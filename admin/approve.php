<?php	
include("dbcon.php");
$bid = $_GET['id'];
$sql = "update authorregistration set status=2 where  authid=".$bid;

$conn->query($sql);

 header('location:viewusers.php');



?>