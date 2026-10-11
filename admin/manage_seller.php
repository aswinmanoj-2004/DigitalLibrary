<?php
// admin/manage_seller.php - Handles seller approval, rejection, and revocation in one place
include("dbcon.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($id > 0) {
    if ($action === 'approve') {
        $sql = "UPDATE authorregistration SET status=2 WHERE authid = " . $id;
        $conn->query($sql);
        header('Location: viewusers.php');
        exit();
    } elseif ($action === 'reject') {
        $sql = "UPDATE authorregistration SET status=3 WHERE authid = " . $id;
        $conn->query($sql);
        header('Location: viewusers.php');
        exit();
    } elseif ($action === 'revoke') {
        $sql = "UPDATE authorregistration SET status=3 WHERE authid = " . $id;
        $conn->query($sql);
        header('Location: AllSellers.php');
        exit();
    }
}

// Fallback redirect if parameters are invalid
header('Location: Dashboard.php');
exit();
?>
