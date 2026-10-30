<?php

session_start();

// Login check
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Only employee can access
if ($_SESSION["role"] !== "employee") {
    header("Location: dashboard.php");
    exit();
}

// Database connection
require_once __DIR__ . '/database.php';

// Logged-in employee name
$employeeName = trim($_SESSION["name"] ?? "");

// Find employee using name
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

if (!$employeeStatement) {
die("Employee SQL Error: " . mysqli_error($conn));
}


mysqli_stmt_bind_param(
    $employeeStatement,
    "s",
    $employeeName
);

mysqli_stmt_execute(
    $employeeStatement
);

$employeeResult = mysqli_stmt_get_result(
    $employeeStatement
);

$employee = mysqli_fetch_assoc(
    $employeeResult
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Tasks</title>

    <!-- Bootstrap CSS -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

<style>
.hrtech-navbar-actions{flex-wrap:nowrap!important;white-space:nowrap}
.hrtech-navbar-actions .btn{display:inline-flex;align-items:center;justify-content:center;white-space:nowrap;font-weight:600}
@media(max-width:520px){.hrtech-navbar-actions{gap:4px!important}.hrtech-navbar-actions .btn{font-size:11px;padding:6px 7px}.navbar-brand{font-size:17px}}
</style>
</head>

<body class="bg-light">


<!-- Navbar -->

<nav class="navbar navbar-dark bg-primary shadow-sm">
  <div class="container d-flex align-items-center flex-nowrap gap-2">
    <a
            class="navbar-brand fw-bold"
            href="dashboard.php"
        >

            HRTech

        </a>
    <div class="hrtech-navbar-actions d-flex align-items-center gap-2 ms-auto flex-nowrap">
      <a href="dashboard.php" class="btn btn-outline-light btn-sm">← Back</a>
      <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </div>
</nav>


<!-- Main Content -->

<div class="container mt-5">

    <h2>My Tasks</h2>

    <p class="text-muted">

        Welcome,

        <strong>

            <?php

            echo htmlspecialchars(
                $_SESSION["name"]
            );

            ?>

        </strong>

    </p>


    <!-- Success Message -->

    <?php if (isset($_GET["success"])) { ?>

        <div class="alert alert-success">

            Work submitted successfully.
            Task status is now Completed.

        </div>

    <?php } ?>


    <!-- Empty Work Error -->

    <?php

    if (
        isset($_GET["error"])
        &&
        $_GET["error"] === "empty"
    ) {

    ?>

        <div class="alert alert-danger">

            Please enter your work before submitting.

        </div>

    <?php } ?>


    <?php if (!$employee) { ?>

        <div class="alert alert-warning">

            Your employee profile was not found.
            Please contact HR.

        </div>

    <?php } else { ?>


        <?php

        // Get assigned tasks

        $taskQuery = "
            SELECT *
            FROM onboarding_tasks
            WHERE employee_id = ?
            ORDER BY id DESC
        ";

        $taskStatement = mysqli_prepare(
            $conn,
            $taskQuery
        );

        mysqli_stmt_bind_param(
            $taskStatement,
            "i",
            $employee["id"]
        );

        mysqli_stmt_execute(
            $taskStatement
        );

        $taskResult = mysqli_stmt_get_result(
            $taskStatement
        );

        ?>


        <div class="card shadow border-0">

            <div class="card-body">

                <h4 class="mb-3">

                    Assigned Tasks

                </h4>


                <div class="table-responsive">

                    <table class="table table-bordered align-middle">

                        <thead class="table-primary">

                            <tr>

                                <th>Task</th>

                                <th>Description</th>

                                <th>Due Date</th>

                                <th>Status</th>

                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php

                        if (
                            mysqli_num_rows(
                                $taskResult
                            ) > 0
                        ) {

                            while (
                                $task = mysqli_fetch_assoc(
                                    $taskResult
                                )
                            ) {

                                $taskId =
                                    $task["id"];

                        ?>


                            <tr>

                                <!-- Task Title -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $task["task_title"]
                                    );

                                    ?>

                                </td>


                                <!-- Task Description -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $task[
                                            "task_description"
                                        ]
                                    );

                                    ?>

                                </td>


                                <!-- Due Date -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $task["due_date"]
                                    );

                                    ?>

                                </td>


                                <!-- Status -->

                                <td>

                                    <?php

                                    if (
                                        $task["status"]
                                        === "Completed"
                                    ) {

                                    ?>

                                        <span class="badge bg-success">

                                            Completed

                                        </span>

                                    <?php

                                    } elseif (
                                        $task["status"]
                                        === "In Progress"
                                    ) {

                                    ?>

                                        <span class="badge bg-primary">

                                            In Progress

                                        </span>

                                    <?php

                                    } else {

                                    ?>

                                        <span
                                            class="
                                            badge
                                            bg-warning
                                            text-dark
                                            "
                                        >

                                            Pending

                                        </span>

                                    <?php

                                    }

                                    ?>

                                </td>


                                <!-- Action -->

                                <td>

                                    <?php

                                    if (
                                        $task["status"]
                                        !== "Completed"
                                    ) {

                                    ?>

                                        <button
                                            type="button"
                                            class="
                                            btn
                                            btn-primary
                                            btn-sm
                                            "
                                            data-bs-toggle="modal"
                                            data-bs-target="#submitModal<?php echo $taskId; ?>"
                                        >

                                            Submit Work

                                        </button>

                                    <?php

                                    } else {

                                    ?>

                                        <span
                                            class="
                                            text-success
                                            fw-bold
                                            "
                                        >

                                            Work Submitted

                                        </span>

                                    <?php

                                    }

                                    ?>

                                </td>

                            </tr>


                            <!-- Submit Work Modal -->

                            <div
                                class="modal fade"
                                id="submitModal<?php echo $taskId; ?>"
                                tabindex="-1"
                                aria-hidden="true"
                            >

                                <div class="modal-dialog">

                                    <div class="modal-content">

                                        <form
                                            action="submit_work.php"
                                            method="POST"
                                        >

                                            <div class="modal-header">

                                                <h5 class="modal-title">

                                                    Submit Work

                                                </h5>

                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"
                                                ></button>

                                            </div>


                                            <div class="modal-body">

                                                <input
                                                    type="hidden"
                                                    name="task_id"
                                                    value="<?php echo $taskId; ?>"
                                                >


                                                <label
                                                    class="form-label"
                                                >

                                                    Enter your work

                                                </label>


                                                <textarea
                                                    name="submission_text"
                                                    class="form-control"
                                                    rows="6"
                                                    placeholder="Write your completed work here..."
                                                    required
                                                ></textarea>

                                            </div>


                                            <div class="modal-footer">

                                                <button
                                                    type="button"
                                                    class="
                                                    btn
                                                    btn-secondary
                                                    "
                                                    data-bs-dismiss="modal"
                                                >

                                                    Cancel

                                                </button>


                                                <button
                                                    type="submit"
                                                    class="
                                                    btn
                                                    btn-success
                                                    "
                                                >

                                                    Submit Work

                                                </button>

                                            </div>

                                        </form>

                                    </div>

                                </div>

                            </div>


                        <?php

                            }

                        } else {

                        ?>


                            <tr>

                                <td
                                    colspan="5"
                                    class="text-center"
                                >

                                    No tasks assigned.

                                </td>

                            </tr>


                        <?php

                        }

                        ?>


                        </tbody>

                    </table>

                </div>

            </div>

        </div>


    <?php } ?>


</div>


<!-- Bootstrap JavaScript -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>
<link
    rel="stylesheet"
    href="css/style.css"
>

<style id="hrtech-logout-modal-styles">
.hrtech-modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.58);display:none;align-items:center;justify-content:center;padding:20px;z-index:99999}
.hrtech-modal-overlay.is-open{display:flex}
.hrtech-modal{width:min(420px,100%);background:#fff;border-radius:18px;padding:26px;box-shadow:0 24px 70px rgba(0,0,0,.28);font-family:Arial,sans-serif;color:#1f2937}
.hrtech-modal h2{font-size:21px;margin:0 0 10px;font-weight:700}
.hrtech-modal p{font-size:15px;line-height:1.55;color:#64748b;margin:0 0 22px}
.hrtech-modal-actions{display:flex;justify-content:flex-end;gap:10px}
.hrtech-modal-actions button{border:0;border-radius:9px;padding:10px 18px;font-weight:700;cursor:pointer;font-size:14px}
.hrtech-modal-no{background:#eef2f7;color:#334155}
.hrtech-modal-yes{background:#dc3545;color:#fff}
.hrtech-modal-yes:hover{background:#bb2d3b}.hrtech-modal-no:hover{background:#e2e8f0}
body.hrtech-modal-active{overflow:hidden}
</style>
<div class="hrtech-modal-overlay" id="hrtechLogoutModal" role="dialog" aria-modal="true" aria-labelledby="hrtechLogoutTitle" aria-describedby="hrtechLogoutMessage">
 <div class="hrtech-modal">
  <h2 id="hrtechLogoutTitle">Log out?</h2>
  <p id="hrtechLogoutMessage">Are you sure you want to log out of HRTech?</p>
  <div class="hrtech-modal-actions">
   <button type="button" class="hrtech-modal-no" id="hrtechLogoutNo">No, stay</button>
   <button type="button" class="hrtech-modal-yes" id="hrtechLogoutYes">Yes, log out</button>
  </div>
 </div>
</div>
<script id="hrtech-logout-modal-script">
(function(){
 const overlay=document.getElementById('hrtechLogoutModal');
 if(!overlay) return;
 let logoutUrl='';
 function closeModal(){overlay.classList.remove('is-open');document.body.classList.remove('hrtech-modal-active');logoutUrl='';}
 document.addEventListener('click',function(event){
   const link=event.target.closest('a[href]');
   if(!link) return;
   let target;
   try{target=new URL(link.href,window.location.href);}catch(e){return;}
   if(!target.pathname.toLowerCase().endsWith('/logout.php') && target.pathname.toLowerCase()!=='logout.php') return;
   event.preventDefault();logoutUrl=link.href;overlay.classList.add('is-open');document.body.classList.add('hrtech-modal-active');document.getElementById('hrtechLogoutNo').focus();
 });
 document.getElementById('hrtechLogoutNo').addEventListener('click',closeModal);
 document.getElementById('hrtechLogoutYes').addEventListener('click',function(){if(logoutUrl) window.location.href=logoutUrl;});
 overlay.addEventListener('click',function(event){if(event.target===overlay) closeModal();});
 document.addEventListener('keydown',function(event){if(event.key==='Escape' && overlay.classList.contains('is-open')) closeModal();});
})();
</script>

</body>

</html>