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
    // Check if already in wishlist (could be soft-deleted)
    $existing = $dao->query("SELECT * FROM wishlist WHERE userId='$readerId' AND bid='$bid'");
    if ($existing) {
        // Reactivate if removed before
        $dao->update(['status' => 1], "wishlist", "wid='" . $existing[0]['wid'] . "'");
    } else {
        $data = [
            'bid'       => $bid,
            'userId'    => $readerId,
            'AddedDate' => date('Y-m-d H:i:s'),
            'status'    => 1,
        ];
        $dao->insert($data, "wishlist");
    }
    echo "<script>alert('Book added to wishlist!');</script>";
}

if ($from == 'detail') {
    header("location: bookDetail.php?bid=$bid");
} else {
    header("location: bookList.php");
}
exit();
?>
