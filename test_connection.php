<?php
// Test database connection
$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';
$db_name = 'smart_udhar_system';

try {
    $conn = new mysqli($host, $user, $pass, $db_name, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "Database connection successful!<br>";
    
    // Test if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "Users table exists.<br>";
        
        // Test if there are any users
        $user_result = $conn->query("SELECT COUNT(*) as count FROM users");
        $user_count = $user_result->fetch_assoc();
        echo "Number of users in database: " . $user_count['count'] . "<br>";
    } else {
        echo "Users table does not exist. You need to import the database schema.<br>";
        echo "Please import the database from database/smart_udhar_system.sql file.<br>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Database connection error: " . $e->getMessage() . "<br>";
    echo "Please make sure:<br>";
    echo "1. XAMPP/Apache and MySQL services are running<br>";
    echo "2. The database 'smart_udhar_system' exists<br>";
    echo "3. The database contains the required tables<br>";
}
?>