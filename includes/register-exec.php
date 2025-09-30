<?php
// Start session
session_start();

// Include database connection details
require_once(__DIR__ . '/../config.php');

// Array to store validation errors
$errmsg_arr = array();

// Validation error flag
$errflag = false;

// Connect to MySQL server with mysqli
$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (!$con) {
    die('Failed to connect to server: ' . mysqli_connect_error());
}

// Function to sanitize values received from the form. Prevents SQL injection
function clean($con, $str) {
    $str = trim($str);
    return mysqli_real_escape_string($con, $str);
}

// Sanitize the POST values
$username = clean($con, $_POST['username']);
$email    = clean($con, $_POST['email']);
$password = clean($con, $_POST['password']);
$cpassword = clean($con, $_POST['cpassword']);

// Input Validations
if ($username == '') {
    $errmsg_arr[] = 'Username missing';
    $errflag = true;
}
if ($email == '') {
    $errmsg_arr[] = 'Email missing';
    $errflag = true;
}
if ($password == '') {
    $errmsg_arr[] = 'Password missing';
    $errflag = true;
}
if ($cpassword == '') {
    $errmsg_arr[] = 'Confirm password missing';
    $errflag = true;
}
if (strcmp($password, $cpassword) != 0) {
    $errmsg_arr[] = 'Passwords do not match';
    $errflag = true;
}
if (strlen($password) < 6) {
    $errmsg_arr[] = 'Password is too short.';
    $errflag = true;
}
if (strpos($email, "@") === false || strpos($email, ".") === false) {
    $errmsg_arr[] = 'Enter a valid email ID';
    $errflag = true;
}

// Check for duplicate login ID
if ($username != '') {
    $qry = "SELECT * FROM tbl_user WHERE user_name='$username'";
    $result = mysqli_query($con, $qry);
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $errmsg_arr[] = 'Username already in use';
            $errflag = true;
        }
        mysqli_free_result($result);
    } else {
        die("Query failed: " . mysqli_error($con));
    }
}

// If there are input validations, redirect back to the registration form
if ($errflag) {
    $_SESSION['ERRMSG_ARR'] = $errmsg_arr;
    session_write_close();
    header("location: ../register.php");
    exit();
}

// Check if username contains 'admin' → mark as admin
$is_admin = preg_match("/(.*)admin/", $username) ? 1 : 0;

// Hash password securely (better than md5)
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Create INSERT query
$qry = "INSERT INTO tbl_user(user_name, password, user_email, created_at, updated_at, user_is_admin) 
        VALUES('$username', '$hashedPassword', '$email', NOW(), NOW(), $is_admin)";

$result = mysqli_query($con, $qry);

// Check whether the query was successful or not
if ($result) {
    $_SESSION['MSGS'] = array('<b>Whoa you are awesome!</b> Registration Successful!');
    session_write_close();
    header("location: ../index.php");
    exit();
} else {
    die("Query failed: " . mysqli_error($con));
}

// Close connection
mysqli_close($con);
?>

