<?php
include('../includes/session.php');
include('../includes/db_connect.php');

$classes = mysqli_query($conn, "SELECT id, name FROM classes");
$students = mysqli_query($conn, "SELECT id, name FROM students");

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$class_id = $_GET['class_id'] ?? '';
$student_id = $_GET['student_id'] ?? '';
$hour = $_GET['hour'] ?? '';
$status = $_GET['status'] ?? '';

$where = "1=1";
if ($from && $to) {
    $where .= " AND a.date BETWEEN '$from' AND '$to'";
}
if ($class_id) {
    $where .= " AND a.class_id = $class_id";
}
if ($student_id) {
    $where .= " AND a.student_id = $student_id";
}
if ($hour) {
    $where .= " AND a.hour = $hour";
}


$records = [];
if ($from && $to) {
    $query = "
        SELECT a.*, s.name AS student_name, s.roll_no, c.name AS class_name
        FROM attendance a
        JOIN students s ON a.student_id = s.id
        JOIN classes c ON a.class_id = c.id
        WHERE $where
        ORDER BY a.date DESC, a.hour
    ";
    $records = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report</title>
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include('../includes/header.php'); ?>
<?php include('../includes/sidebar.php'); ?>

<div class="container mt-4">
    <h4>📊 Attendance Report (Admin)</h4>

    <form method="get" class="row mb-4">
        <div class="col-md-2">
            <label>From Date</label>
            <input type="date" name="from" value="<?= $from ?>" class="form-control">
        </div>
        <div class="col-md-2">
            <label>To Date</label>
            <input type="date" name="to" value="<?= $to ?>" class="form-control">
        </div>
        <div class="col-md-2">
            <label>Class</label>
            <select name="class_id" class="form-select">
                <option value="">All</option>
                <?php while ($c = mysqli_fetch_assoc($classes)) {
                    $sel = $class_id == $c['id'] ? 'selected' : '';
                    echo "<option value='{$c['id']}' $sel>{$c['name']}</option>";
                } ?>
            </select>
        </div>
        <div class="col-md-2">
            <label>Student</label>
            <select name="student_id" class="form-select">
                <option value="">All</option>
                <?php mysqli_data_seek($students, 0);
                while ($s = mysqli_fetch_assoc($students)) {
                    $sel = $student_id == $s['id'] ? 'selected' : '';
                    echo "<option value='{$s['id']}' $sel>{$s['name']}</option>";
                } ?>
            </select>
        </div>
        <div class="col-md-1">
            <label>Hour</label>
            <select name="hour" class="form-select">
                <option value="">All</option>
                <?php for ($i = 1; $i <= 5; $i++) {
                    $sel = $hour == $i ? 'selected' : '';
                    echo "<option value='$i' $sel>$i</option>";
                } ?>
            </select>
        </div>
        
        <div class="col-md-1 pt-4">
            <label></label>
            <button class="btn btn-primary mt-2">Filter</button>
        </div>
    </form>

    <?php if ($from && $to): ?>
        <table id="reportTable" class="display table table-bordered table-striped bg-white">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Hour</th>
                    <th>Class</th>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($records)): ?>
                    <tr>
                        <td><?= $row['date'] ?></td>
                        <td><?= $row['hour'] ?></td>
                        <td><?= $row['class_name'] ?></td>
                        <td><?= $row['roll_no'] ?></td>
                        <td><?= $row['student_name'] ?></td>
                        <td><?= $row['status'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#reportTable').DataTable();
    });
</script>
</body>
</html>
