<?php
require('../config/autoload.php');

$dao = new DataAccess();
$cid = isset($_GET['cid']) ? intval($_GET['cid']) : 0;
if ($cid) {
    $dao->update(['status' => 0], 'category', "cid='$cid'");
}
echo "<script>alert('Category deleted successfully.'); window.location.href='Category.php';</script>";
exit();
