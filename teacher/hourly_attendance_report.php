<?php
include('session.php');
include('../includes/db_connect.php');
require_once '../vendor/autoload.php';

$classes = mysqli_query($conn, "SELECT * FROM classes");

$filter_class = $_GET['class_id'] ?? '';
$filter_hour = $_GET['hour'] ?? '';
$filter_date = $_GET['date'] ?? date('Y-m-d');

$data = [];

if ($filter_class && $filter_hour && $filter_date) {
    $query = "
        SELECT s.roll_no, s.name, a.status
        FROM students s
        LEFT JOIN attendance a 
            ON s.id = a.student_id 
            AND a.date = '$filter_date' 
            AND a.hour = $filter_hour
        WHERE s.class_id = $filter_class
        ORDER BY s.roll_no ASC
    ";
    $data = mysqli_query($conn, $query);
}

// PDF Export
if (isset($_GET['export']) && $_GET['export'] === 'pdf' && $data) {
    $mpdf = new \Mpdf\Mpdf();
    $html = "<h4>Hour-wise Attendance Report - $filter_date (Hour $filter_hour)</h4>";
    $html .= '<table border="1" cellpadding="5"><tr><th>Roll No</th><th>Name</th><th>Status</th></tr>';
    mysqli_data_seek($data, 0);
    while ($row = mysqli_fetch_assoc($data)) {
        $status = $row['status'] ?? 'N/A';
        $html .= "<tr>
            <td>{$row['roll_no']}</td>
            <td>{$row['name']}</td>
            <td>$status</td>
        </tr>";
    }
    $html .= '</table>';
    $mpdf->WriteHTML($html);
    $mpdf->Output('Hourly_Attendance_Report.pdf', 'D');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hour-wise Attendance Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include('../includes/header.php'); include('../includes/sidebar.php'); ?>

<div class="container mt-4">
    <h4>🕒 Hour-wise Attendance Report</h4>

    <form method="get" class="row mb-4">
        <div class="col-md-3">
            <label>Class</label>
            <select name="class_id" class="form-select" required>
                <option value="">Select Class</option>
                <?php mysqli_data_seek($classes, 0);
                while ($c = mysqli_fetch_assoc($classes)) {
                    $sel = ($c['id'] == $filter_class) ? 'selected' : '';
                    echo "<option value='{$c['id']}' $sel>{$c['name']}</option>";
                } ?>
            </select>
        </div>
        <div class="col-md-2">
            <label>Date</label>
            <input type="date" name="date" class="form-control" value="<?= $filter_date ?>" required>
        </div>
        <div class="col-md-2">
            <label>Hour</label>
            <select name="hour" class="form-select" required>
                <option value="">Select</option>
                <?php for ($i = 1; $i <= 5; $i++) {
                    $sel = ($i == $filter_hour) ? 'selected' : '';
                    echo "<option value='$i' $sel>Hour $i</option>";
                } ?>
            </select>
        </div>
        <div class="col-md-3 pt-4">
            <button class="btn btn-primary mt-2">View Report</button>
            <?php if ($data): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'pdf'])) ?>" class="btn btn-danger mt-2">Export PDF</a>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($data): ?>
        <table class="table table-bordered bg-white">
            <thead class="table-dark">
                <tr>
                    <th>Roll No</th>
                    <th>Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($data)) {
                $status = $row['status'] ?? 'N/A';
                echo "<tr>
                    <td>{$row['roll_no']}</td>
                    <td>{$row['name']}</td>
                    <td>$status</td>
                </tr>";
            } ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
