<?php
// Set headers to display plain text
header('Content-Type: text/plain');

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Starting Web Database Connection Test...\n";

// Manual configuration to avoid include issues if paths are wrong, 
// but let's try to verify the config file logic too.
// We are in root, config is in config/database.php
$configFile = __DIR__ . '/config/database.php';

if (!file_exists($configFile)) {
    die("Config file not found at: $configFile\n");
}

echo "Config file found.\n";

// We can't easily include config/database.php because it defines constants 
// and if we already have them defined it might error (Notice).
// Also it starts session.
// Let's just try to parse it or just include it.
// To avoid session header issues (headers already sent), let's ignore session errors or output buffering.
ob_start();
include $configFile;
ob_end_clean();

echo "Configuration Loaded:\n";
echo "HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "\n";
echo "USER: " . (defined('DB_USER') ? DB_USER : 'NOT DEFINED') . "\n";
echo "NAME: " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "\n";
echo "PORT: " . (defined('DB_PORT') ? DB_PORT : 'NOT DEFINED') . "\n";

echo "Attempting connection...\n";

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($conn->connect_error) {
        echo "CONNECTION FAILED: " . $conn->connect_error . "\n";
        echo "Error No: " . $conn->connect_errno . "\n";
    } else {
        echo "CONNECTION SUCCESSFUL!\n";
        echo "Server Info: " . $conn->server_info . "\n";
        $conn->close();
    }
} catch (Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
