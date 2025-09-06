<?php
// Auth first (defines isAdmin(), requireAdmin(), starts session)
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

// DB
require_once __DIR__ . '/../config/db.php';

// Optional search
$q = trim($_GET['q'] ?? '');

// Sorting
$sort = $_GET['sort'] ?? 'recent';
switch ($sort) {
    case 'id':
        $orderBy = "p.id ASC";
        break;
    case 'price_asc':
        $orderBy = "p.price ASC";
        break;
    case 'price_desc':
        $orderBy = "p.price DESC";
        break;
    case 'recent':
    default:
        $orderBy = "p.created_at DESC";
        break;
}

// Build query
if ($q !== '') {
    $like = '%' . $q . '%';
    $sql = "SELECT p.id, p.name, p.type, p.breed, p.price, p.created_at, u.username
            FROM pets p
            JOIN users u ON u.id = p.user_id
            WHERE p.name LIKE ? OR p.breed LIKE ? OR p.type LIKE ? OR u.username LIKE ?
            ORDER BY $orderBy";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $like, $like, $like, $like);
    $stmt->execute();
    $pets = $stmt->get_result();
} else {
    $sql = "SELECT p.id, p.name, p.type, p.breed, p.price, p.created_at, u.username
            FROM pets p
            JOIN users u ON u.id = p.user_id
            ORDER BY $orderBy";
    $pets = $conn->query($sql);
}

// Use admin header if you created it, otherwise fall back to the normal header
$adminHeader = __DIR__ . '/../includes/admin_header.php';
if (file_exists($adminHeader)) {
    include $adminHeader;
} else {
    include __DIR__ . '/../includes/header.php';
}
?>

<div class="container mt-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="m-0">🐾 Manage Pets</h2>

    <form class="d-flex" method="get" action="">
      <input class="form-control me-2" type="search" name="q" placeholder="Search pets, breed, type, seller…" value="<?= htmlspecialchars($q) ?>">
      <button class="btn btn-primary" type="submit">Search</button>
    </form>
  </div>

  <!-- Sorting Dropdown -->
  <div class="d-flex justify-content-end mb-3">
    <form method="get" class="d-flex">
      <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
      <label class="me-2 align-self-center">Sort by:</label>
      <select name="sort" class="form-select me-2" onchange="this.form.submit()">
        <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Most Recent</option>
        <option value="id" <?= $sort === 'id' ? 'selected' : '' ?>>ID</option>
        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low → High</option>
        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High → Low</option>
      </select>
      <noscript><button type="submit" class="btn btn-primary">Sort</button></noscript>
    </form>
  </div>

  <div class="table-responsive shadow-sm rounded">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Type</th>
          <th>Breed</th>
          <th>Seller</th>
          <th>Price (₱)</th>
          <th>Created</th>
          <th style="width: 120px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($pets && $pets->num_rows > 0): ?>
          <?php while ($row = $pets->fetch_assoc()): ?>
            <tr>
              <td><?= (int)$row['id'] ?></td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars(ucfirst($row['type'])) ?></td>
              <td><?= htmlspecialchars($row['breed']) ?></td>
              <td><?= htmlspecialchars($row['username']) ?></td>
              <td><?= number_format((float)$row['price'], 2) ?></td>
              <td><?= htmlspecialchars($row['created_at']) ?></td>
              <td>
                <div class="btn-group btn-group-sm">
                  <a class="btn btn-outline-secondary" href="/catshop/public/pet-details.php?id=<?= (int)$row['id'] ?>">View</a>
                  <a class="btn btn-outline-primary disabled" href="#" tabindex="-1" aria-disabled="true">Edit</a>
                  <a class="btn btn-outline-danger" href="/catshop/admin/delete-pet.php?id=<?= (int)$row['id'] ?>" 
                    onclick="return confirm('Are you sure you want to delete this pet?');">Delete</a>
                </div>
              </td>

            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center text-muted py-4">No pets found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    <a class="btn btn-link" href="/catshop/admin/index.php">← Back to Admin Dashboard</a>
  </div>
</div>

<?php
$adminHeader ? null : include __DIR__ . '/../includes/footer.php';
?>
