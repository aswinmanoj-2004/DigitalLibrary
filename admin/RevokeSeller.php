<?php
// RevokeSeller.php — sets approved author back to status=3 (rejected/revoked)
include("dbcon.php");

$id  = (int)$_GET['id'];
$sql = "UPDATE authorregistration SET status=3 WHERE authid=" . $id;
$conn->query($sql);

header('location:AllSellers.php');
?>
