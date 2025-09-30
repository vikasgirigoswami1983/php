<?php
session_start();

// Clear cart
if (isset($_GET['clear']) && $_GET['clear']) {
    unset($_SESSION['CART']);
    $_SESSION['MSGS'] = ['Your cart has been emptied.'];
    session_write_close();
    header("location: cart.php");
    exit();
}

// Remove single item
if (isset($_GET['del'])) {
    foreach ($_SESSION['CART'] as $cart_item_ID => $cart_item) {
        if ($cart_item['pd_id'] == $_GET['del']) {
            unset($_SESSION['CART'][$cart_item_ID]);
            $_SESSION['MSGS'] = ['Item removed from your cart.'];
            session_write_close();
            header("location: cart.php");
            exit();
        }
    }
}

// Add item to cart
if (isset($_GET['add'])) {
    require_once('config.php');
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
    if (!$conn) {
        die("Cannot access db: " . mysqli_connect_error());
    }

    $product_id = (int)$_GET['add'];
    $sql = "
        SELECT `tbl_product`.*, `tbl_category`.`cat_name`
        FROM `tbl_product`
        INNER JOIN `tbl_category` ON `tbl_product`.`cat_id` = `tbl_category`.`cat_id`
        WHERE `pd_id` = $product_id
        LIMIT 1
    ";
    $res = mysqli_query($conn, $sql);
    $product = mysqli_fetch_assoc($res);

    if (!isset($_SESSION['CART'])) $_SESSION['CART'] = [];

    // Check if product already in cart
    $already_in_cart = false;
    foreach ($_SESSION['CART'] as $item) {
        if ($item['pd_id'] == $product['pd_id']) {
            $already_in_cart = true;
            break;
        }
    }

    if (!$already_in_cart) {
        $_SESSION['CART'][] = $product;
        $_SESSION['MSGS'] = ['Item added to your cart.'];
    } else {
        $_SESSION['ERR_MSGS'] = ['Item is already added to your cart.'];
    }

    session_write_close();
    header("location: cart.php");
    exit();
}

include 'includes/header.php';
include 'includes/nav.php';
?>
<div id="main">
  <header class="container">
    <h3 class="page-header">Cart</h3>
  </header>
  <div class="container">
    <?php if (isset($_SESSION['CART']) && count($_SESSION['CART']) > 0) { ?>
    <div class="table-responsive">
      <table class="table products-table">
        <thead>
          <tr>
            <th>Preview</th>
            <th>Name</th>
            <th>Description</th>
            <th class="text-center">Category</th>
            <th width="100" class="text-center">Price</th>
            <th class="text-center">Remove</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $_SESSION['total'] = 0;
          foreach ($_SESSION['CART'] as $item) {
              $_SESSION['total'] += $item['pd_price'];
          ?>
          <tr>
            <td><img style="max-width:140px;" src="img/uploads/<?php echo $item['pd_image'] ?>" alt="<?php echo $item['pd_name'] ?>"></td>
            <td><?php echo $item['pd_name'] ?></td>
            <td><?php echo $item['pd_description'] ?: '<span class="text-muted">No description</span>'; ?></td>
            <td class="text-center"><?php echo $item['cat_name'] ?></td>
            <td class="text-center">&#8377; <?php echo number_format((float)$item['pd_price'], 2); ?></td>
            <td class="text-center"><a href="cart.php?del=<?php echo $item['pd_id'] ?>"><span class="glyphicon glyphicon-trash" onclick="return confirm('Are you sure you want to delete this item from your cart?');"> </span></a></td>
          </tr>
          <?php } ?>
          <tr>
            <td colspan="3"></td>
            <td><h4>Total:</h4></td>
            <td colspan="2" class="text-info">&#8377; <?php echo number_format((float)$_SESSION['total'], 2); ?></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="pull-right">
      <a href="cart.php?clear=true" class="btn btn-default">Clear <span class="glyphicon glyphicon-shopping-cart"></span></a>
      <a href="order.php" class="btn btn-primary">Place Order</a>
    </div>
    <?php } else { ?>
      <div class="alert alert-info">Oh no! Add something to your cart from the Store.</div>
    <?php } ?>
  </div>
</div>
<?php
include 'includes/footer.php';
?>

