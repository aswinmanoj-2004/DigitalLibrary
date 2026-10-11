<?php
require('../config/autoload.php');
if (!isset($_SESSION['reader_id'])) {
    header('location: readerLogin.php'); exit();
}

$dao      = new DataAccess();
$readerId = $_SESSION['reader_id'];
$bid      = isset($_GET['bid']) ? intval($_GET['bid']) : 0;

if (!$bid) { header('location: bookList.php'); exit(); }

$books = $dao->query("SELECT * FROM books WHERE bid='$bid' AND status=1");
if (!$books) { header('location: bookList.php'); exit(); }
$book = $books[0];

if ($book['is_paid'] == 'paid') {
    $purchases = $dao->query("SELECT * FROM purchases WHERE userId='$readerId' AND bid='$bid' AND payment_status='completed' AND status=1");
    if (!$purchases) {
        header('location: bookList.php');
        exit();
    }
}

// Log download if not already logged
$existing = $dao->query("SELECT * FROM downloads WHERE userId='$readerId' AND bid='$bid'");
if (!$existing) {
    $data = [
        'userId'        => $readerId,
        'bid'           => $bid,
        'download_date' => date('Y-m-d H:i:s'),
        'status'        => 1,
    ];
    $dao->insert($data, "downloads");
}

// Redirect to the actual PDF file
header("location: ../uploadpdf/" . $book['story']);
exit();
?>
