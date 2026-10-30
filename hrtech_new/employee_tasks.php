<?php

session_start();


// Login check

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");

    exit();

}


// =====================================
// Admin Access Check
// =====================================

if (
    !isset($_SESSION["role"]) ||
    strtolower(trim($_SESSION["role"])) !== "admin"
) {

    die(
        "Access denied. Only HR Admin can assign employee tasks."
    );

}


// =====================================
// Database Connection
// =====================================

require_once __DIR__ . '/database.php';


$message = "";

$error = "";


// =====================================
// Assign Employee Task
// =====================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $employeeId =
        isset($_POST["employee_id"])
        ? (int) $_POST["employee_id"]
        : 0;


    $taskTitle =
        isset($_POST["task_title"])
        ? trim($_POST["task_title"])
        : "";


    $taskDescription =
        isset($_POST["task_description"])
        ? trim($_POST["task_description"])
        : "";


    $dueDate =
        isset($_POST["due_date"])
        ? trim($_POST["due_date"])
        : "";


    // Validation

    if (
        $employeeId <= 0 ||
        $taskTitle === ""
    ) {

        $error =
            "Please select an employee and enter a task title.";

    } else {

        // Insert task

        $insertQuery = "
            INSERT INTO onboarding_tasks
            (
                employee_id,
                task_title,
                task_description,
                due_date,
                status
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                'Pending'
            )
        ";


        $insertStatement = mysqli_prepare(
            $conn,
            $insertQuery
        );


        if ($insertStatement) {

            mysqli_stmt_bind_param(
                $insertStatement,
                "isss",
                $employeeId,
                $taskTitle,
                $taskDescription,
                $dueDate
            );


            if (
                mysqli_stmt_execute(
                    $insertStatement
                )
            ) {

                $message =
                    "Employee task assigned successfully.";

            } else {

                $error =
                    "Task could not be assigned.";

            }

        } else {

            $error =
                "Database query preparation failed.";

        }

    }

}


// =====================================
// Get All Employees
// =====================================

$employeeQuery = "
    SELECT
        id,
        employee_id,
        full_name,
        department
    FROM employees
    ORDER BY full_name ASC
";


$employeeResult = mysqli_query(
    $conn,
    $employeeQuery
);


// =====================================
// Get Assigned Tasks
// =====================================

$taskQuery = "
    SELECT
        onboarding_tasks.id,
        onboarding_tasks.task_title,
        onboarding_tasks.task_description,
        onboarding_tasks.due_date,
        onboarding_tasks.status,
        employees.full_name,
        employees.employee_id
    FROM onboarding_tasks

    INNER JOIN employees

    ON onboarding_tasks.employee_id
    =
    employees.id

    ORDER BY onboarding_tasks.id DESC
";


$taskResult = mysqli_query(
    $conn,
    $taskQuery
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

    <title>

        Assign Employee Task

    </title>


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


<!-- =====================================
Navbar
===================================== -->

<nav class="navbar navbar-dark bg-primary shadow-sm">
  <div class="container d-flex align-items-center flex-nowrap gap-2">
    <a
            href="dashboard.php"
            class="navbar-brand fw-bold"
        >

            HRTech

        </a>
    <div class="hrtech-navbar-actions d-flex align-items-center gap-2 ms-auto flex-nowrap">
      <a href="dashboard.php" class="btn btn-outline-light btn-sm">← Back</a>
      <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </div>
</nav>


<!-- =====================================
Main Content
===================================== -->

<div class="container mt-5">


    <h2>

        Assign Employee Task

    </h2>


    <p class="text-muted">

        Assign tasks to employees.

    </p>


    <!-- Success Message -->

    <?php if ($message !== "") { ?>


        <div class="alert alert-success">

            <?php

            echo htmlspecialchars(
                $message
            );

            ?>

        </div>


    <?php } ?>


    <!-- Error Message -->

    <?php if ($error !== "") { ?>


        <div class="alert alert-danger">

            <?php

            echo htmlspecialchars(
                $error
            );

            ?>

        </div>


    <?php } ?>


    <!-- =====================================
    Assign Task Form
    ===================================== -->

    <div class="card shadow border-0 mb-5">

        <div class="card-body">


            <h4 class="mb-4">

                Create New Task

            </h4>


            <form method="POST">


                <!-- Employee -->

                <div class="mb-3">

                    <label class="form-label">

                        Select Employee

                    </label>


                    <select
                        name="employee_id"
                        class="form-select"
                        required
                    >

                        <option value="">

                            Select Employee

                        </option>


                        <?php

                        if (
                            $employeeResult &&
                            mysqli_num_rows(
                                $employeeResult
                            ) > 0
                        ) {

                            while (
                                $employee =
                                mysqli_fetch_assoc(
                                    $employeeResult
                                )
                            ) {

                        ?>


                            <option
                                value="<?php

                                echo
                                $employee["id"];

                                ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $employee["full_name"]
                                );

                                ?>

                                -

                                <?php

                                echo htmlspecialchars(
                                    $employee["employee_id"]
                                );

                                ?>

                            </option>


                        <?php

                            }

                        }

                        ?>


                    </select>

                </div>


                <!-- Task Title -->

                <div class="mb-3">

                    <label class="form-label">

                        Task Title

                    </label>


                    <input
                        type="text"
                        name="task_title"
                        class="form-control"
                        placeholder="Enter task title"
                        required
                    >

                </div>


                <!-- Description -->

                <div class="mb-3">

                    <label class="form-label">

                        Task Description

                    </label>


                    <textarea
                        name="task_description"
                        class="form-control"
                        rows="4"
                        placeholder="Enter task description"
                    ></textarea>

                </div>


                <!-- Due Date -->

                <div class="mb-3">

                    <label class="form-label">

                        Due Date

                    </label>


                    <input
                        type="date"
                        name="due_date"
                        class="form-control"
                    >

                </div>


                <!-- Submit -->

                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    Assign Task

                </button>


            </form>


        </div>

    </div>


    <!-- =====================================
    Assigned Tasks List
    ===================================== -->

    <div class="card shadow border-0">

        <div class="card-body">


            <h4 class="mb-4">

                Assigned Employee Tasks

            </h4>


            <div class="table-responsive">


                <table
                    class="table table-bordered table-hover"
                >


                    <thead class="table-primary">

                        <tr>

                            <th>

                                Employee

                            </th>

                            <th>

                                Employee ID

                            </th>

                            <th>

                                Task

                            </th>

                            <th>

                                Description

                            </th>

                            <th>

                                Due Date

                            </th>

                            <th>

                                Status

                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    if (
                        $taskResult &&
                        mysqli_num_rows(
                            $taskResult
                        ) > 0
                    ) {

                        while (
                            $task =
                            mysqli_fetch_assoc(
                                $taskResult
                            )
                        ) {

                    ?>


                        <tr>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $task["full_name"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $task["employee_id"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $task["task_title"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $task[
                                        "task_description"
                                    ] ?? ""
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $task[
                                        "due_date"
                                    ] ?? ""
                                );

                                ?>

                            </td>


                            <td>


                                <?php

                                $status =
                                $task["status"];


                                if (
                                    $status ===
                                    "Completed"
                                ) {

                                ?>


                                    <span
                                        class="
                                        badge
                                        bg-success
                                        "
                                    >

                                        Completed

                                    </span>


                                <?php

                                } elseif (
                                    $status ===
                                    "In Progress"
                                ) {

                                ?>


                                    <span
                                        class="
                                        badge
                                        bg-primary
                                        "
                                    >

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


                        </tr>


                    <?php

                        }

                    } else {

                    ?>


                        <tr>

                            <td
                                colspan="6"
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


</div>


<!-- Bootstrap JavaScript -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

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