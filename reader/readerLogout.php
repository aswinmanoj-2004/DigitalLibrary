<?php
require('../config/autoload.php');
session_destroy();
header('location: ../index.php');
exit();
?>
