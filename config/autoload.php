<?php 


// Filesystem root of the project, auto-detected from this file's location.
// Works on any machine/host (local WAMP, shared hosting, etc.) - no editing needed.
define("BASE_PATH", rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/') . '/');

// Base URL of the app, auto-detected from the current request and document root.
define("BASE_URL", (function () {
    $scheme = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/') : '';
    $appRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
    $webPath = ($docRoot !== '' && strpos($appRoot, $docRoot) === 0) ? substr($appRoot, strlen($docRoot)) : '';
    return $scheme . '://' . $host . rtrim(str_replace('\\', '/', $webPath), '/') . '/';
})());

//set your timezone here
date_default_timezone_set('asia/kolkata');





 session_start();
 require(BASE_PATH.'config/database.php'); 
 require( BASE_PATH .'classes/database.php'); 
 require( BASE_PATH .'classes/FormAssist.class.php'); 
 require(BASE_PATH.'classes/FormValidator.class.php'); 
 require( BASE_PATH .'classes/DataAccess.class.php');
 


?>    