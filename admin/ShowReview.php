<?php
// ShowReview.php — sets review status=1 (visible)
include("dbcon.php");

$rid = (int)$_GET['id'];
$conn->query("UPDATE reviews SET status=1 WHERE reviewid=" . $rid);

header('location:ViewReviews.php');
?>
