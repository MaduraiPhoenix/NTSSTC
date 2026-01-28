<?php
include('../includes/session.php');
include('../includes/db_connect.php');

// Handle Add or Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $department = $_POST['department'];

    if (isset($_POST['edit_id'])) {
        $edit_id = $_POST['edit_id'];
        $query = "UPDATE subjects SET name='$name', department='$department' WHERE id=$edit_id";
    } else {
        $query = "INSERT INTO subjects (name, department) VALUES ('$name', '$department')";
    }

    mysqli_query($conn, $query);
    header("Location: manage_subjects.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM subjects WHERE id=$id");
    header("Location: manage_subjects.php");
    exit;
}

// Handle Edit Load
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM subjects WHERE id=$edit_id");
    $edit_data = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Subjects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
</head>
<body>
<?php include('../includes/header.php'); ?>
<?php include('../includes/sidebar.php'); ?>

<div class="container mt-4">
    <h4><?= $edit_mode ? '✏️ Edit Subject' : '➕ Add New Subject' ?></h4>
    <form method="POST" class="row g-3">
        <div class="col-md-5">
            <label>Subject Name</label>
            <input type="text" name="name" class="form-control" required value="<?= $edit_mode ? $edit_data['name'] : '' ?>">
        </div>
        <div class="col-md-5">
            <label>Department</label>
            <input type="text" name="department" class="form-control" required value="<?= $edit_mode ? $edit_data['department'] : '' ?>">
        </div>
        <?php if ($edit_mode): ?>
            <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?>">
        <?php endif; ?>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-success w-100"><?= $edit_mode ? 'Update' : 'Add Subject' ?></button>
        </div>
    </form>

    <hr>
    <h5 class="mt-4">📚 All Subjects</h5>
    <div class="table-responsive">
        <table id="subjectTable" class="table table-bordered bg-white">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Subject Name</th>
                    <th>Department</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $subjects = mysqli_query($conn, "SELECT * FROM subjects ORDER BY id DESC");
                while ($row = mysqli_fetch_assoc($subjects)):
                ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['department'] ?></td>
                        <td>
                            <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this subject?')">Delete</a>
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
    $('#subjectTable').DataTable();
});
</script>

</body>
</html>
