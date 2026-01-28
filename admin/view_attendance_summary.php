<?php
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');
require_once '../vendor/autoload.php';
use Mpdf\Mpdf;

$class_id = $_GET['class_id'] ?? '';
$academic_year_id = $_GET['academic_year_id'] ?? '';
$semester_no = $_GET['semester_no'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

/* -------- DAY STATUS FUNCTION -------- */
function getDayStatusDetail($student_id, $class_id, $academic_year_id, $semester_no, $date, $conn) {

    $hours = [1=>'P',2=>'P',3=>'P',4=>'P',5=>'P'];

    $stmt = $conn->prepare(
        "SELECT hour, status
         FROM attendance
         WHERE student_id=? AND class_id=? AND academic_year_id=? AND semester_no=? AND date=?"
    );
    $stmt->bind_param("iiiis", $student_id, $class_id, $academic_year_id, $semester_no, $date);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($r = $res->fetch_assoc()) {
        $hours[$r['hour']] = $r['status'];
    }

    $morningAbsent = 0;
    $afternoonAbsent = 0;

    foreach ([1,2,3] as $h) if ($hours[$h]=='A') $morningAbsent++;
    foreach ([4,5] as $h) if ($hours[$h]=='A') $afternoonAbsent++;

    if ($morningAbsent>0 && $afternoonAbsent>0) return 'Absent';
    if ($morningAbsent>0) return 'Half Day (Morning Absent)';
    if ($afternoonAbsent>0) return 'Half Day (Afternoon Absent)';
    return 'Present';
}

/* -------- PDF EXPORT -------- */
if (isset($_GET['export']) && $class_id && $academic_year_id && $semester_no) {

    ob_clean();
    $mpdf = new Mpdf();

    $classRow = $conn->query("SELECT name FROM classes WHERE id=$class_id")->fetch_assoc();
    $ayRow = $conn->query("SELECT academic_year FROM academic_years WHERE id=$academic_year_id")->fetch_assoc();

    $class_name = $classRow['name'];
    $academic_year = $ayRow['academic_year'];

    // Working days
    $dates = [];
    $dres = $conn->query(
        "SELECT DISTINCT date
         FROM attendance
         WHERE class_id=$class_id
         AND academic_year_id=$academic_year_id
         AND semester_no=$semester_no
         ORDER BY date"
    );
    while ($d = $dres->fetch_assoc()) $dates[] = $d['date'];
    $total_days = count($dates);

    // Students
    $students = $conn->query(
        "SELECT id, roll_no, name
         FROM students
         WHERE class_id=$class_id
         AND academic_year_id=$academic_year_id
         ORDER BY roll_no"
    );

    $html = "
    <style>
    body{font-family:sans-serif;}
    table{border-collapse:collapse;width:100%;}
    th,td{border:1px solid #999;padding:6px;text-align:center;}
    th{background:#eee;}
    </style>
    <h3 align='center'>Attendance Report</h3>
    <p align='center'>
    <b>Class:</b> $class_name |
    <b>Academic Year:</b> $academic_year |
    <b>Semester:</b> $semester_no
    </p>
    <table>
    <tr>
      <th>Roll No</th>
      <th>Name</th>
      <th>Status ($date)</th>
      <th>Present Days</th>
      <th>Working Days</th>
      <th>%</th>
    </tr>";

    while ($s = $students->fetch_assoc()) {

        $present_days = 0;

        foreach ($dates as $d) {
            $st = getDayStatusDetail($s['id'],$class_id,$academic_year_id,$semester_no,$d,$conn);
            if ($st=='Present') $present_days += 1;
            elseif (strpos($st,'Half Day')===0) $present_days += 0.5;
        }

        $percent = ($total_days>0) ? round(($present_days/$total_days)*100,2) : 0;
        $today_status = getDayStatusDetail($s['id'],$class_id,$academic_year_id,$semester_no,$date,$conn);

        $html .= "
        <tr>
          <td>{$s['roll_no']}</td>
          <td>{$s['name']}</td>
          <td>$today_status</td>
          <td>$present_days</td>
          <td>$total_days</td>
          <td>$percent%</td>
        </tr>";
    }

    $html .= "</table>";

    $mpdf->WriteHTML($html);
    $mpdf->Output("Attendance_{$class_name}_Sem{$semester_no}.pdf","D");
    exit;
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<div class="container-fluid">
<h4 class="mt-4">Attendance Report</h4>

<form method="GET" class="row g-3 mb-4">

<div class="col-md-3">
<label>Class</label>
<select name="class_id" class="form-select" required onchange="this.form.submit()">
<option value="">Select</option>
<?php
$res = $conn->query("SELECT id,name FROM classes");
while($c=$res->fetch_assoc()){
$sel = ($class_id==$c['id'])?'selected':'';
echo "<option value='{$c['id']}' $sel>{$c['name']}</option>";
}
?>
</select>
</div>

<div class="col-md-3">
<label>Academic Year</label>
<select name="academic_year_id" class="form-select" required onchange="this.form.submit()">
<option value="">Select</option>
<?php
if($class_id){
$ay = $conn->query("SELECT id,academic_year FROM academic_years WHERE class_id=$class_id");
while($a=$ay->fetch_assoc()){
$sel = ($academic_year_id==$a['id'])?'selected':'';
echo "<option value='{$a['id']}' $sel>{$a['academic_year']}</option>";
}}
?>
</select>
</div>

<div class="col-md-2">
<label>Semester</label>
<select name="semester_no" class="form-select" required>
<option value="">Select</option>
<?php
if($class_id && $academic_year_id){
$ayRow=$conn->query("SELECT academic_year FROM academic_years WHERE id=$academic_year_id")->fetch_assoc();
$ayStr=$ayRow['academic_year'];

$sr = $conn->query(
    "SELECT semester_no
     FROM semesters
     WHERE class_id=$class_id AND academic_year='$ayStr'
     ORDER BY semester_no"
);
while($s=$sr->fetch_assoc()){
$sel = ($semester_no==$s['semester_no'])?'selected':'';
echo "<option value='{$s['semester_no']}' $sel>Sem {$s['semester_no']}</option>";
}}
?>
</select>
</div>

<div class="col-md-2">
<label>Date</label>
<input type="date" name="date" class="form-control" value="<?= $date ?>">
</div>

<div class="col-md-2 align-self-end">
<button class="btn btn-primary">Filter</button>
<?php if($class_id && $academic_year_id && $semester_no): ?>
<a href="?class_id=<?= $class_id ?>&academic_year_id=<?= $academic_year_id ?>&semester_no=<?= $semester_no ?>&date=<?= $date ?>&export=1"
class="btn btn-success">Export PDF</a>
<?php endif; ?>
</div>
</form>

<?php if($class_id && $academic_year_id && $semester_no): ?>
<table id="attendanceTable" class="table table-bordered">
<thead>
<tr>
<th>Roll No</th>
<th>Name</th>
<th>Status (<?= $date ?>)</th>
<th>Present Days</th>
<th>Working Days</th>
<th>%</th>
</tr>
</thead>
<tbody>
<?php
$dates=[];
$dres=$conn->query(
    "SELECT DISTINCT date FROM attendance
     WHERE class_id=$class_id
     AND academic_year_id=$academic_year_id
     AND semester_no=$semester_no
     ORDER BY date"
);
while($d=$dres->fetch_assoc()) $dates[]=$d['date'];
$total_days=count($dates);

$students=$conn->query(
    "SELECT id,roll_no,name
     FROM students
     WHERE class_id=$class_id
     AND academic_year_id=$academic_year_id
     ORDER BY roll_no"
);

while($s=$students->fetch_assoc()){
$present_days=0;
foreach($dates as $d){
$st=getDayStatusDetail($s['id'],$class_id,$academic_year_id,$semester_no,$d,$conn);
if($st=='Present') $present_days+=1;
elseif(strpos($st,'Half Day')===0) $present_days+=0.5;
}
$percent=($total_days>0)?round(($present_days/$total_days)*100,2):0;
$today_status=getDayStatusDetail($s['id'],$class_id,$academic_year_id,$semester_no,$date,$conn);
echo "
<tr>
<td>{$s['roll_no']}</td>
<td>{$s['name']}</td>
<td>$today_status</td>
<td>$present_days</td>
<td>$total_days</td>
<td>$percent%</td>
</tr>";
}
?>
</tbody>
</table>

<script>
$(function(){ $('#attendanceTable').DataTable(); });
</script>
<?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>
