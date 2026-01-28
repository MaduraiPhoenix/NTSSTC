<?php
include('../includes/session.php');
checkAdminSession();
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

$success = '';
$selected_class = $_GET['class_id'] ?? '';
$assigned = [];

// Save if form submitted
if (isset($_POST['assign'])) {
    $selected_class = $_POST['class_id'];
    $teacher_ids = $_POST['teacher_ids'] ?? [];

    // Remove old assignments
    mysqli_query($conn, "DELETE FROM class_teacher_map WHERE class_id = $selected_class");

    // Insert new assignments (no limit)
    foreach ($teacher_ids as $tid) {
        $stmt = $conn->prepare("INSERT INTO class_teacher_map (class_id, teacher_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $selected_class, $tid);
        $stmt->execute();
    }
    $success = "Teachers assigned successfully.";
}

// Load assigned teachers
if (!empty($selected_class)) {
    $res = mysqli_query($conn, "SELECT teacher_id FROM class_teacher_map WHERE class_id = $selected_class");
    while ($row = mysqli_fetch_assoc($res)) {
        $assigned[] = $row['teacher_id'];
    }
}
?>

<div class="container">
    <h4 class="mb-3">Assign Teachers to Class</h4>

    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

    <form method="get" class="mb-3">
        <label>Select Class</label>
        <select name="class_id" class="form-select" onchange="this.form.submit()" required>
            <option value="">-- Select Class --</option>
            <?php
            $classes = mysqli_query($conn, "SELECT * FROM classes");
            while ($c = mysqli_fetch_assoc($classes)) {
                $sel = ($selected_class == $c['id']) ? 'selected' : '';
                echo "<option value='{$c['id']}' $sel>{$c['name']} ({$c['department']})</option>";
            }
            ?>
        </select>
    </form>

    <?php if ($selected_class) { ?>
        <form method="post">
            <input type="hidden" name="class_id" value="<?= $selected_class ?>">
            <div class="mb-3">
                <label>Select Teachers</label>
                <select name="teacher_ids[]" class="form-select" multiple size="8" required>
                    <?php
                    $teachers = mysqli_query($conn, "SELECT * FROM teachers");
                    while ($t = mysqli_fetch_assoc($teachers)) {
                        $checked = in_array($t['id'], $assigned) ? 'selected' : '';
                        echo "<option value='{$t['id']}' $checked>{$t['name']} ({$t['email']})</option>";
                    }
                    ?>
                </select>
                <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple teachers.</small>
            </div>
            <button class="btn btn-success" name="assign">Save Assignment</button>
        </form>
    <?php } ?>
</div>

<?php include('../includes/footer.php'); ?>
