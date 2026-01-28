<?php
include('../includes/session.php');
checkAdminSession();
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

$name = '';
$roll_no = '';
$department = '';
$class_id = '';
$academic_year_id = '';
$edit_id = 0;
$success = '';

// GET CURRENT RUNNING SEMESTER
function getCurrentSemester($conn, $class_id, $academic_year_id) {

    $ayq = mysqli_query($conn,
        "SELECT academic_year FROM academic_years WHERE id=$academic_year_id"
    );
    $academic_year = mysqli_fetch_assoc($ayq)['academic_year'];

    $q = mysqli_query($conn,
        "SELECT semester_no
         FROM semesters
         WHERE class_id=$class_id
         AND academic_year='$academic_year'
         AND start_date <= CURDATE()
         AND end_date >= CURDATE()
         LIMIT 1"
    );

    if (mysqli_num_rows($q) == 0) return 1;

    return mysqli_fetch_assoc($q)['semester_no'];
}

// SAVE
if (isset($_POST['save'])) {

    $name = $_POST['name'];
    $roll_no = $_POST['roll_no'];
    $department = $_POST['department'];
    $class_id = (int)$_POST['class_id'];
    $academic_year_id = (int)$_POST['academic_year_id'];

    $semester = getCurrentSemester($conn, $class_id, $academic_year_id);

    if (!empty($_POST['edit_id'])) {

        $eid = (int)$_POST['edit_id'];

        mysqli_query($conn,
            "UPDATE students SET
                name='$name',
                roll_no='$roll_no',
                department='$department',
                class_id=$class_id,
                academic_year_id=$academic_year_id,
                semester=$semester
             WHERE id=$eid"
        );

        $success = "Student updated successfully.";

    } else {

        mysqli_query($conn,
            "INSERT INTO students
            (name, roll_no, department, class_id, academic_year_id, semester)
            VALUES
            ('$name','$roll_no','$department',$class_id,$academic_year_id,$semester)"
        );

        $success = "Student added successfully.";
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    mysqli_query($conn,"DELETE FROM students WHERE id=$did");
    $success = "Student deleted.";
}

// EDIT
if (isset($_GET['edit'])) {

    $eid = (int)$_GET['edit'];
    $q = mysqli_query($conn,"SELECT * FROM students WHERE id=$eid");
    $r = mysqli_fetch_assoc($q);

    $name = $r['name'];
    $roll_no = $r['roll_no'];
    $department = $r['department'];
    $class_id = $r['class_id'];
    $academic_year_id = $r['academic_year_id'];
    $edit_id = $r['id'];
}
?>

<div class="container-fluid">
<h4 class="mb-3">Manage Students</h4>

<?php if ($success) { ?>
<div class="alert alert-success"><?= $success ?></div>
<?php } ?>

<form method="post" class="border rounded p-3 mb-4 bg-light">
<input type="hidden" name="edit_id" value="<?= $edit_id ?>">

<div class="row">
    <div class="col-md-3">
        <label>Class</label>
        <select name="class_id" class="form-control" required onchange="this.form.submit()">
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

    <div class="col-md-3">
        <label>Academic Year</label>
        <select name="academic_year_id" class="form-control" required>
            <option value="">Select</option>
            <?php
            if ($class_id) {
                $ay = mysqli_query($conn,
                    "SELECT id, academic_year
                     FROM academic_years
                     WHERE class_id=$class_id"
                );
                while ($r = mysqli_fetch_assoc($ay)) {
                    $sel = ($academic_year_id==$r['id'])?'selected':'';
                    echo "<option value='{$r['id']}' $sel>{$r['academic_year']}</option>";
                }
            }
            ?>
        </select>
    </div>

    <div class="col-md-2">
        <label>Roll No</label>
        <input type="text" name="roll_no" value="<?= $roll_no ?>" class="form-control" required>
    </div>

    <div class="col-md-2">
        <label>Name</label>
        <input type="text" name="name" value="<?= $name ?>" class="form-control" required>
    </div>

    <div class="col-md-2">
        <label>Department</label>
        <input type="text" name="department" value="<?= $department ?>" class="form-control" required>
    </div>
</div>

<div class="mt-3">
<button name="save" class="btn btn-success"><?= $edit_id?'Update':'Add' ?></button>
<?php if ($edit_id) { ?>
<a href="manage_students.php" class="btn btn-secondary">Cancel</a>
<?php } ?>
</div>
</form>

<table id="studentTable" class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Roll No</th>
<th>Name</th>
<th>Class</th>
<th>Semester</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php
$q = mysqli_query($conn,
    "SELECT s.*, c.name AS class_name
     FROM students s
     JOIN classes c ON c.id=s.class_id
     ORDER BY s.id DESC"
);
while ($r = mysqli_fetch_assoc($q)) {
echo "<tr>
<td>{$r['id']}</td>
<td>{$r['roll_no']}</td>
<td>{$r['name']}</td>
<td>{$r['class_name']}</td>
<td>{$r['semester']}</td>
<td>
<a href='manage_students.php?edit={$r['id']}' class='btn btn-sm btn-warning'>Edit</a>
<a href='manage_students.php?delete={$r['id']}' class='btn btn-sm btn-danger'
onclick=\"return confirm('Delete?')\">Delete</a>
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
$(function(){ $('#studentTable').DataTable(); });
</script>
