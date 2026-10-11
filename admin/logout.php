<?php
require('../config/autoload.php');
session_unset();
session_destroy();
header("Location: login.php");
exit();
?>
