
<?php
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');
require_once '../vendor/autoload.php'; // mpdf
use Mpdf\Mpdf;

$class_id = $_GET['class_id'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

function getDayStatusDetail($student_id, $class_id, $date, $conn) {
    $hours = [1 => 'P', 2 => 'P', 3 => 'P', 4 => 'P', 5 => 'P'];
    $query = "SELECT hour, status FROM attendance WHERE student_id = ? AND class_id = ? AND date = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iis', $student_id, $class_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $hours[$row['hour']] = $row['status'];
    }

    $morningAbsent = 0;
    $afternoonAbsent = 0;

    foreach ([1, 2, 3] as $h) {
        if ($hours[$h] == 'A') $morningAbsent++;
    }
    foreach ([4, 5] as $h) {
        if ($hours[$h] == 'A') $afternoonAbsent++;
    }

    if ($morningAbsent > 0 && $afternoonAbsent > 0) return 'Absent';
    if ($morningAbsent > 0) return 'Half Day (Morning Absent)';
    if ($afternoonAbsent > 0) return 'Half Day (Afternoon Absent)';
    return 'Present';
}

// if (isset($_GET['export']) && $class_id) {
//     ob_clean();
//     $mpdf = new Mpdf();

//     // Get class name
//     $class_stmt = $conn->prepare("SELECT name FROM classes WHERE id = ?");
//     $class_stmt->bind_param("i", $class_id);
//     $class_stmt->execute();
//     $class_res = $class_stmt->get_result();
//     $class_row = $class_res->fetch_assoc();
//     $class_name = $class_row['name'] ?? 'Unknown';

//     // Get student list
//     $stmt = $conn->prepare("SELECT id, roll_no, name FROM students WHERE class_id = ? ORDER BY name");
//     $stmt->bind_param('i', $class_id);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     $html = "<style>
//     body { font-family: sans-serif; }
//     h3 { text-align: center; }
//     table { border-collapse: collapse; width: 100%; margin-top: 10px; }
//     th, td { border: 1px solid #999; padding: 8px; text-align: center; }
//     th { background-color: #f2f2f2; }
//     </style>";
//     $html .= "<h3>Attendance Report - Class: $class_name | Date: $date</h3>";
//     $html .= "<table>
//         <tr>
//             <th>Sl. No</th>
//             <th>Student ID</th>
//             <th>Student Name</th>
//             <th>Status</th>
//         </tr>";

//     $sn = 1;
//     while ($student = $result->fetch_assoc()) {
//         $status = getDayStatusDetail($student['id'], $class_id, $date, $conn);
//         $html .= "<tr>
//             <td>{$sn}</td>
//             <td>{$student['roll_no']}</td>
//             <td>{$student['name']}</td>
//             <td>{$status}</td>
//         </tr>";
//         $sn++;
//     }
//     $html .= "</table>";

//     $mpdf->WriteHTML($html);
//     $mpdf->Output("Attendance_Summary_{$class_name}_{$date}.pdf", "D");
//     exit;
// }

if (isset($_GET['export']) && $class_id) {
    ob_clean();
    $mpdf = new Mpdf();

    // Get class name
    $class_stmt = $conn->prepare("SELECT name FROM classes WHERE id = ?");
    $class_stmt->bind_param("i", $class_id);
    $class_stmt->execute();
    $class_res = $class_stmt->get_result();
    $class_row = $class_res->fetch_assoc();
    $class_name = $class_row['name'] ?? 'Unknown';

    // Get all attendance dates for that class
    $datesResult = $conn->prepare("SELECT DISTINCT date FROM attendance WHERE class_id = ? ORDER BY date");
    $datesResult->bind_param('i', $class_id);
    $datesResult->execute();
    $datesRes = $datesResult->get_result();

    $all_dates = [];
    while ($d = $datesRes->fetch_assoc()) {
        $all_dates[] = $d['date'];
    }
    $total_days = count($all_dates);

    // Get student list
    $stmt = $conn->prepare("SELECT id, roll_no, name FROM students WHERE class_id = ? ORDER BY name");
    $stmt->bind_param('i', $class_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Start PDF content
    $html = "<style>
        body { font-family: sans-serif; }
        h3 { text-align: center; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
    </style>";
    $html .= "<h3>Attendance Report - Class: $class_name | Date: $date</h3>";
    $html .= "<table>
        <tr>
            <th>Sl. No</th>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Status ($date)</th>
            <th>Present Days</th>
            <th>Total Days</th>
            <th>Percentage</th>
        </tr>";

    $sn = 1;
    while ($student = $result->fetch_assoc()) {
        $present_days = 0;
$daily_status = "";
        foreach ($all_dates as $day) {
            $daily_status = getDayStatusDetail($student['id'], $class_id, $day, $conn);
            if ($daily_status == 'Present') $present_days += 1;
            elseif (strpos($daily_status, 'Half Day') === 0) $present_days += 0.5;
        }

        $percentage = ($total_days > 0) ? round(($present_days / $total_days) * 100, 2) : 0;
        $status_today = getDayStatusDetail($student['id'], $class_id, $date, $conn);

        $html .= "<tr>
            <td>{$sn}</td>
            <td>{$student['roll_no']}</td>
            <td>{$student['name']}</td>
            <td>{$status_today}</td>
            <td>{$present_days}</td>
            <td>{$total_days}</td>
            <td>{$percentage}%</td>
        </tr>";
        $sn++;
    }
    $html .= "</table>";

    $mpdf->WriteHTML($html);
    $mpdf->Output("Attendance_Summary_{$class_name}_{$date}.pdf", "D");
    exit;
}


?>


<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<div class="container-fluid">
  <h4 class="mt-4">Attendance Summary Report</h4>

  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
      <label class="form-label">Select Class</label>
      <select name="class_id" class="form-select" required>
        <option value="">Select Class</option>
        <?php
        $classes = $conn->query("SELECT id, name FROM classes");
        while ($c = $classes->fetch_assoc()) {
          $selected = ($class_id == $c['id']) ? 'selected' : '';
          echo "<option value='{$c['id']}' $selected>{$c['name']}</option>";
        }
        ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Select Date</label>
      <input type="date" name="date" class="form-control" value="<?= $date ?>">
    </div>
    <div class="col-md-4 align-self-end">
      <button type="submit" class="btn btn-primary">Filter</button>
      <?php if ($class_id): ?>
      <a href="?class_id=<?= $class_id ?>&date=<?= $date ?>&export=1" class="btn btn-success">Export PDF</a>
      <?php endif; ?>
    </div>
  </form>

  <?php if ($class_id): ?>
  <div class="table-responsive">
    <table id="attendanceTable" class="table table-bordered">
      <thead>
  <tr>
    <th>Sl. No</th>
    <th>Student ID</th>
    <th>Student Name</th>
    <th>Status (<?= $date ?>)</th>
    <th>Present Days</th>
    <th>Total Days</th>
    <th>Percentage</th>
  </tr>
</thead>
<tbody>
  <?php
  // Get list of unique attendance dates for the selected class
  $datesResult = $conn->prepare("SELECT DISTINCT date FROM attendance WHERE class_id = ? ORDER BY date");
  $datesResult->bind_param('i', $class_id);
  $datesResult->execute();
  $datesRes = $datesResult->get_result();

  $all_dates = [];
  while ($d = $datesRes->fetch_assoc()) {
    $all_dates[] = $d['date'];
  }
  $total_days = count($all_dates);

  $stmt = $conn->prepare("SELECT id, roll_no, name FROM students WHERE class_id = ? ORDER BY name");
  $stmt->bind_param('i', $class_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $sn = 1;

  while ($student = $result->fetch_assoc()):
    $present_days = 0;

    foreach ($all_dates as $day) {
      $daily_status = getDayStatusDetail($student['id'], $class_id, $day, $conn);
      if ($daily_status == 'Present') $present_days += 1;
      elseif (str_starts_with($daily_status, 'Half Day')) $present_days += 0.5;
    }

    $percentage = ($total_days > 0) ? round(($present_days / $total_days) * 100, 2) : 0;

    // Current day status
    $status_today = getDayStatusDetail($student['id'], $class_id, $date, $conn);
  ?>
  <tr>
    <td><?= $sn++ ?></td>
    <td><?= htmlspecialchars($student['roll_no']) ?></td>
    <td><?= htmlspecialchars($student['name']) ?></td>
    <td><?= $status_today ?></td>
    <td><?= $present_days ?></td>
    <td><?= $total_days ?></td>
    <td><?= $percentage ?>%</td>
  </tr>
  <?php endwhile; ?>
</tbody>
    </table>
  </div>
  <script>
    $(document).ready(function () {
      $('#attendanceTable').DataTable();
    });
  </script>
  <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>