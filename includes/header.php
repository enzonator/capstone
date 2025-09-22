<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF']); // detect current page
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CatShop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .navbar {
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .navbar-brand img {
      height: 40px;
      margin-right: 10px;
    }
    .nav-link.active {
      font-weight: bold;
      color: #0d6efd !important;
    }
    .nav-link:hover {
      color: #0a58ca !important;
    }
    .dropdown-menu {
      border-radius: 8px;
    }
    .search-box {
      max-width: 350px;
      flex-grow: 1;
    }
  </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="../assets/images/logo.png" alt="CatShop Logo"> 
      <span class="fw-bold">CatShop</span>
    </a>

    <!-- Mobile toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar content -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- Left side links -->
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link <?= $currentPage == 'index.php' ? 'active' : '' ?>" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage == 'products.php' ? 'active' : '' ?>" href="products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage == 'about-us.php' ? 'active' : '' ?>" href="about-us.php">About Us</a></li>
      </ul>

      <!-- Search bar -->
      <form class="d-flex search-box me-3" action="search.php" method="GET">
        <input class="form-control me-2" type="search" name="q" placeholder="Search products..." aria-label="Search" required>
        <button class="btn btn-outline-primary" type="submit">Search</button>
      </form>

      <!-- Right side links -->
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link <?= $currentPage == 'wishlisted-pets.php' ? 'active' : '' ?>" href="wishlisted-pets.php">My Wishlist ❤️</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
          <!-- Dropdown for logged-in user -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              👤 <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="profile.php">Profile</a></li>
              <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link <?= $currentPage == 'login.php' ? 'active' : '' ?>" href="login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link <?= $currentPage == 'register.php' ? 'active' : '' ?>" href="register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
