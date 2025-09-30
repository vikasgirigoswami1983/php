<?php
session_start();

// Include database connection details
require_once(__DIR__ . '/../config.php');

$user_id = $_SESSION['SESS_USER_ID'] ?? 0;

if ($user_id == 0) {
    die("No user logged in.");
}

// Connect to MySQL using mysqli
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (!$conn) {
    die("Cannot access db: " . mysqli_connect_error());
}

// Fetch user data
$res = mysqli_query($conn, "SELECT * FROM tbl_user WHERE user_id = $user_id LIMIT 1");
$user = mysqli_fetch_assoc($res);

// Fetch orders
$orders = [];
$ord_res = mysqli_query($conn, "
    SELECT `tbl_order`.*, GROUP_CONCAT(`pd_name` SEPARATOR ', ') AS `products`
    FROM `tbl_order`
    JOIN `tbl_order_item` ON `tbl_order`.`od_id` = `tbl_order_item`.`od_id`
    JOIN `tbl_product` ON `tbl_product`.`pd_id` = `tbl_order_item`.`pd_id`
    WHERE `tbl_order`.`user_id` = $user_id
    GROUP BY `tbl_order`.`od_id`
");
while ($row = mysqli_fetch_object($ord_res)) {
    $orders[] = $row;
}

// Handle password update
if (!empty($_POST)) {
    $errmsg_arr = [];
    $errflag = false;

    $password = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';

    if ($password == '') {
        $errmsg_arr[] = 'Password missing';
        $errflag = true;
    }
    if ($cpassword == '') {
        $errmsg_arr[] = 'Confirm password missing';
        $errflag = true;
    }
    if ($password !== $cpassword) {
        $errmsg_arr[] = 'Passwords do not match';
        $errflag = true;
    }
    if (strlen($password) < 6) {
        $errmsg_arr[] = 'Password is too short.';
        $errflag = true;
    }

    if ($errflag) {
        $_SESSION['ERRMSG_ARR'] = $errmsg_arr;
        session_write_close();
        header("location: ../profile.php");
        exit();
    }

    // Update password using bcrypt
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $qry = "UPDATE tbl_user SET password='$hashed_password', updated_at='" . date("Y-m-d H:i:s") . "' WHERE user_id=$user_id";
    $result = mysqli_query($conn, $qry);

    if ($result) {
        $_SESSION['MSGS'] = ['<strong>Wola!</strong> Your password was changed successfully.'];
        session_write_close();
        header("location: ../profile.php");
        exit();
    } else {
        die("Query failed: " . mysqli_error($conn));
    }
}

// Close connection
mysqli_close($conn);
?>

