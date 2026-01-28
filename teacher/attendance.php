<?php
include('session.php');
include('../includes/db_connect.php');

$teacher_id = $_SESSION['teacher_id'];
$success = '';
$error = '';

$class_id = '';
$academic_year_id = '';
$academic_year = '';
$date = date('Y-m-d');
$hour = '';

// =========================
// FETCH ASSIGNED CLASS + ACADEMIC YEARS
// =========================
$classrq = "
SELECT DISTINCT 
    c.id AS class_id,
    c.name AS class_name,
    ay.id AS academic_year_id,
    ay.academic_year
FROM class_teacher_map m
JOIN classes c ON c.id = m.class_id
JOIN academic_years ay ON ay.id = m.academic_year_id
WHERE m.teacher_id = $teacher_id
";
$classes_q = mysqli_query($conn, $classrq);

// =========================
// HANDLE SHOW STUDENTS
// =========================
if (isset($_POST['show'])) {

    $date = $_POST['date'];
    $hour = $_POST['hour'];

    [$class_id, $academic_year_id] = explode('|', $_POST['class_year']);

    // academic_year string
    $ayq = mysqli_query($conn,
        "SELECT academic_year FROM academic_years WHERE id=$academic_year_id"
    );
    $academic_year = mysqli_fetch_assoc($ayq)['academic_year'];
}

// =========================
// HANDLE SUBMIT ATTENDANCE
// =========================
if (isset($_POST['submit'])) {

    $class_id = (int)$_POST['class_id'];
    $academic_year_id = (int)$_POST['academic_year_id'];
    $date = $_POST['date'];
    $hour = $_POST['hour'];
    $att_data = $_POST['attendance'] ?? [];

    // academic_year string
    $ayq = mysqli_query($conn,
        "SELECT academic_year FROM academic_years WHERE id=$academic_year_id"
    );
    $academic_year = mysqli_fetch_assoc($ayq)['academic_year'];

    // running semester
    $semq = mysqli_query($conn,"
        SELECT semester_no
        FROM semesters
        WHERE class_id=$class_id
        AND academic_year='$academic_year'
        AND start_date <= '$date'
        AND end_date >= '$date'
        LIMIT 1
    ");

    if (mysqli_num_rows($semq) == 0) {
        $error = "No running semester found.";
    } else {

        $semester_no = mysqli_fetch_assoc($semq)['semester_no'];

        // duplicate check
        $check = mysqli_query($conn,"
            SELECT id FROM attendance
            WHERE class_id=$class_id
            AND academic_year_id=$academic_year_id
            AND semester_no=$semester_no
            AND date='$date'
            AND hour=$hour
            AND teacher_id=$teacher_id
        ");

        if (mysqli_num_rows($check) > 0) {
            $error = "Attendance already submitted.";
        } else {

            foreach ($att_data as $student_id => $status) {
                mysqli_query($conn,"
                    INSERT INTO attendance
                    (student_id, class_id, academic_year_id, semester_no, teacher_id, date, hour, status)
                    VALUES
                    ($student_id,$class_id,$academic_year_id,$semester_no,$teacher_id,'$date',$hour,'$status')
                ");
            }

            $success = "Attendance submitted successfully.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Take Attendance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include('header.php'); ?>

<div class="container mt-4">
<h4>Take Attendance</h4>

<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

<form method="post" class="bg-white p-3 rounded shadow-sm mb-4">
<div class="row">

    <div class="col-md-3">
        <label>Date</label>
        <input type="date" name="date" value="<?= $date ?>" class="form-control" required>
    </div>

    <div class="col-md-2">
        <label>Hour</label>
        <select name="hour" class="form-select" required>
            <option value="">Select</option>
            <?php for ($i=1;$i<=5;$i++) {
                $sel = ($hour==$i)?'selected':'';
                echo "<option value='$i' $sel>$i</option>";
            } ?>
        </select>
    </div>

    <div class="col-md-4">
        <label>Class / Academic Year</label>
        <select name="class_year" class="form-select" required>
            <option value="">Select</option>
            <?php
            mysqli_data_seek($classes_q,0);
            while ($c = mysqli_fetch_assoc($classes_q)) {
                $val = $c['class_id'].'|'.$c['academic_year_id'];
                $sel = (isset($_POST['class_year']) && $_POST['class_year']==$val)?'selected':'';
                echo "<option value='$val' $sel>{$c['class_name']} ({$c['academic_year']})</option>";
            }
            ?>
        </select>
    </div>

    <div class="col-md-3 mt-4 pt-2">
        <button name="show" class="btn btn-primary">Show Students</button>
    </div>
</div>
</form>

<?php
// =========================
// SHOW STUDENTS LIST
// =========================
if (isset($_POST['show'])) {

    // running semester
    $semq = mysqli_query($conn,"
        SELECT semester_no
        FROM semesters
        WHERE class_id=$class_id
        AND academic_year='$academic_year'
        AND start_date <= '$date'
        AND end_date >= '$date'
        LIMIT 1
    ");

    if (mysqli_num_rows($semq)==0) {
        echo "<div class='alert alert-warning'>No running semester.</div>";
    } else {

        $semester_no = mysqli_fetch_assoc($semq)['semester_no'];

        $stu_q = mysqli_query($conn,"
            SELECT * FROM students
            WHERE class_id=$class_id
            AND academic_year_id=$academic_year_id
            AND semester=$semester_no
            ORDER BY roll_no
        ");

        if (mysqli_num_rows($stu_q)==0) {
            echo "<div class='alert alert-warning'>No students found.</div>";
        } else {
?>
<form method="post">
<input type="hidden" name="class_id" value="<?= $class_id ?>">
<input type="hidden" name="academic_year_id" value="<?= $academic_year_id ?>">
<input type="hidden" name="date" value="<?= $date ?>">
<input type="hidden" name="hour" value="<?= $hour ?>">

<table class="table table-bordered bg-white">
<thead class="table-dark">
<tr>
<th>#</th>
<th>Roll No</th>
<th>Name</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php $n=1;
while ($s=mysqli_fetch_assoc($stu_q)) { ?>
<tr>
<td><?= $n++ ?></td>
<td><?= $s['roll_no'] ?></td>
<td><?= $s['name'] ?></td>
<td>
<label><input type="radio" name="attendance[<?= $s['id'] ?>]" value="P" required> P</label>
<label class="ms-2"><input type="radio" name="attendance[<?= $s['id'] ?>]" value="A" required> A</label>
</td>
</tr>
<?php } ?>
</tbody>
</table>

<button name="submit" class="btn btn-success">Save Attendance</button>
</form>
<?php
        }
    }
}
?>
</div>
</body>
</html>
