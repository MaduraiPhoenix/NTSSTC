<?php
session_start();
include('../includes/db_connect.php');

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Simple static login (replace with DB if needed)
    $query = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
    $fnlrecs = mysqli_fetch_assoc($query);
if (mysqli_num_rows($query) > 0) {
    $_SESSION['admin'] = $username;
    header("Location: index.php");
    $_SESSION['admin_id'] = $fnlrecs["id"];
    exit;
} else {
    $error = "Invalid login details";
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-lg" style="width: 380px;">
        <div class="card-header text-white bg-primary text-center">
            <h4 class="mb-0">Admin Login</h4>
        </div>
        <div class="card-body">
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button name="login" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
