<?php
include('../includes/session.php');
include('../includes/db_connect.php');

// Update attendance status if submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_btn'])) {
    $id = intval($_POST['attendance_id']);
    $status = $_POST['status'];

    $update = mysqli_query($conn, "UPDATE attendance SET status = '$status' WHERE id = $id");

    if ($update) {
        header("Location: edit_attendance.php?success=1&class_id={$_GET['class_id']}&hour={$_GET['hour']}");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Failed to update.</div>";
    }
}
// Fetch classes for filter
$classes = mysqli_query($conn, "SELECT * FROM classes");

// Filter logic
$class_filter = isset($_GET['class_id']) ? $_GET['class_id'] : '';
$hour_filter = isset($_GET['hour']) ? $_GET['hour'] : '';

$where = "1";
if ($class_filter !== '') $where .= " AND a.class_id = $class_filter";
if ($hour_filter !== '') $where .= " AND a.hour = $hour_filter";

// Join attendance, students, classes
$query = "
SELECT a.id AS attendance_id, a.date, a.hour, a.status,
       s.roll_no, s.name AS student_name, c.name AS class_name
FROM attendance a
JOIN students s ON a.student_id = s.id
JOIN classes c ON a.class_id = c.id
WHERE $where
ORDER BY a.date DESC, a.hour ASC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>

<?php include('../includes/header.php'); ?>
<?php include('../includes/sidebar.php'); ?>

<div class="container mt-4">
    <h4 class="mb-3">📝 Edit Attendance Records</h4>

    <!-- Filter -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label>Filter by Class</label>
            <select name="class_id" class="form-select">
                <option value="">All Classes</option>
                <?php while($row = mysqli_fetch_assoc($classes)): ?>
                    <option value="<?= $row['id'] ?>" <?= ($class_filter == $row['id']) ? 'selected' : '' ?>>
                        <?= $row['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label>Filter by Hour</label>
            <select name="hour" class="form-select">
                <option value="">All Hours</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>" <?= ($hour_filter == $i) ? 'selected' : '' ?>>Hour <?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table id="editTable" class="table table-bordered bg-white">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Roll No</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Date</th>
                    <th>Hour</th>
                    <th>Status</th>
                   
                </tr>
            </thead>
           <tbody>
<?php
$sl = 1;
mysqli_data_seek($result, 0); // Reset pointer if needed
while ($row = mysqli_fetch_assoc($result)):
?>
<tr>
    <td><?= $sl++ ?></td>
    <td><?= $row['roll_no'] ?></td>
    <td><?= $row['student_name'] ?></td>
    <td><?= $row['class_name'] ?></td>
    <td><?= $row['date'] ?></td>
    <td>Hour <?= $row['hour'] ?></td>
    <td>
        <form method="POST" class="d-flex">
            <input type="hidden" name="attendance_id" value="<?= $row['attendance_id'] ?>">
            <select name="status" class="form-select form-select-sm me-2">
                <option value="P" <?= ($row['status'] == 'P') ? 'selected' : '' ?>>Present</option>
                <option value="A" <?= ($row['status'] == 'A') ? 'selected' : '' ?>>Absent</option>
            </select>
            <button type="submit" name="update_btn" class="btn btn-sm btn-success">Update</button>
        </form>
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
    $('#editTable').DataTable();
});
</script>

</body>
</html>
