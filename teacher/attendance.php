<?php
include('session.php');
include('../includes/db_connect.php');

$teacher_id = $_SESSION['teacher_id'];
$success = '';
$error = '';

// Get assigned classes
 $classrq = "SELECT c.id, c.name FROM classes c 
    JOIN class_teacher_map m ON c.id = m.class_id WHERE m.teacher_id = $teacher_id";
        $classes_q = mysqli_query($conn,$classrq );

// Submit attendance
if (isset($_POST['submit'])) {
    $class_id = $_POST['class_id'];
    $date = $_POST['date'];
    $hour = $_POST['hour'];
    $att_data = $_POST['attendance'] ?? [];

    // Check if already marked
    $check = mysqli_query($conn, "SELECT * FROM attendance 
        WHERE class_id=$class_id AND date='$date' AND hour=$hour AND teacher_id=$teacher_id");

    if (mysqli_num_rows($check) > 0) {
        $error = "⚠️ Attendance already submitted for this class, date & hour.";
    } else {
        foreach ($att_data as $student_id => $status) {
            mysqli_query($conn, "INSERT INTO attendance (student_id, class_id, teacher_id, date, hour, status) 
                VALUES ($student_id, $class_id, $teacher_id, '$date', $hour, '$status')");
        }
        $success = "✅ Attendance submitted successfully.";
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
    <h4>📋 Take Attendance</h4>

    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="post" class="bg-white p-3 rounded shadow-sm mb-4">
        <div class="row">
            <div class="col-md-3">
                <label>Date</label>
                <input type="date" name="date" value="<?= $_POST['date'] ?? date('Y-m-d') ?>" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label>Hour</label>
                <select name="hour" class="form-select" required>
                    <option value="">Select</option>
                    <?php 
                    for ($i = 1; $i <= 5; $i++) {
                        $sel = ($_POST['hour'] ?? '') == $i ? 'selected' : '';
                        echo "<option value='$i' $sel>$i</option>";
                    } 
                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label>Class</label>
                <select name="class_id" class="form-select" required>
                    <option value="">Select</option>
                    <?php 
                    mysqli_data_seek($classes_q, 0); // Reset result pointer
                    while ($c = mysqli_fetch_assoc($classes_q)) {
                        $sel = ($_POST['class_id'] ?? '') == $c['id'] ? 'selected' : '';
                        echo "<option value='{$c['id']}' $sel>{$c['name']}</option>";
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
    if (isset($_POST['show']) && $_POST['class_id'] && $_POST['date'] && $_POST['hour']) {
        $class_id = $_POST['class_id'];
        $date = $_POST['date'];
        $hour = $_POST['hour'];

        $stu_q = mysqli_query($conn, "SELECT * FROM students WHERE class_id = $class_id");

        if (mysqli_num_rows($stu_q) == 0) {
            echo "<div class='alert alert-warning'>No students found in selected class.</div>";
        } else {
            // Check if already marked
            $check_q = mysqli_query($conn, "SELECT * FROM attendance 
                WHERE class_id=$class_id AND date='$date' AND hour=$hour AND teacher_id=$teacher_id");

            if (mysqli_num_rows($check_q) > 0) {
                echo "<div class='alert alert-info'>Attendance already submitted for this selection.</div>";
            } else {
                ?>
                <form method="post">
                    <input type="hidden" name="class_id" value="<?= $class_id ?>">
                    <input type="hidden" name="date" value="<?= $date ?>">
                    <input type="hidden" name="hour" value="<?= $hour ?>">

                    <table class="table table-bordered bg-white">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Roll No</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $n = 1;
                        while ($s = mysqli_fetch_assoc($stu_q)) { ?>
                            <tr>
                                <td><?= $n++ ?></td>
                                <td><?= $s['name'] ?></td>
                                <td><?= $s['roll_no'] ?></td>
                                <td>
                                    <label><input type="radio" name="attendance[<?= $s['id'] ?>]" value="P" required> P</label>
                                    &nbsp;
                                    <label><input type="radio" name="attendance[<?= $s['id'] ?>]" value="A" required> A</label>
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
