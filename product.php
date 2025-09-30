<?php
session_start();

if (!isset($_GET['id'])) {
    header("location: store.php");
    exit();
}

require_once('config.php');

// Connect to MySQL using mysqli
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (!$conn) {
    die("Cannot access db: " . mysqli_connect_error());
}

// Get product ID safely
$product_id = (int)$_GET['id'];

// Fetch the product
$sql = "
    SELECT `tbl_product`.*, `tbl_category`.`cat_name`
    FROM `tbl_product`
    INNER JOIN `tbl_category` ON `tbl_product`.`cat_id` = `tbl_category`.`cat_id`
    WHERE `pd_id` = $product_id
    LIMIT 1
";
$res = mysqli_query($conn, $sql);

$product = null;
if ($res && mysqli_num_rows($res) == 1) {
    $product = mysqli_fetch_object($res);
} else {
    die("Product not found.");
}

mysqli_close($conn);
?>

<?php
include 'includes/header.php';
include 'includes/nav.php';
?>
<div id="main">
    <header class="container">
        <ol class="breadcrumb">
            <li><a href="store.php">Store</a></li>
            <li><a href="store.php?category=<?php echo $product->cat_id ?>"><?php echo $product->cat_name ?></a></li>
            <li class="active"><?php echo $product->pd_name ?></li>
        </ol>
    </header>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <img src="img/uploads/<?php echo $product->pd_image; ?>" class="img-responsive">
            </div>
            <div class="col-md-8">
                <h3><?php echo $product->pd_name ?></h3>
                <hr>
                <?php setlocale(LC_MONETARY,'en_US'); ?>
                <h4><strong>Price:</strong> &#8377; <?php echo number_format((float)$product->pd_price, 2); ?></h4>
                <p><?php echo $product->pd_description ? $product->pd_description : '<span class="text-muted">No description</span>'; ?></p>
                <p>Available Quantity: <span class="badge"><?php echo $product->pd_qty ?></span></p>
                <a href="cart.php?add=<?php echo $product->pd_id; ?>" class="btn btn-primary">Add to Cart</a>
            </div>
        </div>
    </div>
</div>
<?php
include 'includes/footer.php';
?>

