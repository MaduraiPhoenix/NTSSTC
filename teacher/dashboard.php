<?php
include('../includes/session.php');
checkTeacherSession();
?>

<h2>Welcome, <?php echo $_SESSION['teacher_name']; ?>!</h2>

<ul>
    <li><a href="take_attendance.php">Take Attendance</a></li>
    <li><a href="logout.php">Logout</a></li>
</ul>
