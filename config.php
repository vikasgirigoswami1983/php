<?php
    ini_set('display_errors', 1);
    error_reporting(-1);

    // RDS settings
    define('DB_HOST', 'database-1.clyi666mwwf0.ca-central-1.rds.amazonaws.com'); // your RDS endpoint
    define('DB_USER', 'student_user');   // RDS username
    define('DB_PASSWORD', 'vikas123');   // RDS password
    define('DB_DATABASE', 'student_db'); // Database name

    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    // echo "Connected successfully"; // optional, for testing

