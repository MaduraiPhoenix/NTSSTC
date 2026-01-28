<?php
include('session.php');
include('../includes/db_connect.php');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $teacher_id = $_SESSION['teacher_id'];
    $check = mysqli_query($conn, "SELECT * FROM teachers WHERE id = $teacher_id AND password = '$current'");

    if (mysqli_num_rows($check) == 1) {
        if ($new === $confirm) {
            mysqli_query($conn, "UPDATE teachers SET password = '$new' WHERE id = $teacher_id");
            $msg = "<div class='alert alert-success'>Password changed successfully!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>New passwords do not match!</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>Current password is incorrect!</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password - Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include('header.php'); ?>

<div class="container mt-4">
    <h4>🔐 Change Password</h4>
    <?= $msg ?>
    <form method="POST" class="col-md-6">
        <div class="mb-3">
            <label>Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>New Password</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Update Password</button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
</body>
</html>
