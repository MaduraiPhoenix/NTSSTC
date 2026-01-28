<?php
include('../includes/session.php');
include('../includes/db_connect.php');

// ADD student
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $roll = $_POST['roll_no'];
    $dept = $_POST['department'];
    $class_id = $_POST['class_id'];
    mysqli_query($conn, "INSERT INTO students (name, roll_no, department, class_id) 
        VALUES ('$name', '$roll', '$dept', $class_id)");
}

// DELETE student
if (isset($_GET['del'])) {
    $id = $_GET['del'];
    mysqli_query($conn, "DELETE FROM students WHERE id = $id");
}

// EDIT student
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $roll = $_POST['roll_no'];
    $dept = $_POST['department'];
    $class_id = $_POST['class_id'];
    mysqli_query($conn, "UPDATE students SET name='$name', roll_no='$roll', department='$dept', class_id=$class_id 
        WHERE id=$id");
}

// GET class list
$classes_q = mysqli_query($conn, "SELECT * FROM classes");

// GET students list
$students_q = mysqli_query($conn, "SELECT s.*, c.name AS class_name FROM students s 
    JOIN classes c ON s.class_id = c.id ORDER BY s.id DESC");

// For editing
$edit = false;
if (isset($_GET['edit'])) {
    $edit = true;
    $id = $_GET['edit'];
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE id = $id"));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include('../includes/header.php'); include('../includes/sidebar.php'); ?>

<div class="container mt-4">
    <h4>👨‍🎓 Manage Students</h4>

    <form method="post" class="row bg-white shadow-sm p-3 mb-4 rounded">
        <input type="hidden" name="id" value="<?= $row['id'] ?? '' ?>">
        <div class="col-md-3">
            <label>Name</label>
            <input type="text" name="name" value="<?= $row['name'] ?? '' ?>" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label>Roll No</label>
            <input type="text" name="roll_no" value="<?= $row['roll_no'] ?? '' ?>" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label>Department</label>
            <input type="text" name="department" value="<?= $row['department'] ?? '' ?>" class="form-control">
        </div>
        <div class="col-md-2">
            <label>Class</label>
            <select name="class_id" class="form-select" required>
                <option value="">Select</option>
                <?php while ($c = mysqli_fetch_assoc($classes_q)) {
                    $sel = ($row['class_id'] ?? '') == $c['id'] ? 'selected' : '';
                    echo "<option value='{$c['id']}' $sel>{$c['name']}</option>";
                } ?>
            </select>
        </div>
        <div class="col-md-2 mt-4 pt-2">
            <button name="<?= $edit ? 'update' : 'add' ?>" class="btn btn-<?= $edit ? 'warning' : 'primary' ?>">
                <?= $edit ? 'Update' : 'Add' ?>
            </button>
        </div>
    </form>

    <table class="table table-bordered bg-white shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Roll No</th>
                <th>Department</th>
                <th>Class</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($s = mysqli_fetch_assoc($students_q)) { ?>
            <tr>
                <td><?= $s['id'] ?></td>
                <td><?= $s['name'] ?></td>
                <td><?= $s['roll_no'] ?></td>
                <td><?= $s['department'] ?></td>
                <td><?= $s['class_name'] ?></td>
                <td>
                    <a href="?edit=<?= $s['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="?del=<?= $s['id'] ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this student?')">Delete</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
