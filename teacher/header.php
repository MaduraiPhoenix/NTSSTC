<?php
if (!isset($_SESSION)) session_start();
?>

<nav class="navbar navbar-dark bg-primary px-3 d-flex justify-content-between">
    <div class="d-flex align-items-center">
        <span class="navbar-brand me-4">MDUSC - Teacher Panel</span>
        <a href="index.php" class="btn btn-sm btn-light">🏠 Home</a>
    </div>
    <div class="text-white">
        <?= $_SESSION['teacher_name'] ?? '' ?>
        | <a href="change_password.php" class="text-white text-decoration-underline">Change Password</a>
        | <a href="logout.php" class="text-white text-decoration-underline">Logout</a>
    </div>
</nav>
