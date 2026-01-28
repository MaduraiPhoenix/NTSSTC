<?php
include('../includes/session.php');
checkAdminSession();
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

$msg = "";
$class_id = $_POST['class_id'] ?? '';
$semester_id = $_POST['semester_id'] ?? '';

if (isset($_POST['assign'])) {

    $stmt = $conn->prepare(
        "UPDATE students
         SET current_semester_id = ?
         WHERE class_id = ?
         AND status = 'active'"
    );
    $stmt->bind_param("ii", $semester_id, $class_id);
    $stmt->execute();

    $msg = "Students assigned to semester successfully.";
}
?>

<div class="container-fluid">
<h4 class="mt-4">Assign Students to Semester</h4>

<?php if($msg): ?>
<div class="alert alert-success"><?= $msg ?></div>
<?php endif; ?>

<form method="post" class="row g-3 mb-4">

<div class="col-md-4">
<label>Class</label>
<select name="class_id" class="form-select" required onchange="this.form.submit()">
<option value="">Select Class</option>
<?php
$res = $conn->query("SELECT id,name FROM classes ORDER BY name");
while($c=$res->fetch_assoc()){
$sel = ($class_id==$c['id'])?'selected':'';
echo "<option value='{$c['id']}' $sel>{$c['name']}</option>";
}
?>
</select>
</div>

<div class="col-md-4">
<label>Semester</label>
<select name="semester_id" class="form-select" required>
<option value="">Select Semester</option>
<?php
if($class_id){
$sr = $conn->query(
    "SELECT id,name 
     FROM semesters 
     WHERE class_id=$class_id 
     ORDER BY start_date"
);
while($s=$sr->fetch_assoc()){
$sel = ($semester_id==$s['id'])?'selected':'';
echo "<option value='{$s['id']}' $sel>{$s['name']}</option>";
}}
?>
</select>
</div>

<div class="col-md-4 align-self-end">
<button class="btn btn-primary" name="assign">Assign Students</button>
</div>

</form>

<?php if($class_id): ?>
<div class="card">
<div class="card-header">Students in Class</div>
<div class="card-body">
<table class="table table-bordered">
<tr>
<th>Roll No</th>
<th>Name</th>
<th>Current Semester</th>
</tr>
<?php
$q = $conn->query(
    "SELECT s.roll_no, s.name, sm.name AS sem_name
     FROM students s
     LEFT JOIN semesters sm ON sm.id = s.current_semester_id
     WHERE s.class_id=$class_id
     AND s.status='active'
     ORDER BY s.roll_no ASC"
);
while($r=$q->fetch_assoc()){
echo "
<tr>
<td>{$r['roll_no']}</td>
<td>{$r['name']}</td>
<td>{$r['sem_name']}</td>
</tr>";
}
?>
</table>
</div>
</div>
<?php endif; ?>

</div>

<?php include('../includes/footer.php'); ?>
