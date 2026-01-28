<?php
include('../includes/session.php');
checkAdminSession();
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

$name = '';
$department = '';
$total_semesters = '';
$edit_id = 0;
$success = '';

// Add or Update
if (isset($_POST['save'])) {
    $name = $_POST['name'];
    $department = $_POST['department'];
    $total_semesters = (int)$_POST['total_semesters'];

    if ($total_semesters <= 0) {
        die('Invalid semester count');
    }

    if (!empty($_POST['edit_id'])) {
        $eid = (int)$_POST['edit_id'];
        mysqli_query(
            $conn,
            "UPDATE classes 
             SET name='$name', department='$department', total_semesters='$total_semesters' 
             WHERE id=$eid"
        );
        $success = "Class updated successfully.";
    } else {
        mysqli_query(
            $conn,
            "INSERT INTO classes (name, department, total_semesters) 
             VALUES ('$name', '$department', '$total_semesters')"
        );
        $success = "Class added successfully.";
    }
}

// Delete
if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM classes WHERE id=$did");
    $success = "Class deleted.";
}

// Edit
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM classes WHERE id=$eid");
    $row = mysqli_fetch_assoc($res);
    $name = $row['name'];
    $department = $row['department'];
    $total_semesters = $row['total_semesters'];
    $edit_id = $row['id'];
}
?>

<div class="container-fluid">
    <h4 class="mb-3">Manage Classes</h4>

    <?php if (!empty($success)) { ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php } ?>

    <form method="post" class="border rounded p-3 mb-4 bg-light">
        <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
        <div class="row">
            <div class="col-md-3">
                <label>Class Name</label>
                <input type="text" name="name" value="<?= $name ?>" class="form-control" required>
            </div>

            <div class="col-md-3">
                <label>Department</label>
                <input type="text" name="department" value="<?= $department ?>" class="form-control" required>
            </div>

            <div class="col-md-3">
                <label>Total Semesters</label>
                <input type="number" name="total_semesters" value="<?= $total_semesters ?>" class="form-control" min="1" required>
            </div>

            <div class="col-md-3 mt-4 pt-2">
                <button name="save" class="btn btn-success">
                    <?= $edit_id ? 'Update' : 'Add' ?>
                </button>
                <?php if ($edit_id) { ?>
                    <a href="manage_classes.php" class="btn btn-secondary">Cancel</a>
                <?php } ?>
            </div>
        </div>
    </form>

    <table id="classTable" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Class Name</th>
                <th>Department</th>
                <th>Total Semesters</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q = mysqli_query($conn, "SELECT * FROM classes ORDER BY id DESC");
            while ($r = mysqli_fetch_assoc($q)) {
                echo "<tr>
                    <td>{$r['id']}</td>
                    <td>{$r['name']}</td>
                    <td>{$r['department']}</td>
                    <td>{$r['total_semesters']}</td>
                    <td>
                        <a href='manage_classes.php?edit={$r['id']}' class='btn btn-sm btn-warning'>Edit</a>
                        <a href='manage_classes.php?delete={$r['id']}' onclick=\"return confirm('Delete this class?')\" class='btn btn-sm btn-danger'>Delete</a>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include('../includes/footer.php'); ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        $('#classTable').DataTable();
    });
</script>
