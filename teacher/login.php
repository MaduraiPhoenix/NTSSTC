<?php
session_start();
include('../includes/db_connect.php');

$error = '';
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $q = mysqli_query($conn, "SELECT * FROM teachers WHERE email='$email' AND password='$password'");
    if (mysqli_num_rows($q) == 1) {
        $row = mysqli_fetch_assoc($q);
        $_SESSION['teacher_id'] = $row['id'];
        $_SESSION['teacher_name'] = $row['name'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid login credentials.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Login - MDUSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-4 shadow-sm">
                    <h4 class="text-center mb-4">Teacher Login</h4>
                    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button name="login" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
                <p class="text-center mt-3 text-muted">MDUSC Attendance System</p>
            </div>
        </div>
    </div>
</body>
</html>
