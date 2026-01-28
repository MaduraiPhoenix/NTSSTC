<?php
include('session.php');
include('../includes/db_connect.php');
$teacher_id = $_SESSION['teacher_id'];

// Filters
$filter_date = $_GET['date'] ?? '';
$filter_class = $_GET['class_id'] ?? '';

// Get classes assigned
$class_q = mysqli_query($conn, "SELECT c.id, c.name FROM classes c 
    JOIN class_teacher_map m ON c.id = m.class_id WHERE m.teacher_id = $teacher_id");

// Build base query
$query = "SELECT a.*, s.name AS student_name, s.roll_no, c.name AS class_name 
    FROM attendance a 
    JOIN students s ON a.student_id = s.id 
    JOIN classes c ON a.class_id = c.id 
    WHERE a.teacher_id = $teacher_id";

if ($filter_date) $query .= " AND a.date = '$filter_date'";
if ($filter_class) $query .= " AND a.class_id = $filter_class";

$query .= " ORDER BY a.date DESC, a.hour ASC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Attendance Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include('header.php'); ?>

<div class="container mt-4">
    <h4>📊 My Attendance Reports</h4>

    <form method="get" class="row mb-3 bg-white p-3 shadow-sm rounded">
        <div class="col-md-3">
            <label>Date</label>
            <input type="date" name="date" value="<?= $filter_date ?>" class="form-control">
        </div>
        <div class="col-md-3">
            <label>Class</label>
            <select name="class_id" class="form-select">
                <option value="">All</option>
                <?php while ($c = mysqli_fetch_assoc($class_q)) {
                    $sel = ($filter_class == $c['id']) ? 'selected' : '';
                    echo "<option value='{$c['id']}' $sel>{$c['name']}</option>";
                } ?>
            </select>
        </div>
        <div class="col-md-3 mt-4 pt-2">
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>

    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Hour</th>
                    <th>Student</th>
                    <th>Roll No</th>
                    <th>Class</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($result) == 0) {
                echo "<tr><td colspan='6' class='text-center'>No records found.</td></tr>";
            } else {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                        <td>{$row['date']}</td>
                        <td>{$row['hour']}</td>
                        <td>{$row['student_name']}</td>
                        <td>{$row['roll_no']}</td>
                        <td>{$row['class_name']}</td>
                        <td>{$row['status']}</td>
                    </tr>";
                }
            } ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
