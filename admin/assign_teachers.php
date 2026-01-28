<?php
include('../includes/session.php');
checkAdminSession();
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

$success = '';
$class_id = $_GET['class_id'] ?? '';
$academic_year_id = $_GET['academic_year_id'] ?? '';
$assigned = [];

// SAVE
if (isset($_POST['assign'])) {
    $class_id = (int)$_POST['class_id'];
    $academic_year_id = (int)$_POST['academic_year_id'];
    $teacher_ids = $_POST['teacher_ids'] ?? [];

    mysqli_query($conn,
        "DELETE FROM class_teacher_map
         WHERE class_id=$class_id AND academic_year_id=$academic_year_id"
    );

    foreach ($teacher_ids as $tid) {
        $stmt = $conn->prepare(
            "INSERT INTO class_teacher_map (class_id, academic_year_id, teacher_id)
             VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iii", $class_id, $academic_year_id, $tid);
        $stmt->execute();
    }

    $success = "Teachers assigned successfully.";
}

// LOAD ASSIGNED
if ($class_id && $academic_year_id) {
    $res = mysqli_query($conn,
        "SELECT teacher_id FROM class_teacher_map
         WHERE class_id=$class_id AND academic_year_id=$academic_year_id"
    );
    while ($r = mysqli_fetch_assoc($res)) {
        $assigned[] = $r['teacher_id'];
    }
}
?>

<div class="container-fluid">
<h4 class="mb-3">Assign Teachers</h4>

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
        <select name="academic_year_id" class="form-control" onchange="this.form.submit()" required>
            <option value="">Select</option>
            <?php
            if ($class_id) {
                $ay = mysqli_query($conn,
                    "SELECT id, academic_year
                     FROM academic_years
                     WHERE class_id=$class_id AND status='ACTIVE'"
                );
                while ($r = mysqli_fetch_assoc($ay)) {
                    $sel = ($academic_year_id==$r['id'])?'selected':'';
                    echo "<option value='{$r['id']}' $sel>{$r['academic_year']}</option>";
                }
            }
            ?>
        </select>
    </div>
</form>

<?php if ($class_id && $academic_year_id) { ?>
<form method="post">
<input type="hidden" name="class_id" value="<?= $class_id ?>">
<input type="hidden" name="academic_year_id" value="<?= $academic_year_id ?>">

<div class="mb-3">
<label>Select Teachers</label>
<select name="teacher_ids[]" class="form-control" multiple size="8" required>
<?php
$t = mysqli_query($conn,"SELECT * FROM teachers ORDER BY name");
while ($r = mysqli_fetch_assoc($t)) {
    $sel = in_array($r['id'],$assigned)?'selected':'';
    echo "<option value='{$r['id']}' $sel>{$r['name']} ({$r['email']})</option>";
}
?>
</select>
</div>

<button class="btn btn-success" name="assign">Save Assignment</button>
</form>
<?php } ?>

</div>

<?php include('../includes/footer.php'); ?>
