<?php
include('../includes/session.php');
checkAdminSession();
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

$class_id = '';
$academic_year = '';
$start_date = '';
$end_date = '';
$edit_id = 0;
$success = '';

// Add / Update
if (isset($_POST['save'])) {
    $class_id = (int)$_POST['class_id'];
    $academic_year = $_POST['academic_year'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if (!empty($_POST['edit_id'])) {
        $eid = (int)$_POST['edit_id'];
        mysqli_query(
            $conn,
            "UPDATE academic_years 
             SET class_id='$class_id', academic_year='$academic_year',
                 start_date='$start_date', end_date='$end_date'
             WHERE id=$eid"
        );
        $success = "Academic year updated.";
    } else {
        mysqli_query(
            $conn,
            "INSERT INTO academic_years (class_id, academic_year, start_date, end_date)
             VALUES ('$class_id', '$academic_year', '$start_date', '$end_date')"
        );
        $success = "Academic year added.";
    }
}

// Delete
if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM academic_years WHERE id=$did");
    $success = "Academic year deleted.";
}

// Edit
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM academic_years WHERE id=$eid");
    $row = mysqli_fetch_assoc($res);

    $class_id = $row['class_id'];
    $academic_year = $row['academic_year'];
    $start_date = $row['start_date'];
    $end_date = $row['end_date'];
    $edit_id = $row['id'];
}
?>

<div class="container-fluid">
    <h4 class="mb-3">Academic Year Management</h4>

    <?php if (!empty($success)) { ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php } ?>

    <form method="post" class="border rounded p-3 mb-4 bg-light">
        <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
        <div class="row">
            <div class="col-md-3">
                <label>Class</label>
                <select name="class_id" class="form-control" required>
                    <option value="">Select</option>
                    <?php
                    $cls = mysqli_query($conn, "SELECT id, name FROM classes ORDER BY name");
                    while ($c = mysqli_fetch_assoc($cls)) {
                        $sel = ($c['id'] == $class_id) ? 'selected' : '';
                        echo "<option value='{$c['id']}' $sel>{$c['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3">
                <label>Academic Year</label>
                <input type="text" name="academic_year" value="<?= $academic_year ?>" class="form-control" placeholder="2025-2028" required>
            </div>

            <div class="col-md-3">
                <label>Course Start Date</label>
                <input type="date" name="start_date" value="<?= $start_date ?>" class="form-control" required>
            </div>

            <div class="col-md-3">
                <label>Course End Date</label>
                <input type="date" name="end_date" value="<?= $end_date ?>" class="form-control" required>
            </div>
        </div>

        <div class="mt-3">
            <button name="save" class="btn btn-success">
                <?= $edit_id ? 'Update' : 'Add' ?>
            </button>
            <?php if ($edit_id) { ?>
                <a href="academic_years.php" class="btn btn-secondary">Cancel</a>
            <?php } ?>
        </div>
    </form>

    <table id="yearTable" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Class</th>
                <th>Academic Year</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q = mysqli_query(
                $conn,
                "SELECT ay.*, c.name AS class_name 
                 FROM academic_years ay
                 JOIN classes c ON c.id = ay.class_id
                 ORDER BY ay.id DESC"
            );
            while ($r = mysqli_fetch_assoc($q)) {
                echo "<tr>
                    <td>{$r['id']}</td>
                    <td>{$r['class_name']}</td>
                    <td>{$r['academic_year']}</td>
                    <td>{$r['start_date']}</td>
                    <td>{$r['end_date']}</td>
                    <td>{$r['status']}</td>
                    <td>
                        <a href='academic_years.php?edit={$r['id']}' class='btn btn-sm btn-warning'>Edit</a>
                        <a href='academic_years.php?delete={$r['id']}' onclick=\"return confirm('Delete this academic year?')\" class='btn btn-sm btn-danger'>Delete</a>
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
    $('#yearTable').DataTable();
});
</script>
