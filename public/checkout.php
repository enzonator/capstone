<?php
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$user_id = $_SESSION['user']['id'];
// Load cart
$sql = "SELECT c.product_id, c.quantity, p.price, p.stock FROM cart c
        JOIN products p ON p.id = c.product_id
        WHERE c.user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (!$items) {
  echo "<p>Your cart is empty.</p>";
  require_once __DIR__ . '/../includes/footer.php'; exit;
}

// compute total and validate stock
$total = 0; $stockError = false;
foreach ($items as $it) {
  if ($it['quantity'] > $it['stock']) $stockError = true;
  $total += $it['price'] * $it['quantity'];
}

if ($stockError) {
  echo "<p style='color:#b00'>One or more items exceed stock. Update your cart.</p>";
  require_once __DIR__ . '/../includes/footer.php'; exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verify_csrf($_POST['csrf'] ?? '')) die('Bad CSRF');

  // Create order
  $conn->begin_transaction();
  try {
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?,?, 'Pending')");
    $stmt->bind_param('id', $user_id, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert items + reduce stock
    $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)");
    $stmtStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id=?");

    foreach ($items as $it) {
      $stmtItem->bind_param('iiid', $order_id, $it['product_id'], $it['quantity'], $it['price']);
      $stmtItem->execute();

      $stmtStock->bind_param('ii', $it['quantity'], $it['product_id']);
      $stmtStock->execute();
    }
    $stmtItem->close(); $stmtStock->close();

    // Clear cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id=?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute(); $stmt->close();

    $conn->commit();
    echo "<h2>Order placed!</h2><p>Order #$order_id (COD). We’ll contact you soon.</p>";
    require_once __DIR__ . '/../includes/footer.php'; exit;

  } catch (Throwable $e) {
    $conn->rollback();
    echo "<p style='color:#b00'>Checkout failed. Please try again.</p>";
  }
}
?>
<h2>Checkout</h2>
<p>Total: <strong>₱<?= number_format($total,2) ?></strong></p>
<form method="post">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
  <button type="submit">Place Order (Cash on Delivery)</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
