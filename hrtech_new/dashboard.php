<?php

session_start();

// =====================================
// Login Check
// =====================================

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// =====================================
// Database Connection
// =====================================

require_once __DIR__ . '/database.php';

// =====================================
// Default Counts
// =====================================

$totalEmployees = 0;
$pendingTasks = 0;
$completedTasks = 0;
$performanceReviews = 0;

$totalGoals = 0;
$inProgressGoals = 0;
$completedGoals = 0;

$role = $_SESSION["role"] ?? "";

// =====================================
// EMPLOYEE DASHBOARD
// =====================================

if ($role === "employee") {

    $employeeName = trim($_SESSION["name"] ?? "");

    // Find Employee
    $employeeQuery = "
        SELECT id, full_name
        FROM employees
        WHERE LOWER(TRIM(full_name)) = LOWER(TRIM(?))
        LIMIT 1
    ";

    $employeeStatement = mysqli_prepare(
        $conn,
        $employeeQuery
    );

    $employee = null;

    if ($employeeStatement) {

        mysqli_stmt_bind_param(
            $employeeStatement,
            "s",
            $employeeName
        );

        mysqli_stmt_execute($employeeStatement);

        $employeeResult = mysqli_stmt_get_result(
            $employeeStatement
        );

        $employee = mysqli_fetch_assoc(
            $employeeResult
        );

        mysqli_stmt_close($employeeStatement);
    }

    // =====================================
    // Employee Goals Summary
    // =====================================

    if ($employee) {

        $employeeId = (int)$employee["id"];

        // Total Goals

        $stmt = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS total
             FROM employee_goals
             WHERE employee_id = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $employeeId
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $data = mysqli_fetch_assoc($result);

            $totalGoals = (int)($data["total"] ?? 0);

            mysqli_stmt_close($stmt);
        }

        // Goals In Progress

        $stmt = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS total
             FROM employee_goals
             WHERE employee_id = ?
             AND TRIM(status) = 'In Progress'"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $employeeId
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $data = mysqli_fetch_assoc($result);

            $inProgressGoals = (int)($data["total"] ?? 0);

            mysqli_stmt_close($stmt);
        }

        // Completed Goals

        $stmt = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS total
             FROM employee_goals
             WHERE employee_id = ?
             AND TRIM(status) = 'Completed'"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $employeeId
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $data = mysqli_fetch_assoc($result);

            $completedGoals = (int)($data["total"] ?? 0);

            mysqli_stmt_close($stmt);
        }
    }

}

// =====================================
// ADMIN / MANAGER DASHBOARD
// =====================================

else {

    // Total Employees

    $result = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM employees"
    );

    if ($result) {

        $data = mysqli_fetch_assoc($result);

        $totalEmployees = (int)$data["total"];
    }

    // Pending Tasks

    $result = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM onboarding_tasks
         WHERE status = 'Pending'"
    );

    if ($result) {

        $data = mysqli_fetch_assoc($result);

        $pendingTasks = (int)$data["total"];
    }

    // Completed Tasks

    $result = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM onboarding_tasks
         WHERE status = 'Completed'"
    );

    if ($result) {

        $data = mysqli_fetch_assoc($result);

        $completedTasks = (int)$data["total"];
    }

    // Performance Reviews

    $result = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM performance_reviews"
    );

    if ($result) {

        $data = mysqli_fetch_assoc($result);

        $performanceReviews = (int)$data["total"];
    }

    // Organisation-wide Goals

    $result = mysqli_query(
        $conn,
        "SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN TRIM(status) = 'In Progress'
                THEN 1 ELSE 0 END) AS in_progress,
            SUM(CASE WHEN TRIM(status) = 'Completed'
                THEN 1 ELSE 0 END) AS completed
         FROM employee_goals"
    );

    if ($result) {

        $data = mysqli_fetch_assoc($result);

        $totalGoals = (int)($data["total"] ?? 0);

        $inProgressGoals = (int)($data["in_progress"] ?? 0);

        $completedGoals = (int)($data["completed"] ?? 0);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>HRTech Dashboard</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="css/style.css">

</head>

<body class="bg-light">

<!-- =====================================
     NAVBAR
===================================== -->

<nav class="navbar navbar-dark bg-primary shadow-sm">

    <div class="container">

        <a class="navbar-brand fw-bold"
           href="dashboard.php">
            HRTech
        </a>

        <div class="d-flex align-items-center gap-3">

            <div class="d-flex align-items-center">

                <div class="profile-avatar"
                     title="<?php echo htmlspecialchars($_SESSION["name"]); ?>">

                    <?php
                    echo strtoupper(
                        substr($_SESSION["name"], 0, 1)
                    );
                    ?>

                </div>

                <div class="ms-2 text-white d-none d-md-block">

                    <div class="fw-bold">
                        <?php
                        echo htmlspecialchars($_SESSION["name"]);
                        ?>
                    </div>

                    <small class="text-white-50">
                        <?php
                        echo ucfirst($_SESSION["role"]);
                        ?>
                    </small>

                </div>

            </div>

            <a href="logout.php"
               class="btn btn-light btn-sm px-3 py-1 ms-4">
                Logout
            </a>

        </div>

    </div>

</nav>


<!-- =====================================
     MAIN DASHBOARD
===================================== -->

<div class="container mt-5">

    <h2>
        Welcome,
        <?php
        echo htmlspecialchars(
            strtoupper(trim($_SESSION["name"]))
        );
        ?>
    </h2>

    <p class="text-muted">
        Your Role:
        <strong>
            <?php
            echo htmlspecialchars($_SESSION["role"]);
            ?>
        </strong>
    </p>


    <!-- =====================================
         MAIN CARDS
         ADMIN AND MANAGER ONLY
    ====================================== -->

    <?php if ($role !== "employee") { ?>

        <div class="row mt-4">

            <!-- Total Employees -->

            <div class="col-md-3 mb-3">

                <a class="dashboard-summary-link"
                   href="employees.php"
                   aria-label="Open Total Employees details">

                    <div class="card shadow border-0">

                        <div class="card-body">

                            <h5>Total Employees</h5>

                            <h2>
                                <?php echo $totalEmployees; ?>
                            </h2>

                        </div>

                    </div>

                </a>

            </div>


            <!-- Pending Tasks -->

            <?php if ($role !== "manager") { ?>

                <div class="col-md-3 mb-3">

                    <a class="dashboard-summary-link"
                       href="employee_tasks.php">

                        <div class="card shadow border-0">

                            <div class="card-body">

                                <h5>Pending Tasks</h5>

                                <h2>
                                    <?php echo $pendingTasks; ?>
                                </h2>

                            </div>

                        </div>

                    </a>

                </div>


                <!-- Completed Tasks -->

                <div class="col-md-3 mb-3">

                    <a class="dashboard-summary-link"
                       href="employee_tasks.php">

                        <div class="card shadow border-0">

                            <div class="card-body">

                                <h5>Completed Tasks</h5>

                                <h2>
                                    <?php echo $completedTasks; ?>
                                </h2>

                            </div>

                        </div>

                    </a>

                </div>

            <?php } ?>


            <!-- Performance Reviews -->

            <div class="col-md-3 mb-3">

                <a class="dashboard-summary-link"
                   href="performance.php">

                    <div class="card shadow border-0">

                        <div class="card-body">

                            <h5>Performance Reviews</h5>

                            <h2>
                                <?php echo $performanceReviews; ?>
                            </h2>

                        </div>

                    </div>

                </a>

            </div>

        </div>

    <?php } ?>


    <!-- =====================================
         EMPLOYEE GOALS SUMMARY
    ====================================== -->

    <?php if ($role === "employee") { ?>

        <h4 class="mt-3">
            My Goals Summary
        </h4>

        <div class="row mt-2">

            <!-- Total Goals -->

            <div class="col-md-4 mb-3">

                <a class="dashboard-summary-link"
                   href="my_goals.php?filter=all">

                    <div class="card shadow border-0">

                        <div class="card-body">

                            <h5>Total Goals</h5>

                            <h2>
                                <?php echo $totalGoals; ?>
                            </h2>

                        </div>

                    </div>

                </a>

            </div>


            <!-- Goals In Progress -->

            <div class="col-md-4 mb-3">

                <a class="dashboard-summary-link"
                   href="my_goals.php?filter=progress">

                    <div class="card shadow border-0">

                        <div class="card-body">

                            <h5>Goals In Progress</h5>

                            <h2>
                                <?php echo $inProgressGoals; ?>
                            </h2>

                        </div>

                    </div>

                </a>

            </div>


            <!-- Completed Goals -->

            <div class="col-md-4 mb-3">

                <a class="dashboard-summary-link"
                   href="my_goals.php?filter=completed">

                    <div class="card shadow border-0">

                        <div class="card-body">

                            <h5>Completed Goals</h5>

                            <h2>
                                <?php echo $completedGoals; ?>
                            </h2>

                        </div>

                    </div>

                </a>

            </div>

        </div>

    <?php } else { ?>


        <!-- =====================================
             ADMIN / MANAGER GOALS SUMMARY
        ====================================== -->

        <h4 class="mt-4">
            Goals Summary
        </h4>

        <p class="text-muted mb-2">
            Organisation-wide goal status. Select any card to view the related goal records.
        </p>

        <div class="row mt-2">

            <div class="col-md-4 mb-3">

                <a class="dashboard-summary-link"
                   href="goal_summary.php?filter=all">

                    <div class="card shadow border-0">

                        <div class="card-body">

                            <h5>Total Goals</h5>

                            <h2>
                                <?php echo $totalGoals; ?>
                            </h2>

                            <small class="text-muted">
                                View all employee goals
                            </small>

                        </div>

                    </div>

                </a>

            </div>


            <div class="col-md-4 mb-3">

                <a class="dashboard-summary-link"
                   href="goal_summary.php?filter=progress">

                    <div class="card shadow border-0">

                        <div class="card-body">

                            <h5>Goals In Progress</h5>

                            <h2>
                                <?php echo $inProgressGoals; ?>
                            </h2>

                            <small class="text-muted">
                                View active goals
                            </small>

                        </div>

                    </div>

                </a>

            </div>


            <div class="col-md-4 mb-3">

                <a class="dashboard-summary-link"
                   href="goal_summary.php?filter=completed">

                    <div class="card shadow border-0">

                        <div class="card-body">

                            <h5>Completed Goals</h5>

                            <h2>
                                <?php echo $completedGoals; ?>
                            </h2>

                            <small class="text-muted">
                                View completed goals
                            </small>

                        </div>

                    </div>

                </a>

            </div>

        </div>

    <?php } ?>


    <!-- =====================================
         QUICK ACTIONS
    ====================================== -->

    <div class="card shadow border-0 mt-4">

        <div class="card-body">

            <h4 class="mb-3">
                Quick Actions
            </h4>


            <!-- ADMIN -->

            <?php if ($role === "admin") { ?>

                <a href="employees.php"
                   class="btn btn-primary me-2 mb-2">
                    Add Employee
                </a>

                <a href="employee_tasks.php"
                   class="btn btn-success me-2 mb-2">
                    Assign Employee Task
                </a>

                <a href="performance.php"
                   class="btn btn-warning me-2 mb-2">
                    Performance Review
                </a>

                <a href="modules/manage_leave.php"
                   class="btn btn-danger me-2 mb-2">
                    Manage Leave Requests
                </a>

                <a href="modules/documents.php"
                   class="btn btn-outline-primary me-2 mb-2">
                    Document Center
                </a>

            <?php } ?>


            <!-- MANAGER -->

            <?php if ($role === "manager") { ?>

                <a href="modules/manage_team.php"
                   class="btn btn-success me-2 mb-2">
                    Manage Team
                </a>

                <a href="performance.php"
                   class="btn btn-warning me-2 mb-2">
                    Performance Review
                </a>

                <a href="modules/manage_leave.php"
                   class="btn btn-danger me-2 mb-2">
                    Manage Leave Requests
                </a>

            <?php } ?>


            <!-- EMPLOYEE -->

            <?php if ($role === "employee") { ?>

                <a href="my_tasks.php"
                   class="btn btn-primary me-2 mb-2">
                    My Tasks
                </a>

                <a href="my_goals.php"
                   class="btn btn-success me-2 mb-2">
                    My Goals
                </a>

                <a href="my_performance.php"
                   class="btn btn-warning me-2 mb-2">
                    My Performance
                </a>

                <a href="modules/leave_request.php"
                   class="btn btn-danger me-2 mb-2">
                    Apply Leave
                </a>

                <a href="documents.php"
                   class="btn btn-outline-primary me-2 mb-2">
                    Document Center
                </a>

            <?php } ?>

        </div>

    </div>

</div>


<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<!-- =====================================
     LOGOUT CONFIRMATION MODAL
===================================== -->

<style id="hrtech-logout-modal-styles">

.hrtech-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,.58);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 99999;
}

.hrtech-modal-overlay.is-open {
    display: flex;
}

.hrtech-modal {
    width: min(420px,100%);
    background: #fff;
    border-radius: 18px;
    padding: 26px;
    box-shadow: 0 24px 70px rgba(0,0,0,.28);
    font-family: Arial,sans-serif;
    color: #1f2937;
}

.hrtech-modal h2 {
    font-size: 21px;
    margin: 0 0 10px;
    font-weight: 700;
}

.hrtech-modal p {
    font-size: 15px;
    line-height: 1.55;
    color: #64748b;
    margin: 0 0 22px;
}

.hrtech-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.hrtech-modal-actions button {
    border: 0;
    border-radius: 9px;
    padding: 10px 18px;
    font-weight: 700;
    cursor: pointer;
    font-size: 14px;
}

.hrtech-modal-no {
    background: #eef2f7;
    color: #334155;
}

.hrtech-modal-yes {
    background: #dc3545;
    color: #fff;
}

.hrtech-modal-yes:hover {
    background: #bb2d3b;
}

.hrtech-modal-no:hover {
    background: #e2e8f0;
}

body.hrtech-modal-active {
    overflow: hidden;
}

</style>


<div class="hrtech-modal-overlay"
     id="hrtechLogoutModal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="hrtechLogoutTitle"
     aria-describedby="hrtechLogoutMessage">

    <div class="hrtech-modal">

        <h2 id="hrtechLogoutTitle">
            Log out?
        </h2>

        <p id="hrtechLogoutMessage">
            Are you sure you want to log out of HRTech?
        </p>

        <div class="hrtech-modal-actions">

            <button type="button"
                    class="hrtech-modal-no"
                    id="hrtechLogoutNo">
                No, stay
            </button>

            <button type="button"
                    class="hrtech-modal-yes"
                    id="hrtechLogoutYes">
                Yes, log out
            </button>

        </div>

    </div>

</div>


<script id="hrtech-logout-modal-script">

(function() {

    const overlay = document.getElementById(
        'hrtechLogoutModal'
    );

    if (!overlay) return;

    let logoutUrl = '';

    function closeModal() {

        overlay.classList.remove('is-open');

        document.body.classList.remove(
            'hrtech-modal-active'
        );

        logoutUrl = '';
    }

    document.addEventListener('click', function(event) {

        const link = event.target.closest('a[href]');

        if (!link) return;

        let target;

        try {
            target = new URL(
                link.href,
                window.location.href
            );
        } catch(e) {
            return;
        }

        if (
            !target.pathname.toLowerCase().endsWith('/logout.php') &&
            target.pathname.toLowerCase() !== 'logout.php'
        ) {
            return;
        }

        event.preventDefault();

        logoutUrl = link.href;

        overlay.classList.add('is-open');

        document.body.classList.add(
            'hrtech-modal-active'
        );

        document.getElementById(
            'hrtechLogoutNo'
        ).focus();

    });

    document.getElementById(
        'hrtechLogoutNo'
    ).addEventListener('click', closeModal);

    document.getElementById(
        'hrtechLogoutYes'
    ).addEventListener('click', function() {

        if (logoutUrl) {
            window.location.href = logoutUrl;
        }

    });

    overlay.addEventListener('click', function(event) {

        if (event.target === overlay) {
            closeModal();
        }

    });

    document.addEventListener('keydown', function(event) {

        if (
            event.key === 'Escape' &&
            overlay.classList.contains('is-open')
        ) {
            closeModal();
        }

    });

})();

</script>

</body>
</html>