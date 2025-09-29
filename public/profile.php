<?php
session_start();
require_once "../config/db.php";
include_once "../includes/header.php";

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$sql = "SELECT username, email, first_name, last_name FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL error: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name  = $_POST['last_name'] ?? '';

    $updateSql = "UPDATE users SET email = ?, first_name = ?, last_name = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    if (!$updateStmt) {
        die("SQL error: " . $conn->error);
    }
    $updateStmt->bind_param("sssi", $email, $first_name, $last_name, $user_id);

    if ($updateStmt->execute()) {
        $success = "Profile updated successfully!";
        $user['email'] = $email;
        $user['first_name'] = $first_name;
        $user['last_name'] = $last_name;
    } else {
        $error = "Failed to update profile.";
    }
}
?>

<style>
.dashboard {
    display: flex;
    min-height: 100vh;
    background: #f9f9f9;
}
.profile-container {
    flex-grow: 1;
    padding: 40px;
}
.profile-box {
    max-width: 500px;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
}
.profile-box h2 {
    margin-bottom: 20px;
}
.profile-box label {
    display: block;
    margin: 10px 0 5px;
    font-weight: bold;
}
.profile-box input {
    width: 100%;
    padding: 8px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
}
.profile-box button {
    padding: 10px 15px;
    background: #007bff;
    border: none;
    color: #fff;
    border-radius: 6px;
    cursor: pointer;
}
.profile-box button:hover {
    background: #0056b3;
}
.success {
    color: green;
    margin-bottom: 10px;
}
.error {
    color: red;
    margin-bottom: 10px;
}
</style>

<div class="dashboard">
    <!-- Sidebar -->
    <?php include_once "../includes/sidebar.php"; ?>

    <!-- Profile Section -->
    <div class="profile-container">
        <div class="profile-box">
            <h2>My Profile</h2>

            <?php if (!empty($success)): ?>
                <p class="success"><?= htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <p class="error"><?= htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST">
                <label>Username</label>
                <input type="text" value="<?= htmlspecialchars($user['username']); ?>" disabled>

                <label>First Name</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']); ?>">

                <label>Last Name</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']); ?>">

                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>">

                <button type="submit">Update Profile</button>
            </form>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>
