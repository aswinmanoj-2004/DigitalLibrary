<?php
require('../config/autoload.php');
if (!isset($_SESSION['reader_id'])) {
    header('location: readerLogin.php'); exit();
}

$dao      = new DataAccess();
$readerId = $_SESSION['reader_id'];
$bid      = isset($_GET['bid']) ? intval($_GET['bid']) : 0;
$from     = isset($_GET['from']) ? $_GET['from'] : '';

if ($bid) {
    $dao->update(['status' => 0], "wishlist", "userId='$readerId' AND bid='$bid'");
    echo "<script>alert('Book removed from wishlist.');</script>";
}

if ($from == 'detail') {
    header("location: bookDetail.php?bid=$bid");
} elseif ($from == 'wishlist') {
    header("location: wishlist.php");
} else {
    header("location: bookList.php");
}
exit();
?>
