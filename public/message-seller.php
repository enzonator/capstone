<?php
session_start();
require_once "../config/db.php";
include_once "../includes/header.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$pet_id = intval($_GET['pet_id'] ?? 0);
$seller_id = intval($_GET['seller_id'] ?? 0);

if (!$pet_id || !$seller_id) {
    die("Invalid request.");
}

// Fetch pet info
$petSql = "SELECT name FROM pets WHERE id = ?";
$petStmt = $conn->prepare($petSql);
$petStmt->bind_param("i", $pet_id);
$petStmt->execute();
$pet = $petStmt->get_result()->fetch_assoc();

// Handle message submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['message'])) {
    $message = trim($_POST['message']);
    $sql = "INSERT INTO messages (pet_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $pet_id, $user_id, $seller_id, $message);
    $stmt->execute();
}

// Fetch conversation between current user and seller for this pet
$msgSql = "SELECT m.*, u.username 
           FROM messages m
           JOIN users u ON m.sender_id = u.id
           WHERE m.pet_id = ? AND 
                 ((m.sender_id = ? AND m.receiver_id = ?) OR 
                  (m.sender_id = ? AND m.receiver_id = ?))
           ORDER BY m.created_at ASC";
$msgStmt = $conn->prepare($msgSql);
$msgStmt->bind_param("iiiii", $pet_id, $user_id, $seller_id, $seller_id, $user_id);
$msgStmt->execute();
$messages = $msgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<style>
.chat-container {
    max-width: 800px;
    margin: 30px auto;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
}

.messages {
    max-height: 400px;
    overflow-y: auto;
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.message {
    margin: 8px 0;
    padding: 8px 12px;
    border-radius: 6px;
    max-width: 70%;
}

.message.sent {
    background: #d1ffd6;
    margin-left: auto;
    text-align: right;
}

.message.received {
    background: #f1f1f1;
    margin-right: auto;
    text-align: left;
}

.send-form {
    display: flex;
    gap: 10px;
}

.send-form textarea {
    flex: 1;
    resize: none;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.send-form button {
    background: #007bff;
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
}

.send-form button:hover {
    background: #0056b3;
}
</style>

<div class="chat-container">
    <h2>Inquire about <?= htmlspecialchars($pet['name']); ?></h2>

    <div class="messages">
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="message <?= $msg['sender_id'] == $user_id ? 'sent' : 'received' ?>">
                    <strong><?= htmlspecialchars($msg['username']); ?>:</strong><br>
                    <?= nl2br(htmlspecialchars($msg['message'])); ?><br>
                    <small><em><?= $msg['created_at']; ?></em></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p><em>No messages yet. Start the conversation!</em></p>
        <?php endif; ?>
    </div>

    <form method="POST" class="send-form">
        <textarea name="message" rows="2" placeholder="Type your message..." required></textarea>
        <button type="submit">Send</button>
    </form>
</div>

<?php include_once "../includes/footer.php"; ?>
