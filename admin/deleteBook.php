<?php
require('../config/autoload.php');

$dao = new DataAccess();
$bid = isset($_GET['bid']) ? intval($_GET['bid']) : 0;

if ($bid) {
    $dao->update(['status' => 0], 'books', "bid='$bid'");
}

echo "<script>alert('Book deleted successfully.'); window.location.href='Books.php';</script>";
exit();
