<?php
header('Content-Type: text/plain');
ini_set('display_errors', 1);
error_reporting(E_ALL);

function testConnection($host, $port, $user, $pass, $db)
{
    echo "Testing connection to DB: '$db' User: '$user' Pass: '" . ($pass ? 'YES' : 'NO') . "' Port: $port ... ";
    try {
        $conn = new mysqli($host, $user, $pass, $db, $port);
        if ($conn->connect_error) {
            echo "FAILED: " . $conn->connect_error . "\n";
        } else {
            echo "SUCCESS!\n";
            $conn->close();
        }
    } catch (Exception $e) {
        echo "EXCEPTION: " . $e->getMessage() . "\n";
    }
}

$host = '127.0.0.1';
$port = 3307;
$user = 'root';
$pass = ''; // Empty password

echo "Diagnostic Start:\n";
testConnection($host, $port, $user, $pass, 'smart_udhar_system');
testConnection($host, $port, $user, $pass, 'smart_udhar_db');
