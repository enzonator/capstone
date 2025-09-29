<?php
include '../config/db.php';
include_once "../includes/header.php"; 

$message = '';
$first_name = $last_name = $username = $email = $password = $confirm_password = ''; // default values

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $username   = trim($_POST['username']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Confirm password check
    if ($password !== $confirm_password) {
        $message = "password_mismatch"; // flag for UI
    } else {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Check if username or email already exists
        $check = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $message = "⚠️ Username or email already taken.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $first_name, $last_name, $username, $email, $hashedPassword);

            if ($stmt->execute()) {
                $message = "✅ Account created successfully! <a href='login.php'>Login here</a>";
                // Clear values after success
                $first_name = $last_name = $username = $email = $password = $confirm_password = '';
            } else {
                $message = "❌ Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Account - CatShop</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background: #f8f9fa;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }
    .register-container {
      max-width: 450px;
      margin: 50px auto;
      background: #fff;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .register-container h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    .btn-custom {
      width: 100%;
      background-color: #ff7f50;
      border: none;
    }
    .btn-custom:hover {
      background-color: #ff5722;
    }
    .form-text {
      text-align: center;
      margin-top: 15px;
    }
    .error-text {
      color: red;
      font-size: 0.875rem;
    }
  </style>
</head>
<body>

<div class="register-container">
  <h2>🐾 Create Account</h2>

  <?php if ($message && $message !== "password_mismatch"): ?>
    <div class="alert alert-info"><?= $message; ?></div>
  <?php endif; ?>

  <form method="POST">

    <div class="mb-3">
      <label class="form-label">First Name</label>
      <input type="text" name="first_name" id="first_name" 
             value="<?= htmlspecialchars($first_name) ?>" 
             class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Last Name</label>
      <input type="text" name="last_name" id="last_name" 
             value="<?= htmlspecialchars($last_name) ?>" 
             class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Username</label>
      <input type="text" name="username" id="username" 
             value="<?= htmlspecialchars($username) ?>" 
             class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" id="email" 
             value="<?= htmlspecialchars($email) ?>" 
             class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" id="password" 
             value="<?= htmlspecialchars($password) ?>" 
             class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Confirm Password</label>
      <input 
        type="password" 
        name="confirm_password" 
        id="confirm_password" 
        value="<?= htmlspecialchars($confirm_password) ?>"
        class="form-control <?php if($message === "password_mismatch") echo 'is-invalid'; ?>" 
        required
      >
      <?php if($message === "password_mismatch"): ?>
        <div class="error-text">⚠️ Passwords do not match.</div>
      <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-custom text-white">Register</button>
  </form>

  <p class="form-text">Already have an account? <a href="login.php">Login</a></p>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
