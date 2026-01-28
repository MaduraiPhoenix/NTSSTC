<?php
include('../includes/session.php');
checkAdminSession();
?>

<?php include('../includes/header.php'); ?>
<?php include('../includes/sidebar.php'); ?>

<div class="container-fluid">
    <h3 class="mb-4">Admin Dashboard</h3>

    <div class="row">
        <div class="col-md-3">
            <a href="manage_students.php" class="text-decoration-none">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body text-center">
                        <i class="bi bi-people fs-2"></i>
                        <h5 class="card-title mt-2">Manage Students</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="manage_teachers.php" class="text-decoration-none">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body text-center">
                        <i class="bi bi-person-badge fs-2"></i>
                        <h5 class="card-title mt-2">Manage Teachers</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="manage_subjects.php" class="text-decoration-none">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body text-center">
                        <i class="bi bi-journal-bookmark fs-2"></i>
                        <h5 class="card-title mt-2">Manage Subjects</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="view_attendance_summary.php" class="text-decoration-none">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-check fs-2"></i>
                        <h5 class="card-title mt-2">View Attendance</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
    <a href="attendance_report.php" class="text-decoration-none">
        <div class="card text-white bg-primary">
            <div class="card-body text-center">
                <i class="bi bi-journal-text" style="font-size: 2rem;"></i>
                <h6 class="mt-2">Attendance Report</h6>
            </div>
        </div>
    </a>
</div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
