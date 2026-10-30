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
// Only Employee Can Access
// =====================================

if (
    !isset($_SESSION["role"])
    ||
    $_SESSION["role"] !== "employee"
) {

    header("Location: dashboard.php");

    exit();
}


// =====================================
// Database Connection
// =====================================

require_once __DIR__ . "/../config/database.php";


// =====================================
// Show Message After Redirect
// =====================================

$message =
    $_SESSION["message"] ?? "";

$messageType =
    $_SESSION["messageType"] ?? "";


unset(
    $_SESSION["message"],
    $_SESSION["messageType"]
);


// =====================================
// Find Logged-in Employee
// =====================================

$employeeName =
    trim(
        $_SESSION["name"] ?? ""
    );


$employee = null;


$employeeQuery = "
    SELECT id, full_name
    FROM employees
    WHERE LOWER(TRIM(full_name))
    =
    LOWER(TRIM(?))
    LIMIT 1
";


$employeeStatement =
    mysqli_prepare(
        $conn,
        $employeeQuery
    );


if ($employeeStatement) {

    mysqli_stmt_bind_param(
        $employeeStatement,
        "s",
        $employeeName
    );


    mysqli_stmt_execute(
        $employeeStatement
    );


    $employeeResult =
        mysqli_stmt_get_result(
            $employeeStatement
        );


    $employee =
        mysqli_fetch_assoc(
            $employeeResult
        );

}


// =====================================
// Add New Goal
// =====================================

if (
    $_SERVER["REQUEST_METHOD"]
    ===
    "POST"
    &&
    isset($_POST["add_goal"])
) {

    if (!$employee) {

        $_SESSION["message"] =
            "Employee profile was not found.";

        $_SESSION["messageType"] =
            "danger";


        header(
            "Location: my_goals.php"
        );

        exit();

    }


    $goalTitle =
        trim(
            $_POST["goal_title"] ?? ""
        );


    $goalDescription =
        trim(
            $_POST["goal_description"] ?? ""
        );


    $targetDate =
        trim(
            $_POST["target_date"] ?? ""
        );


    // Validation

    if (
        $goalTitle === ""
        ||
        $targetDate === ""
    ) {

        $_SESSION["message"] =
            "Please enter goal title and target date.";

        $_SESSION["messageType"] =
            "danger";


        header(
            "Location: my_goals.php"
        );

        exit();

    }


    // Insert Goal

    $insertQuery = "
        INSERT INTO employee_goals
        (
            employee_id,
            goal_title,
            goal_description,
            target_date,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            'Not Started'
        )
    ";


    $insertStatement =
        mysqli_prepare(
            $conn,
            $insertQuery
        );


    if (!$insertStatement) {

        $_SESSION["message"] =
            "Database error: "
            .
            mysqli_error($conn);

        $_SESSION["messageType"] =
            "danger";


        header(
            "Location: my_goals.php"
        );

        exit();

    }


    mysqli_stmt_bind_param(
        $insertStatement,
        "isss",
        $employee["id"],
        $goalTitle,
        $goalDescription,
        $targetDate
    );


    if (
        mysqli_stmt_execute(
            $insertStatement
        )
    ) {

        $_SESSION["message"] =
            "Goal added successfully.";

        $_SESSION["messageType"] =
            "success";

    } else {

        $_SESSION["message"] =
            "Goal could not be added: "
            .
            mysqli_stmt_error(
                $insertStatement
            );

        $_SESSION["messageType"] =
            "danger";

    }


    // Redirect after POST

    header(
        "Location: my_goals.php"
    );

    exit();

}


// =====================================
// Update Goal Status
// =====================================

if (
    $_SERVER["REQUEST_METHOD"]
    ===
    "POST"
    &&
    isset($_POST["update_status"])
) {

    if (!$employee) {

        $_SESSION["message"] =
            "Employee profile was not found.";

        $_SESSION["messageType"] =
            "danger";


        header(
            "Location: my_goals.php"
        );

        exit();

    }


    $goalId =
        isset($_POST["goal_id"])
        ?
        (int) $_POST["goal_id"]
        :
        0;


    $goalStatus =
        trim(
            $_POST["status"] ?? ""
        );


    $allowedStatus = [

        "Not Started",

        "In Progress",

        "Completed"

    ];


    // Validate Goal ID

    if ($goalId <= 0) {

        $_SESSION["message"] =
            "Invalid goal ID.";

        $_SESSION["messageType"] =
            "danger";


        header(
            "Location: my_goals.php"
        );

        exit();

    }


    // Validate Status

    if (
        !in_array(
            $goalStatus,
            $allowedStatus,
            true
        )
    ) {

        $_SESSION["message"] =
            "Invalid goal status.";

        $_SESSION["messageType"] =
            "danger";


        header(
            "Location: my_goals.php"
        );

        exit();

    }


    // Update Goal

    $updateQuery = "
        UPDATE employee_goals
        SET status = ?
        WHERE id = ?
        AND employee_id = ?
    ";


    $updateStatement =
        mysqli_prepare(
            $conn,
            $updateQuery
        );


    if (!$updateStatement) {

        $_SESSION["message"] =
            "Database query error: "
            .
            mysqli_error($conn);

        $_SESSION["messageType"] =
            "danger";


        header(
            "Location: my_goals.php"
        );

        exit();

    }


    mysqli_stmt_bind_param(
        $updateStatement,
        "sii",
        $goalStatus,
        $goalId,
        $employee["id"]
    );


    if (
        mysqli_stmt_execute(
            $updateStatement
        )
    ) {

        if (
            mysqli_stmt_affected_rows(
                $updateStatement
            ) > 0
        ) {

            $_SESSION["message"] =
                "Goal status updated successfully.";

            $_SESSION["messageType"] =
                "success";

        } else {

            $_SESSION["message"] =
                "No change was made. This goal already has the selected status.";

            $_SESSION["messageType"] =
                "info";

        }

    } else {

        $_SESSION["message"] =
            "Goal update failed: "
            .
            mysqli_stmt_error(
                $updateStatement
            );

        $_SESSION["messageType"] =
            "danger";

    }


    // Redirect after POST

    header(
        "Location: my_goals.php"
    );

    exit();

}


// =====================================
// Get Employee Goals
// =====================================

$goalResult = null;


if ($employee) {

    $goalQuery = "
        SELECT *
        FROM employee_goals
        WHERE employee_id = ?
        ORDER BY id DESC
    ";


    $goalStatement =
        mysqli_prepare(
            $conn,
            $goalQuery
        );


    if ($goalStatement) {

        mysqli_stmt_bind_param(
            $goalStatement,
            "i",
            $employee["id"]
        );


        mysqli_stmt_execute(
            $goalStatement
        );


        $goalResult =
            mysqli_stmt_get_result(
                $goalStatement
            );

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Goals - HRTech</title>


    <!-- Bootstrap CSS -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Custom CSS -->

    <link
        rel="stylesheet"
        href="../css/style.css"
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
            href="../dashboard.php"
            class="navbar-brand fw-bold"
        >

            HRTech

        </a>
    <div class="hrtech-navbar-actions d-flex align-items-center gap-2 ms-auto flex-nowrap">
      <a href="../dashboard.php" class="btn btn-outline-light btn-sm">← Back</a>
      <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </div>
</nav>


<!-- Main Content -->

<div class="container mt-5">


    <h2>

        My Goals

    </h2>


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


    <!-- Success / Error Message -->

    <?php if ($message !== "") { ?>


        <div
            class="
            alert
            alert-<?php
            echo htmlspecialchars(
                $messageType
            );
            ?>
            alert-dismissible
            fade
            show
            "
        >


            <?php

            echo htmlspecialchars(
                $message
            );

            ?>


            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>


        </div>


    <?php } ?>


    <!-- Employee Not Found -->

    <?php if (!$employee) { ?>


        <div class="alert alert-warning">

            Your employee profile was not found.

            Please contact HR.

        </div>


    <?php } else { ?>


        <!-- Add Goal Form -->

        <div
            class="
            card
            shadow
            border-0
            mb-4
            "
        >


            <div class="card-body">


                <h4 class="mb-3">

                    Add New Goal

                </h4>


                <form method="POST">


                    <!-- Goal Title -->

                    <div class="mb-3">


                        <label class="form-label">

                            Goal Title

                        </label>


                        <input
                            type="text"
                            name="goal_title"
                            class="form-control"
                            placeholder="Example: Complete Java Course"
                            required
                        >


                    </div>


                    <!-- Goal Description -->

                    <div class="mb-3">


                        <label class="form-label">

                            Goal Description

                        </label>


                        <textarea
                            name="goal_description"
                            class="form-control"
                            rows="4"
                            placeholder="Write your goal details..."
                        ></textarea>


                    </div>


                    <!-- Target Date -->

                    <div class="mb-3">


                        <label class="form-label">

                            Target Date

                        </label>


                        <input
                            type="date"
                            name="target_date"
                            class="form-control"
                            required
                        >


                    </div>


                    <!-- Add Button -->

                    <button
                        type="submit"
                        name="add_goal"
                        value="1"
                        class="btn btn-success"
                    >

                        Add Goal

                    </button>


                </form>


            </div>

        </div>


        <!-- Goal List -->

        <div
            class="
            card
            shadow
            border-0
            "
        >


            <div class="card-body">


                <h4 class="mb-3">

                    My Goal List

                </h4>


                <div class="table-responsive">


                    <table
                        class="
                        table
                        table-bordered
                        align-middle
                        "
                    >


                        <thead class="table-primary">


                            <tr>

                                <th>Goal</th>

                                <th>Description</th>

                                <th>Target Date</th>

                                <th>Status</th>

                                <th>Update</th>

                            </tr>


                        </thead>


                        <tbody>


                        <?php

                        if (
                            $goalResult
                            &&
                            mysqli_num_rows(
                                $goalResult
                            ) > 0
                        ) {

                            while (
                                $goal =
                                mysqli_fetch_assoc(
                                    $goalResult
                                )
                            ) {

                                $status =
                                    trim(
                                        $goal["status"]
                                    );


                                if (
                                    $status
                                    ===
                                    "Completed"
                                ) {

                                    $badge =
                                        "success";

                                } elseif (
                                    $status
                                    ===
                                    "In Progress"
                                ) {

                                    $badge =
                                        "primary";

                                } else {

                                    $badge =
                                        "secondary";

                                }

                        ?>


                            <tr>


                                <!-- Goal -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $goal[
                                            "goal_title"
                                        ]
                                    );

                                    ?>

                                </td>


                                <!-- Description -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $goal[
                                            "goal_description"
                                        ]
                                    );

                                    ?>

                                </td>


                                <!-- Target Date -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $goal[
                                            "target_date"
                                        ]
                                    );

                                    ?>

                                </td>


                                <!-- Status -->

                                <td>


                                    <span
                                        class="
                                        badge
                                        bg-<?php
                                        echo $badge;
                                        ?>
                                        "
                                    >


                                        <?php

                                        echo htmlspecialchars(
                                            $status
                                        );

                                        ?>


                                    </span>


                                </td>


                                <!-- Update -->

                                <td>


                                    <form
                                        method="POST"
                                        class="d-flex gap-2"
                                    >


                                        <input
                                            type="hidden"
                                            name="goal_id"
                                            value="<?php
                                            echo
                                            (int)
                                            $goal["id"];
                                            ?>"
                                        >


                                        <select
                                            name="status"
                                            class="
                                            form-select
                                            form-select-sm
                                            "
                                        >


                                            <option
                                                value="Not Started"
                                                <?php

                                                if (
                                                    $status
                                                    ===
                                                    "Not Started"
                                                ) {

                                                    echo
                                                    "selected";

                                                }

                                                ?>
                                            >

                                                Not Started

                                            </option>


                                            <option
                                                value="In Progress"
                                                <?php

                                                if (
                                                    $status
                                                    ===
                                                    "In Progress"
                                                ) {

                                                    echo
                                                    "selected";

                                                }

                                                ?>
                                            >

                                                In Progress

                                            </option>


                                            <option
                                                value="Completed"
                                                <?php

                                                if (
                                                    $status
                                                    ===
                                                    "Completed"
                                                ) {

                                                    echo
                                                    "selected";

                                                }

                                                ?>
                                            >

                                                Completed

                                            </option>


                                        </select>


                                        <button
                                            type="submit"
                                            name="update_status"
                                            value="1"
                                            class="
                                            btn
                                            btn-primary
                                            btn-sm
                                            "
                                        >

                                            Save

                                        </button>


                                    </form>


                                </td>


                            </tr>


                        <?php

                            }

                        } else {

                        ?>


                            <tr>


                                <td
                                    colspan="5"
                                    class="
                                    text-center
                                    text-muted
                                    "
                                >

                                    No goals added yet.

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