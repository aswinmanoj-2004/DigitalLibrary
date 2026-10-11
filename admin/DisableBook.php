<?php
// DisableBook.php — sets book status=0 (disabled by admin)
include("dbcon.php");

$bid  = (int)$_GET['id'];
$from = $_GET['from'] ?? '';   // 'seller' or 'books'
$authid = (int)($_GET['authid'] ?? 0);

$conn->query("UPDATE books SET status=0 WHERE bid=" . $bid);

if ($from == 'seller' && $authid > 0) {
    header("location:SellerBooks.php?id=$authid");
} else {
    header('location:Books.php');
}
?>
