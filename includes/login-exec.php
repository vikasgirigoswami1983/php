<?php
// Start session - must be at the very top
session_start();

// Include database connection details
require_once(__DIR__ . '/../config.php');

// Array to store validation errors
$errmsg_arr = [];
$errflag = false;

// Connect to MySQL server using mysqli
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (!$conn) {
    die('Failed to connect to server: ' . mysqli_connect_error());
}

// Function to sanitize values received from the form. Prevents SQL injection
function clean($conn, $str) {
    $str = trim($str);
    return mysqli_real_escape_string($conn, $str);
}

// Sanitize the POST values
$username = clean($conn, $_POST['username'] ?? '');
$password = $_POST['password'] ?? ''; // do NOT escape password

// Input Validations
if ($username == '') {
    $errmsg_arr[] = 'Please provide a username.';
    $errflag = true;
}
if ($password == '') {
    $errmsg_arr[] = 'Please enter the password.';
    $errflag = true;
}

// If there are input validations, redirect back to the login form
if ($errflag) {
    $_SESSION['ERRMSG_ARR'] = $errmsg_arr;
    session_write_close();
    header("Location: ../login.php");
    exit();
}

// Fetch user by username
$qry = "SELECT * FROM tbl_user WHERE user_name='$username' LIMIT 1";
$result = mysqli_query($conn, $qry);

if ($result && mysqli_num_rows($result) == 1) {
    $member = mysqli_fetch_assoc($result);

    // Verify password using bcrypt
    if (password_verify($password, $member['password'])) {
        // Login successful
        session_regenerate_id(true); // safer
        $_SESSION['SESS_USER_ID'] = $member['user_id'];
        $_SESSION['SESS_USERNAME'] = $member['user_name'];
        $_SESSION['SESS_IS_ADMIN'] = $member['user_is_admin'];
        session_write_close();
        header("Location: ../index.php");
        exit();
    } else {
        // Wrong password
        $_SESSION['ERRMSG_ARR'] = ['<b>Oh no!</b> Incorrect username or password. Please try again.'];
        session_write_close();
        header("Location: ../login.php");
        exit();
    }
} else {
    // Username not found
    $_SESSION['ERRMSG_ARR'] = ['<b>Oh no!</b> Incorrect username or password. Please try again.'];
    session_write_close();
    header("Location: ../login.php");
    exit();
}

// Close the connection
mysqli_close($conn);

