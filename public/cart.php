<?php
require_once __DIR__ . '/../includes/header.php';
requireLogin(); // must be logged in to use DB-backed cart

$user_id = $_SESSION['user']['id'];
$errors = [];
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verify_csrf($_POST['csrf'] ?? '')) $errors[] = 'Invalid CSRF token.';
  $action = $_POST['action'] ?? '';
  $pid    = (int)($_POST['product_id'] ?? 0);
  $qty    = max(1, (int)($_POST['quantity'] ?? 1));

  if (!$errors && $action === 'add' && $pid > 0) {
    // insert or update
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,?)
                            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
    $stmt->bind_param('iii', $user_id, $pid, $qty);
    $stmt->execute(); $stmt->close();
    $notice = 'Added to cart.';
  }

  if (!$errors && $action === 'update' && $pid > 0) {
    $stmt = $conn->prepare("UPDATE cart SET quantity=? WHERE user_id=? AND product_id=?");
    $stmt->bind_param('iii', $qty, $user_id, $pid);
    $stmt->execute(); $stmt->close();
    $notice = 'Cart updated.';
  }

  if (!$errors && $action === 'remove' && $pid > 0) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id=? AND product_id=?");
    $stmt->bind_param('ii', $user_id, $pid);
    $stmt->execute(); $stmt->close();
    $notice = 'Item removed.';
  }
}

$sql = "SELECT c.product_id, c.quantity, p.name, p.price, p.image
        FROM cart c INNER JOIN products p ON p.id=c.product_id
        WHERE c.user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total = 0;
foreach ($items as $it) $total += $it['price'] * $it['quantity'];
?>
<h2>Your Cart</h2>
<?php if ($notice): ?><p style="color:green"><?= htmlspecialchars($notice) ?></p><?php endif; ?>
<?php foreach ($errors as $e): ?><p style="color:#b00"><?= htmlspecialchars($e) ?></p><?php endforeach; ?>

<?php if (!$items): ?>
  <p>No items in cart.</p>
<?php else: ?>
  <div class="grid">
  <?php foreach ($items as $it): ?>
    <div class="card">
      <img src="/catshop/assets/images/<?= htmlspecialchars($it['image'] ?: 'placeholder.jpg') ?>" alt="">
      <h3><?= htmlspecialchars($it['name']) ?></h3>
      <p>₱<?= number_format($it['price'],2) ?> × <?= (int)$it['quantity'] ?></p>
      <form method="post" style="display:flex; gap:6px;">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="product_id" value="<?= (int)$it['product_id'] ?>">
        <input type="hidden" name="action" value="update">
        <input type="number" name="quantity" min="1" value="<?= (int)$it['quantity'] ?>" style="width:70px">
        <button type="submit">Update</button>
      </form>
      <form method="post" style="margin-top:6px;">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="product_id" value="<?= (int)$it['product_id'] ?>">
        <input type="hidden" name="action" value="remove">
        <button type="submit">Remove</button>
      </form>
    </div>
  <?php endforeach; ?>
  </div>
  <h3>Total: ₱<?= number_format($total,2) ?></h3>
  <a class="btn" href="/catshop/public/checkout.php">Proceed to Checkout</a>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
