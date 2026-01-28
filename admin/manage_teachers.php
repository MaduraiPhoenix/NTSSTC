<?php
include('../includes/session.php');
include('../includes/db_connect.php');

// Handle Add or Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $dept = $_POST['department'];
    $password = $_POST['password'];

    if (isset($_POST['edit_id'])) {
        // Edit existing
        $edit_id = $_POST['edit_id'];
        $query = "UPDATE teachers SET name='$name', email='$email', department='$dept', password='$password' WHERE id=$edit_id";
    } else {
        // Add new
        $query = "INSERT INTO teachers (name, email, department, password) VALUES ('$name', '$email', '$dept', '$password')";
    }

    mysqli_query($conn, $query);
    header("Location: manage_teachers.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM teachers WHERE id=$id");
    header("Location: manage_teachers.php");
    exit;
}

// Handle Edit Load
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM teachers WHERE id=$edit_id");
    $edit_data = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Teachers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
</head>
<body>
<?php include('../includes/header.php'); ?>
<?php include('../includes/sidebar.php'); ?>

<div class="container mt-4">
    <h4><?= $edit_mode ? '✏️ Edit Teacher' : '➕ Add New Teacher' ?></h4>
    <form method="POST" class="row g-3">
        <div class="col-md-4">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required value="<?= $edit_mode ? $edit_data['name'] : '' ?>">
        </div>
        <div class="col-md-4">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required value="<?= $edit_mode ? $edit_data['email'] : '' ?>">
        </div>
        <div class="col-md-4">
            <label>Department</label>
            <input type="text" name="department" class="form-control" required value="<?= $edit_mode ? $edit_data['department'] : '' ?>">
        </div>
        <div class="col-md-4">
            <label>Password</label>
            <input type="text" name="password" class="form-control" required value="<?= $edit_mode ? $edit_data['password'] : '' ?>">
        </div>
        <?php if ($edit_mode): ?>
            <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?>">
        <?php endif; ?>
        <div class="col-md-12">
            <button type="submit" class="btn btn-success"><?= $edit_mode ? 'Update' : 'Add Teacher' ?></button>
            <?php if ($edit_mode): ?>
                <a href="manage_teachers.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>

    <hr>
    <h5 class="mt-4">📋 All Teachers</h5>
    <div class="table-responsive">
        <table id="teacherTable" class="table table-bordered bg-white">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Password</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $teachers = mysqli_query($conn, "SELECT * FROM teachers ORDER BY id DESC");
                while ($row = mysqli_fetch_assoc($teachers)):
                ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['department'] ?></td>
                        <td><?= $row['password'] ?></td>
                        <td>
                            <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this teacher?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('../includes/footer.php'); ?>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function () {
    $('#teacherTable').DataTable();
});
</script>

</body>
</html>
