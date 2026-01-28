<?php
include('session.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-icon {
            font-size: 2rem;
            color: #fff;
            padding: 15px;
            border-radius: 10px;
        }
        .card-box {
            transition: 0.3s;
        }
        .card-box:hover {
            transform: scale(1.03);
        }
    </style>
</head>
<body>
<?php include('header.php'); ?>

<div class="container mt-4">
    <h4>👩‍🏫 Welcome, <?= $_SESSION['teacher_name'] ?></h4>

    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <a href="attendance.php" class="text-decoration-none">
                <div class="card card-box bg-primary text-white shadow">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 card-icon bg-dark">🕒</div>
                            <div>
                                <h5 class="mb-0">Mark Attendance</h5>
                                <small>Hour-wise entry</small>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4 mb-3">
            <a href="attendance_report.php" class="text-decoration-none">
                <div class="card card-box bg-success text-white shadow">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3 card-icon bg-dark">📄</div>
                            <div>
                                <h5 class="mb-0">My Attendance Report</h5>
                                <small>View full/half-day summary</small>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        

      

    </div>
</div>

<?php include('../includes/footer.php'); ?>
</body>
</html>
