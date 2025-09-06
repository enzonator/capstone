<?php
require_once "../config/db.php";
include_once "../includes/header.php";

// Get pet_id from URL
if (!isset($_GET['id'])) {
    die("Pet not found.");
}

$pet_id = intval($_GET['id']);

// Fetch pet info
$sql = "SELECT p.*, u.username 
        FROM pets p
        JOIN users u ON p.user_id = u.id
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pet_id);
$stmt->execute();
$result = $stmt->get_result();
$pet = $result->fetch_assoc();

if (!$pet) {
    die("Pet not found.");
}

// Fetch images
$imgSql = "SELECT filename FROM pet_images WHERE pet_id = ?";
$imgStmt = $conn->prepare($imgSql);
$imgStmt->bind_param("i", $pet_id);
$imgStmt->execute();
$images = $imgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<style>
.details-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin: 30px auto;
    max-width: 1000px;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
}

/* Gallery */
.gallery-container {
    display: flex;
    gap: 15px;
}

.thumbnails {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 500px;
    overflow-y: auto;
}
.thumbnails img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 6px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: border 0.2s ease;
}
.thumbnails img.active {
    border: 2px solid #007bff;
}

.main-image {
    flex: 1;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}
.main-image img {
    max-width: 100%;
    max-height: 500px;
    border-radius: 8px;
    object-fit: contain;
    box-shadow: 0px 4px 8px rgba(0,0,0,0.2);
}

.nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.5);
    color: white;
    border: none;
    font-size: 24px;
    cursor: pointer;
    padding: 10px 14px;
    border-radius: 50%;
    transition: background 0.3s;
}
.nav-btn:hover {
    background: rgba(0,0,0,0.7);
}
.nav-btn.prev { left: 10px; }
.nav-btn.next { right: 10px; }

/* Pet Info */
.pet-info h2 {
    margin-bottom: 15px;
}
.pet-info p {
    margin: 6px 0;
    font-size: 16px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .details-container {
        grid-template-columns: 1fr;
    }

    .gallery-container {
        flex-direction: column;
        align-items: center;
    }

    .thumbnails {
        flex-direction: row;
        gap: 8px;
        max-width: 100%;
        max-height: none;
        overflow-x: auto;
        overflow-y: hidden;
    }

    .thumbnails img {
        width: 70px;
        height: 70px;
    }

    .main-image img {
        max-height: 350px;
    }
}
</style>


<div class="details-container">
    <!-- Left: Gallery -->
    <div class="gallery-container">
        <div class="thumbnails">
            <?php if (!empty($images)): ?>
                <?php foreach ($images as $index => $img): ?>
                    <img src="../uploads/<?= htmlspecialchars($img['filename']); ?>" 
                         alt="Thumbnail"
                         class="<?= $index === 0 ? 'active' : '' ?>"
                         onclick="changeImage(this, <?= $index ?>)">
                <?php endforeach; ?>
            <?php else: ?>
                <p>No images available.</p>
            <?php endif; ?>
        </div>

        <div class="main-image">
            <?php if (!empty($images)): ?>
                <img id="currentImage" src="../uploads/<?= htmlspecialchars($images[0]['filename']); ?>" alt="Main Image">
                <button class="nav-btn prev" onclick="prevImage()">&#10094;</button>
                <button class="nav-btn next" onclick="nextImage()">&#10095;</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right: Pet Information -->
    <div class="pet-info">
        <h2><?= htmlspecialchars($pet['name']); ?></h2>
        <p><strong>Type:</strong> <?= htmlspecialchars($pet['type']); ?></p>
        <p><strong>Breed:</strong> <?= htmlspecialchars($pet['breed']); ?></p>
        <p><strong>Age:</strong> <?= htmlspecialchars($pet['age']); ?></p>
        <p><strong>Price:</strong> ₱<?= number_format($pet['price'], 2); ?></p>
        <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($pet['description'])); ?></p>
        <p><strong>Listed by:</strong> <?= htmlspecialchars($pet['username']); ?></p>
    </div>
</div>

<script>
let images = <?php echo json_encode(array_column($images, 'filename')); ?>;
let currentIndex = 0;

function changeImage(el, index) {
    document.getElementById("currentImage").src = el.src;
    currentIndex = index;

    document.querySelectorAll(".thumbnails img").forEach(img => img.classList.remove("active"));
    el.classList.add("active");
}

function prevImage() {
    if (images.length === 0) return;
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    updateImage();
}

function nextImage() {
    if (images.length === 0) return;
    currentIndex = (currentIndex + 1) % images.length;
    updateImage();
}

function updateImage() {
    const mainImage = document.getElementById("currentImage");
    mainImage.src = "../uploads/" + images[currentIndex];

    let thumbs = document.querySelectorAll(".thumbnails img");
    thumbs.forEach(img => img.classList.remove("active"));
    thumbs[currentIndex].classList.add("active");
}
</script>

<?php include_once "../includes/footer.php"; ?>
