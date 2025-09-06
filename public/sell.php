<?php
session_start();
require_once "../config/db.php";

// Only logged-in users can sell
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $breed = $_POST['breed'];
    $age = $_POST['age'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];

    // Insert pet first
    $sql = "INSERT INTO pets (user_id, name, type, breed, age, price, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("issssds", $user_id, $name, $type, $breed, $age, $price, $description);

    if ($stmt->execute()) {
        $pet_id = $stmt->insert_id; // get new pet ID

        // Handle multiple image uploads
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if (!empty($tmpName)) {
                $filename = time() . "_" . basename($_FILES["images"]["name"][$key]);
                $targetFilePath = $targetDir . $filename;

                if (move_uploaded_file($tmpName, $targetFilePath)) {
                    // Save image in pet_images table
                    $imgSql = "INSERT INTO pet_images (pet_id, filename) VALUES (?, ?)";
                    $imgStmt = $conn->prepare($imgSql);
                    $imgStmt->bind_param("is", $pet_id, $filename);
                    $imgStmt->execute();
                }
            }
        }

        $message = "Your pet has been listed successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
}
?>

<?php include_once "../includes/header.php"; ?>

<div class="container" style="max-width: 700px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0px 4px 8px rgba(0,0,0,0.1);">
    <h2 style="text-align:center; margin-bottom:20px; color:#333;">Sell Your Pet</h2>

    <?php if (!empty($message)): ?>
        <div style="padding: 10px; margin-bottom: 15px; border-radius: 5px; background: #f0f8ff; color: #333;">
            <?= htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="display: grid; gap: 15px;">
        <input type="text" name="name" placeholder="Pet Name" required class="form-input">
        
        <select name="type" required class="form-input">
            <option value="">Select Type</option>
            <option value="cat">Cat</option>
            <option value="dog">Dog</option>
        </select>
        
        <input type="text" name="breed" placeholder="Breed" required class="form-input">
        <input type="text" name="age" placeholder="Age (e.g. 2 years)" required class="form-input">
        <input type="number" step="0.01" name="price" placeholder="Price (₱)" required class="form-input">
        
        <textarea name="description" placeholder="Description" rows="4" class="form-input"></textarea>
        
        <!-- Multiple image input -->
        <input type="file" name="images[]" accept="image/*" multiple required class="form-input">

        <button type="submit" style="padding: 10px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
            List Pet for Sale
        </button>
    </form>
</div>

<style>
    .form-input {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 15px;
        width: 100%;
    }
    .form-input:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0,123,255,0.3);
    }
</style>

<?php include_once "../includes/footer.php"; ?>
