<?php
session_start();
include '../config/db.php';

include_once "../includes/header.php"; // ✅ Use your normal header
?>

<!-- Hero Section -->
<section class="hero" style="padding:60px 20px; text-align:center; background:#fffaf2;">
    <h1 style="font-size:32px; margin-bottom:10px;">Welcome to CatShop 🐾</h1>
    <p style="font-size:18px; margin-bottom:25px; color:#555;">
        Find your perfect furry friend or sell your pet safely.
    </p>
    <a href="sell.php" class="sell-btn"
       style="background:#e67e22; color:#fff; padding:12px 22px; border-radius:6px; text-decoration:none; font-weight:bold;">
       + Sell Your Pet
    </a>
</section>

<!-- Featured Pets -->
<section class="featured-pets" style="padding:40px 20px; background:#fafafa; text-align:center;">
    <div class="container">
        <h2 style="font-size:26px; margin-bottom:5px;">🐾 Featured Pets</h2>
        <p style="color:#666; margin-bottom:30px;">Hand-picked furry friends looking for a loving home.</p>

        <div class="pet-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px,1fr)); gap:20px;">
            <?php
            // ✅ Fetch pets with seller info + first image from pet_images or fallback to pets.image
            $result = $conn->query("
                SELECT p.id, p.name, p.breed, p.age, p.price, u.username,
                       COALESCE(
                           (
                               SELECT pi.filename 
                               FROM pet_images pi 
                               WHERE pi.pet_id = p.id 
                               ORDER BY pi.id ASC 
                               LIMIT 1
                           ),
                           p.image
                       ) AS image
                FROM pets p
                LEFT JOIN users u ON p.user_id = u.id
                ORDER BY p.id DESC
                LIMIT 8
            ");

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()):
                    $image = $row['image'] ?: 'no-image.png'; // ✅ fallback
            ?>
            <div class="pet-card" style="background:#fff; border:1px solid #eee; border-radius:10px; padding:15px; text-align:center;">
                <img src="../uploads/<?php echo htmlspecialchars($image); ?>" 
                     alt="<?php echo htmlspecialchars($row['name']); ?>"
                     style="width:100%; height:200px; object-fit:cover; border-radius:8px;">
                <h3 style="font-size:18px; margin:10px 0 5px;"><?php echo htmlspecialchars($row['name']); ?></h3>
                <p style="font-size:14px; color:#555;">Seller: <?php echo htmlspecialchars($row['username'] ?? 'Unknown'); ?></p>
                <p style="font-size:16px; font-weight:bold; color:#e67e22; margin-top:5px;">
                    ₱<?php echo number_format($row['price'], 2); ?>
                </p>
                <a href="pet-details.php?id=<?php echo $row['id']; ?>" 
                   style="display:inline-block; margin-top:10px; padding:8px 14px; background:#3498db; color:#fff; border-radius:6px; text-decoration:none; font-size:14px; font-weight:bold;">
                   View Details
                </a>
            </div>
            <?php
                endwhile;
            } else {
                echo "<p>No pets available right now. Be the first to <a href='sell.php'>sell your pet</a>!</p>";
            }
            ?>
        </div>
    </div>
</section>

<?php include_once "../includes/footer.php"; ?>
