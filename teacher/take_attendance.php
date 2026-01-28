<?php
include('../includes/session.php');
checkTeacherSession();
include('../includes/db_connect.php');

$date = date('Y-m-d');
$hour = $_POST['hour'] ?? '';
$class = $_POST['class'] ?? '';

if (isset($_POST['submit'])) {
    foreach ($_POST['attendance'] as $student_id => $status) {
        $check = $conn->query("SELECT * FROM attendance WHERE student_id=$student_id AND date='$date' AND hour=$hour");
        if ($check->num_rows == 0) {
            $conn->query("INSERT INTO attendance (student_id, teacher_id, date, hour, status, class)
                          VALUES ($student_id, {$_SESSION['teacher_id']}, '$date', $hour, '$status', '$class')");
        }
    }
    echo "<p style='color:green;'>Attendance saved for Hour $hour</p>";
}

?>

<h2>Take Attendance</h2>
<form method="post">
    <label>Date: </label><input type="date" name="date" value="<?= $date ?>" readonly><br>
    <label>Hour (1–5): </label><input type="number" name="hour" min="1" max="5" required><br>
    <label>Class/Section: </label><input type="text" name="class" required><br>
    <button type="submit" name="load">Load Students</button>
</form>

<?php
if (isset($_POST['load'])) {
    $students = $conn->query("SELECT * FROM students WHERE department='$class'"); // or use actual section filter
    if ($students->num_rows > 0) {
        echo "<form method='post'><input type='hidden' name='hour' value='$hour'>
              <input type='hidden' name='class' value='$class'>
              <table border='1'><tr><th>Name</th><th>Present</th><th>Absent</th></tr>";

        while ($s = $students->fetch_assoc()) {
            echo "<tr>
                <td>{$s['name']}</td>
                <td><input type='radio' name='attendance[{$s['id']}]' value='P' checked></td>
                <td><input type='radio' name='attendance[{$s['id']}]' value='A'></td>
            </tr>";
        }

        echo "</table><button type='submit' name='submit'>Save Attendance</button></form>";
    } else {
        echo "<p>No students found for this class.</p>";
    }
}
?>
