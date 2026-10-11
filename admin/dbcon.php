
 <?php
// Database credentials come from a single source: config/database.php
// (edit that file once when deploying to a new host).
require_once __DIR__ . '/../config/database.php';

// Create connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

?> 