<?php
include '../config/db.php';
include_once "../includes/header.php"; 

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $check = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $message = "⚠️ Username or email already taken.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
        if ($stmt->execute()) {
            $message = "✅ Account created successfully! <a href='login.php'>Login here</a>";
        } else {
            $message = "❌ Error: " . $conn->error;
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

  <!-- Bootstrap (for quick styling) -->
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
  </style>
</head>
<body>

<div class="register-container">
  <h2>🐾 Create Account</h2>

  <?php if ($message): ?>
    <div class="alert alert-info"><?= $message; ?></div>
  <?php endif; ?>

  <form method="POST" onsubmit="return validateForm()">
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input type="text" name="username" id="username" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" id="email" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" id="password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-custom text-white">Register</button>
  </form>

  <p class="form-text">Already have an account? <a href="login.php">Login</a></p>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Basic JS validation -->
<script>
  function validateForm() {
    let username = document.getElementById("username").value.trim();
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();

    if (username.length < 3) {
      alert("Username must be at least 3 characters long");
      return false;
    }
    if (password.length < 6) {
      alert("Password must be at least 6 characters long");
      return false;
    }
    return true;
  }
</script>

</body>
</html>
