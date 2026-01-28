<?php
include('../includes/session.php');
checkAdminSession();
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

$success = '';
$class_id = $_GET['class_id'] ?? '';
$academic_year = $_GET['academic_year'] ?? '';

// ADD SEMESTER
if (isset($_POST['add'])) {
    $class_id = (int)$_POST['class_id'];
    $academic_year = $_POST['academic_year'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // block if current semester still running
    $chk = mysqli_query($conn,
        "SELECT COUNT(*) AS cnt
         FROM semesters
         WHERE class_id=$class_id
         AND academic_year='$academic_year'
         AND end_date >= CURDATE()"
    );
    if (mysqli_fetch_assoc($chk)['cnt'] > 0) {
        die('Current semester still running');
    }

    // max semesters
    $c = mysqli_query($conn,"SELECT total_semesters FROM classes WHERE id=$class_id");
    $total = mysqli_fetch_assoc($c)['total_semesters'];

    $q = mysqli_query($conn,
        "SELECT IFNULL(MAX(semester_no),0)+1 AS next_sem
         FROM semesters
         WHERE class_id=$class_id AND academic_year='$academic_year'"
    );
    $next_sem = mysqli_fetch_assoc($q)['next_sem'];

    if ($next_sem > $total) {
        die('Maximum semesters reached');
    }

    mysqli_query($conn,
        "INSERT INTO semesters
        (class_id, academic_year, semester_no, start_date, end_date, is_active)
        VALUES
        ($class_id,'$academic_year',$next_sem,'$start_date','$end_date',1)"
    );

    mysqli_query($conn,
        "UPDATE students SET semester=$next_sem WHERE class_id=$class_id"
    );

    $success = "Semester $next_sem added.";
}

// UPDATE DATES
if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    mysqli_query($conn,
        "UPDATE semesters
         SET start_date='$start_date', end_date='$end_date'
         WHERE id=$id"
    );
    $success = "Semester dates updated.";
}
?>

<div class="container-fluid">
<h4 class="mb-3">Semester Management</h4>

<?php if ($success) { ?>
<div class="alert alert-success"><?= $success ?></div>
<?php } ?>

<form method="get" class="row mb-3">
    <div class="col-md-4">
        <label>Class</label>
        <select name="class_id" class="form-control" onchange="this.form.submit()" required>
            <option value="">Select</option>
            <?php
            $c = mysqli_query($conn,"SELECT * FROM classes ORDER BY name");
            while ($r = mysqli_fetch_assoc($c)) {
                $sel = ($class_id==$r['id'])?'selected':'';
                echo "<option value='{$r['id']}' $sel>{$r['name']}</option>";
            }
            ?>
        </select>
    </div>

    <div class="col-md-4">
        <label>Academic Year</label>
        <select name="academic_year" class="form-control" onchange="this.form.submit()" required>
            <option value="">Select</option>
            <?php
            if ($class_id) {
                $ay = mysqli_query($conn,
                    "SELECT academic_year FROM academic_years WHERE class_id=$class_id"
                );
                while ($r = mysqli_fetch_assoc($ay)) {
                    $sel = ($academic_year==$r['academic_year'])?'selected':'';
                    echo "<option value='{$r['academic_year']}' $sel>{$r['academic_year']}</option>";
                }
            }
            ?>
        </select>
    </div>
</form>

<?php if ($class_id && $academic_year) { ?>
<form method="post" class="border p-3 mb-4 bg-light">
<input type="hidden" name="class_id" value="<?= $class_id ?>">
<input type="hidden" name="academic_year" value="<?= $academic_year ?>">

<div class="row">
    <div class="col-md-3">
        <label>Start Date</label>
        <input type="date" name="start_date" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label>End Date</label>
        <input type="date" name="end_date" class="form-control" required>
    </div>
    <div class="col-md-3 mt-4">
        <button name="add" class="btn btn-success">Add Semester</button>
    </div>
</div>
</form>
<?php } ?>

<table id="semesterTable" class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>Class</th>
<th>Academic Year</th>
<th>Semester</th>
<th>Status</th>
<th>Edit Dates</th>
</tr>
</thead>
<tbody>
<?php
$today = date('Y-m-d');

$q = mysqli_query($conn,
    "SELECT s.*, c.name AS class_name
     FROM semesters s
     JOIN classes c ON c.id=s.class_id
     ORDER BY
       (s.start_date <= CURDATE() AND s.end_date >= CURDATE()) DESC,
       s.start_date DESC"
);

while ($r = mysqli_fetch_assoc($q)) {

    if ($r['end_date'] < $today) {
        $status = 'COMPLETED';
    } elseif ($r['start_date'] <= $today && $today <= $r['end_date']) {
        $status = 'RUNNING';
    } else {
        $status = 'UPCOMING';
    }

$year_no = ceil($r['semester_no'] / 2);
$roman = ['','I','II','III','IV','V'];
$display_class = $r['class_name'].' '.$roman[$year_no];


    echo "<tr>
    
    <td>{$display_class}</td>
    <td>{$r['academic_year']}</td>
    <td>Sem {$r['semester_no']}</td>
    <td>{$status}</td>
    <td>
        <form method='post' class='d-flex'>
            <input type='hidden' name='id' value='{$r['id']}'>
            <input type='date' name='start_date' value='{$r['start_date']}' class='form-control form-control-sm me-1'>
            <input type='date' name='end_date' value='{$r['end_date']}' class='form-control form-control-sm me-1'>
            <button name='update' class='btn btn-sm btn-primary'>Update</button>
        </form>
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
$(function () {
    $('#semesterTable').DataTable({
        order: [[3, 'desc']]
    });
});
</script>
