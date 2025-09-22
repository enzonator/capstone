<?php
session_start();
require_once "../config/db.php";
include_once "../includes/header.php";

// Logged-in user
$user_id = $_SESSION['user_id'] ?? null;

// Get filter/sort parameters
$breed = $_GET['breed'] ?? '';
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';
$sort = $_GET['sort'] ?? 'recent';

// Fetch distinct breeds for sidebar
$breedQuery = $conn->query("SELECT DISTINCT breed FROM pets");
$breeds = $breedQuery->fetch_all(MYSQLI_ASSOC);

// Build query
$sql = "SELECT p.*,
        (SELECT filename FROM pet_images WHERE pet_id = p.id LIMIT 1) as image1,
        (SELECT filename FROM pet_images WHERE pet_id = p.id LIMIT 1,1) as image2
        FROM pets p 
        WHERE 1=1";

$params = [];
$types = "";

// Apply filters
if (!empty($breed)) {
    $sql .= " AND p.breed = ?";
    $params[] = $breed;
    $types .= "s";
}
if (!empty($price_min)) {
    $sql .= " AND p.price >= ?";
    $params[] = $price_min;
    $types .= "d";
}
if (!empty($price_max)) {
    $sql .= " AND p.price <= ?";
    $params[] = $price_max;
    $types .= "d";
}

// Apply sorting
switch ($sort) {
    case "price_high":
        $sql .= " ORDER BY p.price DESC";
        break;
    case "price_low":
        $sql .= " ORDER BY p.price ASC";
        break;
    default: // most recent
        $sql .= " ORDER BY p.created_at DESC";
        break;
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$pets = $result->fetch_all(MYSQLI_ASSOC);

// Fetch wishlist items for current user
$wishlist = [];
if ($user_id) {
    $wishQuery = $conn->prepare("SELECT pet_id FROM wishlist WHERE user_id = ?");
    $wishQuery->bind_param("i", $user_id);
    $wishQuery->execute();
    $wishResult = $wishQuery->get_result();
    while ($row = $wishResult->fetch_assoc()) {
        $wishlist[] = $row['pet_id'];
    }
}
?>

<style>
.products-container {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 20px;
    margin: 20px;
}

/* Sidebar */
.sidebar {
    background: #fff;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0px 3px 6px rgba(0,0,0,0.1);
    height: fit-content;
}

.sidebar h3 {
    margin-bottom: 10px;
    font-size: 18px;
}

.sidebar label {
    display: block;
    margin: 8px 0 4px;
    font-weight: bold;
}

.sidebar input, .sidebar select, .sidebar button {
    width: 100%;
    padding: 8px;
    margin-bottom: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
}

/* Main Content */
.products-main {
    background: #fff;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0px 3px 6px rgba(0,0,0,0.1);
}

.products-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.products-header select {
    padding: 6px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 15px;
}

.product-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    background: #fafafa;
    transition: 0.2s;
    position: relative;
}

.product-card:hover {
    transform: scale(1.03);
    box-shadow: 0px 4px 10px rgba(0,0,0,0.15);
}

.product-card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    display: block;
    transition: opacity 0.3s ease;
}

.product-card .image-wrapper {
    position: relative;
    width: 100%;
    height: 180px;
}

.product-card .image-wrapper img.second {
    position: absolute;
    top: 0;
    left: 0;
    opacity: 0;
}

.product-card .image-wrapper:hover img.first {
    opacity: 0;
}

.product-card .image-wrapper:hover img.second {
    opacity: 1;
}

.product-card .wishlist-form {
    position: absolute;
    top: 10px;
    right: 10px;
}

.product-card .wishlist-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255, 255, 255, 0.9);
    border: none;
    border-radius: 50%;
    padding: 8px;
    cursor: pointer;
    font-size: 18px;
    color: #e74c3c;
    transition: background 0.2s, transform 0.2s;
    z-index: 10; /* make sure it's above images */
}

.product-card .wishlist-btn:hover {
    transform: scale(1.1);
}
.product-card .wishlist-btn.hearted {
    color: red;
}

.product-card .info {
    padding: 10px;
}

.product-card h4 {
    margin: 0 0 5px;
    font-size: 16px;
}

.product-card p {
    margin: 3px 0;
    font-size: 14px;
    color: #555;
}

.product-card a {
    display: inline-block;
    margin-top: 8px;
    padding: 6px 10px;
    background: #007bff;
    color: white;
    border-radius: 5px;
    font-size: 13px;
    text-decoration: none;
}

.product-card a:hover {
    background: #0056b3;
}
</style>

<div class="products-container">
    <!-- Sidebar Filter -->
    <aside class="sidebar">
        <h3>Filter Pets</h3>
        <form method="GET" action="products.php">
            <label for="breed">Breed</label>
            <select name="breed" id="breed">
                <option value="">All</option>
                <?php foreach ($breeds as $b): ?>
                    <option value="<?= htmlspecialchars($b['breed']); ?>" 
                        <?= $breed == $b['breed'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['breed']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="price_min">Price Min</label>
            <input type="number" name="price_min" value="<?= htmlspecialchars($price_min); ?>">

            <label for="price_max">Price Max</label>
            <input type="number" name="price_max" value="<?= htmlspecialchars($price_max); ?>">

            <button type="submit">Apply Filter</button>
        </form>
    </aside>

    <!-- Main Products -->
    <section class="products-main">
        <div class="products-header">
            <h2>Available Pets</h2>
            <form method="GET" action="products.php">
                <input type="hidden" name="breed" value="<?= htmlspecialchars($breed); ?>">
                <input type="hidden" name="price_min" value="<?= htmlspecialchars($price_min); ?>">
                <input type="hidden" name="price_max" value="<?= htmlspecialchars($price_max); ?>">

                <label for="sort">Sort By:</label>
                <select name="sort" onchange="this.form.submit()">
                    <option value="recent" <?= $sort=='recent'?'selected':''; ?>>Most Recent</option>
                    <option value="price_high" <?= $sort=='price_high'?'selected':''; ?>>Price: High → Low</option>
                    <option value="price_low" <?= $sort=='price_low'?'selected':''; ?>>Price: Low → High</option>
                </select>
            </form>
        </div>

        <div class="products-grid">
            <?php if (!empty($pets)): ?>
                <?php foreach ($pets as $pet): ?>
                    <div class="product-card">
                        <div class="image-wrapper">
                            <!-- Wishlist Button (only for logged in users) -->
                            <?php if ($user_id): ?>
                                <form method="POST" action="wishlist.php" class="wishlist-form">
                                    <input type="hidden" name="pet_id" value="<?= $pet['id']; ?>">
                                    <button type="submit" class="wishlist-btn <?= in_array($pet['id'], $wishlist) ? 'hearted' : ''; ?>" title="Add to Wishlist">
                                        <?= in_array($pet['id'], $wishlist) ? "❤️" : "🤍"; ?>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <img src="../uploads/<?= htmlspecialchars($pet['image1'] ?? 'no-image.png'); ?>" 
                                 alt="Pet" class="first">
                            <?php if (!empty($pet['image2'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($pet['image2']); ?>" 
                                     alt="Pet Hover" class="second">
                            <?php endif; ?>
                        </div>
                        <div class="info">
                            <h4><?= htmlspecialchars($pet['name']); ?></h4>
                            <p><strong>Type:</strong> <?= htmlspecialchars($pet['type']); ?></p>
                            <p><strong>Breed:</strong> <?= htmlspecialchars($pet['breed']); ?></p>
                            <p><strong>₱<?= number_format($pet['price'], 2); ?></strong></p>
                            <a href="pet-details.php?id=<?= $pet['id']; ?>">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No pets found.</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include_once "../includes/footer.php"; ?>
