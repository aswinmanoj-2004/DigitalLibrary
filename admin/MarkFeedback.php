<?php
// MarkFeedback.php — marks feedback as read (status=2)
include("dbcon.php");

$fid = (int)$_GET['id'];
$conn->query("UPDATE feedback SET status=2 WHERE feedid=" . $fid);

header('location:ViewFeedback.php');
?>
