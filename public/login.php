<?php
session_start();
include '../config/db.php';
include_once "../includes/header.php";

$message = '';



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Save session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // assuming you have a `role` column in users table

            // Redirect based on role
            if ($_SESSION['role'] === 'admin') {
               header("Location: /catshop/admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $message = "❌ Invalid password.";
        }
    } else {
        $message = "⚠️ User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - CatShop</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background: #f8f9fa;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }
    .login-container {
      max-width: 420px;
      margin: 60px auto;
      background: #fff;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .login-container h2 {
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

<div class="login-container">
  <h2>🐾 Login</h2>

  <?php if ($message): ?>
    <div class="alert alert-info"><?= $message; ?></div>
  <?php endif; ?>

  <form method="POST" onsubmit="return validateLogin()">
    <div class="mb-3">
      <label class="form-label">Username or Email</label>
      <input type="text" name="username" id="username" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" id="password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-custom text-white">Login</button>
  </form>

  <p class="form-text">Don’t have an account? <a href="register.php">Register</a></p>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Basic JS validation -->
<script>
  function validateLogin() {
    let username = document.getElementById("username").value.trim();
    let password = document.getElementById("password").value.trim();

    if (username.length < 3) {
      alert("Please enter a valid username/email.");
      return false;
    }
    if (password.length < 6) {
      alert("Password must be at least 6 characters long.");
      return false;
    }
    return true;
  }
</script>

</body>
</html>


