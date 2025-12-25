<?php
header('Content-Type: text/plain');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Final Connection Test\n";
$configPath = __DIR__ . '/config/database.php';
echo "Loading config from: $configPath\n";

// Read content of config file to verify on disk content
echo "File content of DB_NAME line:\n";
$lines = file($configPath);
foreach ($lines as $line) {
    if (strpos($line, 'DB_NAME') !== false) {
        echo trim($line) . "\n";
    }
}

require_once $configPath;

echo "Loaded Constants:\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_PASS: '" . DB_PASS . "'\n";

$conn = getDBConnection();

if ($conn->connect_error) {
    echo "Connection Failed: " . $conn->connect_error . "\n";
} else {
    echo "Connection Successful via getDBConnection()!\n";
    echo "Host Info: " . $conn->host_info . "\n";
}
