<?php
include('session.php');
include('../includes/db_connect.php');
require_once '../vendor/autoload.php';

$teacher_id = $_SESSION['teacher_id'];

// Get classes assigned to this teacher
$class_query = "
    SELECT c.id, c.name 
    FROM classes c 
    JOIN class_teacher_map ct ON c.id = ct.class_id 
    WHERE ct.teacher_id = $teacher_id
";
$class_result = mysqli_query($conn, $class_query);

// Filters
$selected_class = $_GET['class'] ?? '';
$selected_date = $_GET['date'] ?? date('Y-m-d');
$selected_hour = $_GET['hour'] ?? '';

$report_data = [];

if ($selected_class && $selected_hour && $selected_date) {
    $query = "
        SELECT s.roll_no, s.name, a.status
        FROM students s
        LEFT JOIN attendance a 
            ON s.id = a.student_id 
            AND a.class_id = $selected_class 
            AND a.date = '$selected_date' 
            AND a.hour = $selected_hour
        WHERE s.class_id = $selected_class
        ORDER BY s.roll_no
    ";
    $report_data = mysqli_query($conn, $query);
}

// PDF export
if (isset($_GET['export']) && $_GET['export'] === 'pdf' && $report_data) {
    $mpdf = new \Mpdf\Mpdf();
    $html = "<h4>Hour-wise Attendance Report - $selected_date (Hour $selected_hour)</h4>";
    $html .= '<table border="1" cellpadding="5"><tr><th>Roll No</th><th>Name</th><th>Status</th></tr>';
    mysqli_data_seek($report_data, 0);
    while ($row = mysqli_fetch_assoc($report_data)) {
        $status = $row['status'] ?? 'N/A';
        $html .= "<tr><td>{$row['roll_no']}</td><td>{$row['name']}</td><td>$status</td></tr>";
    }
    $html .= '</table>';
    $mpdf->WriteHTML($html);
    $mpdf->Output('hourwise_attendance.pdf', 'D');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hour-wise Attendance Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color:#f2f2f2;">
<?php include('header.php'); ?>

<div class="container mt-4">
    <h4>⏰ Hour-wise Attendance Report</h4>

    <form method="get" class="row mb-4">
        <div class="col-md-3">
            <label>Class</label>
            <select name="class" class="form-select" required>
                <option value="">Select Class</option>
                <?php mysqli_data_seek($class_result, 0);
                while ($class = mysqli_fetch_assoc($class_result)) {
                    $sel = ($class['id'] == $selected_class) ? 'selected' : '';
                    echo "<option value='{$class['id']}' $sel>{$class['name']}</option>";
                } ?>
            </select>
        </div>

        <div class="col-md-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" value="<?= $selected_date ?>" required>
        </div>

        <div class="col-md-2">
            <label>Hour</label>
            <select name="hour" class="form-select" required>
                <option value="">Select</option>
                <?php for ($i = 1; $i <= 5; $i++) {
                    $sel = ($selected_hour == $i) ? 'selected' : '';
                    echo "<option value='$i' $sel>Hour $i</option>";
                } ?>
            </select>
        </div>

        <div class="col-md-4 pt-4">
            <button class="btn btn-primary mt-2">View</button>
            <?php if ($report_data): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'pdf'])) ?>" class="btn btn-danger mt-2">Export PDF</a>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($report_data): ?>
        <table class="table table-bordered bg-white">
            <thead class="table-light">
                <tr><th>Roll No</th><th>Name</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($report_data)): ?>
                <tr>
                    <td><?= $row['roll_no'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['status'] ?? 'N/A' ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php elseif ($_GET): ?>
        <div class="alert alert-warning">No attendance records found for selected filters.</div>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>
</body>
</html>
