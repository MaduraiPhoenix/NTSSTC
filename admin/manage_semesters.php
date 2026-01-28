<?php
include('../includes/session.php');
checkAdminSession();
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

$msg = "";

/* ---------- SAVE SEMESTER ---------- */
if (isset($_POST['save'])) {

    $class_id      = $_POST['class_id'];
    $name          = $_POST['name'];
    $academic_year = $_POST['academic_year'];
    $start_date    = $_POST['start_date'];
    $end_date      = $_POST['end_date'];
    $is_active     = isset($_POST['is_active']) ? 1 : 0;

    // Deactivate other semesters of same class if active
    if ($is_active) {
        $conn->query("UPDATE semesters SET is_active=0 WHERE class_id=$class_id");
    }

    $stmt = $conn->prepare(
        "INSERT INTO semesters (class_id,name,academic_year,start_date,end_date,is_active)
         VALUES (?,?,?,?,?,?)"
    );
    $stmt->bind_param(
        "issssi",
        $class_id,
        $name,
        $academic_year,
        $start_date,
        $end_date,
        $is_active
    );
    $stmt->execute();

    $msg = "Semester created successfully.";
}

/* ---------- TOGGLE ACTIVE ---------- */
if (isset($_GET['activate'])) {
    $sid = $_GET['activate'];

    $row = $conn->query("SELECT class_id FROM semesters WHERE id=$sid")->fetch_assoc();
    $cid = $row['class_id'];

    $conn->query("UPDATE semesters SET is_active=0 WHERE class_id=$cid");
    $conn->query("UPDATE semesters SET is_active=1 WHERE id=$sid");

    header("Location: manage_semesters.php");
    exit;
}
?>

<div class="container-fluid">
<h4 class="mt-4">Manage Semesters</h4>

<?php if($msg): ?>
<div class="alert alert-success"><?= $msg ?></div>
<?php endif; ?>

<!-- ADD SEMESTER -->
<div class="card mb-4">
<div class="card-header">Add New Semester</div>
<div class="card-body">
<form method="post" class="row g-3">

<div class="col-md-4">
<label>Class</label>
<select name="class_id" class="form-select" required>
<option value="">Select</option>
<?php
$res = $conn->query("SELECT id,name FROM classes ORDER BY name");
while($c=$res->fetch_assoc()){
echo "<option value='{$c['id']}'>{$c['name']}</option>";
}
?>
</select>
</div>

<div class="col-md-4">
<label>Semester Name</label>
<input type="text" name="name" class="form-control" required>
</div>

<div class="col-md-4">
<label>Academic Year</label>
<input type="text" name="academic_year" class="form-control" placeholder="2025-2026" required>
</div>

<div class="col-md-3">
<label>Start Date</label>
<input type="date" name="start_date" class="form-control" required>
</div>

<div class="col-md-3">
<label>End Date</label>
<input type="date" name="end_date" class="form-control" required>
</div>

<div class="col-md-3 align-self-end">
<div class="form-check">
<input type="checkbox" name="is_active" class="form-check-input" id="act">
<label for="act" class="form-check-label">Set as Active</label>
</div>
</div>

<div class="col-md-3 align-self-end">
<button class="btn btn-success" name="save">Save Semester</button>
</div>

</form>
</div>
</div>

<!-- LIST SEMESTERS -->
<div class="card">
<div class="card-header">Semester List</div>
<div class="card-body">
<table class="table table-bordered">
<thead>
<tr>
<th>Class</th>
<th>Semester</th>
<th>Academic Year</th>
<th>Start</th>
<th>End</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php
$q = $conn->query(
    "SELECT s.*, c.name AS class_name
     FROM semesters s
     JOIN classes c ON c.id=s.class_id
     ORDER BY s.start_date DESC"
);
while($r=$q->fetch_assoc()){
$status = $r['is_active'] ? 'Active' : 'Inactive';
$btn = !$r['is_active']
    ? "<a href='?activate={$r['id']}' class='btn btn-sm btn-primary'>Activate</a>"
    : "-";

echo "
<tr>
<td>{$r['class_name']}</td>
<td>{$r['name']}</td>
<td>{$r['academic_year']}</td>
<td>{$r['start_date']}</td>
<td>{$r['end_date']}</td>
<td>$status</td>
<td>$btn</td>
</tr>";
}
?>
</tbody>
</table>
</div>
</div>

</div>

<?php include('../includes/footer.php'); ?>
