<?php
// HideReview.php — sets review status=0 (hidden by admin)
include("dbcon.php");

$rid = (int)$_GET['id'];
$conn->query("UPDATE reviews SET status=0 WHERE reviewid=" . $rid);

header('location:ViewReviews.php');
?>
