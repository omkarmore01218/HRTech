<?php

session_start();


// =====================================
// Login Check
// =====================================

if (!isset($_SESSION["user_id"])) {

    header("Location: ../login.php");

    exit();

}


// =====================================
// Only Admin / Manager Can Access
// =====================================

$role = $_SESSION["role"] ?? "";

if (
    $role !== "admin"
    &&
    $role !== "manager"
) {

    header("Location: ../dashboard.php");

    exit();

}


// =====================================
// Database Connection
// =====================================

require_once __DIR__ . "/../config/database.php";


// =====================================
// Default Message
// =====================================

$message = "";

$messageType = "";


// =====================================
// Approve / Reject Leave
// =====================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST["update_leave"])
) {

    $leaveId = isset(
        $_POST["leave_id"]
    )
    ?
    (int) $_POST["leave_id"]
    :
    0;


    $status = trim(
        $_POST["status"] ?? ""
    );


    // Allowed Status

    $allowedStatus = [

        "Approved",

        "Rejected"

    ];


    if ($leaveId <= 0) {

        $message =
            "Invalid leave request.";

        $messageType =
            "danger";

    } elseif (

        !in_array(
            $status,
            $allowedStatus,
            true
        )

    ) {

        $message =
            "Invalid leave status.";

        $messageType =
            "danger";

    } else {

        $updateQuery = "

            UPDATE leave_requests

            SET status = ?

            WHERE id = ?

        ";


        $updateStatement = mysqli_prepare(

            $conn,

            $updateQuery

        );


        if (!$updateStatement) {

            $message =
                "Database error: "
                .
                mysqli_error($conn);

            $messageType =
                "danger";

        } else {

            mysqli_stmt_bind_param(

                $updateStatement,

                "si",

                $status,

                $leaveId

            );


            if (

                mysqli_stmt_execute(
                    $updateStatement
                )

            ) {

                $_SESSION[
                    "manage_leave_message"
                ] =

                "Leave request "
                .
                strtolower($status)
                .
                " successfully.";


                $_SESSION[
                    "manage_leave_type"
                ] =

                "success";


                // Reload same page

                header(

                    "Location: manage_leave.php"

                );


                exit();

            } else {

                $message =

                "Leave status could not be updated.";

                $messageType =

                "danger";

            }

        }

    }

}


// =====================================
// Show Session Message
// =====================================

if (

    isset(
        $_SESSION[
            "manage_leave_message"
        ]
    )

) {

    $message =

    $_SESSION[
        "manage_leave_message"
    ];


    $messageType =

    $_SESSION[
        "manage_leave_type"
    ]

    ??

    "success";


    unset(

        $_SESSION[
            "manage_leave_message"
        ],

        $_SESSION[
            "manage_leave_type"
        ]

    );

}


// =====================================
// Get All Leave Requests
// =====================================

$leaveQuery = "

    SELECT

        leave_requests.id,

        leave_requests.leave_type,

        leave_requests.start_date,

        leave_requests.end_date,

        leave_requests.reason,

        leave_requests.status,

        employees.full_name

    FROM leave_requests

    INNER JOIN employees

    ON

    leave_requests.employee_id

    =

    employees.id

    ORDER BY

    leave_requests.id DESC

";


$leaveResult = mysqli_query(

    $conn,

    $leaveQuery

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

        Manage Leave Requests - HRTech

    </title>


    <!-- Bootstrap -->

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

        Manage Leave Requests

    </h2>


    <p class="text-muted">

        Review and approve or reject
        employee leave requests.

    </p>


    <!-- Message -->

    <?php

    if ($message !== "") {

    ?>

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

    <?php

    }

    ?>


    <!-- Leave Table -->

    <div class="card shadow border-0">


        <div class="card-body">


            <div class="table-responsive">


                <table class="table table-bordered table-hover">


                    <thead class="table-primary">


                        <tr>

                            <th>Employee</th>

                            <th>Leave Type</th>

                            <th>Start Date</th>

                            <th>End Date</th>

                            <th>Reason</th>

                            <th>Status</th>

                            <th>Action</th>

                        </tr>


                    </thead>


                    <tbody>


                    <?php

                    if (

                        $leaveResult

                        &&

                        mysqli_num_rows(
                            $leaveResult
                        ) > 0

                    ) {

                        while (

                            $leave =

                            mysqli_fetch_assoc(
                                $leaveResult
                            )

                        ) {

                            $status = trim(

                                $leave["status"]

                            );


                            // Badge Color

                            if (

                                $status ===
                                "Approved"

                            ) {

                                $badge =

                                "success";

                            } elseif (

                                $status ===
                                "Rejected"

                            ) {

                                $badge =

                                "danger";

                            } else {

                                $badge =

                                "warning";

                            }

                    ?>


                        <tr>


                            <!-- Employee -->

                            <td>

                                <?php

                                echo htmlspecialchars(

                                    $leave[
                                        "full_name"
                                    ]

                                );

                                ?>

                            </td>


                            <!-- Leave Type -->

                            <td>

                                <?php

                                echo htmlspecialchars(

                                    $leave[
                                        "leave_type"
                                    ]

                                );

                                ?>

                            </td>


                            <!-- Start Date -->

                            <td>

                                <?php

                                echo htmlspecialchars(

                                    $leave[
                                        "start_date"
                                    ]

                                );

                                ?>

                            </td>


                            <!-- End Date -->

                            <td>

                                <?php

                                echo htmlspecialchars(

                                    $leave[
                                        "end_date"
                                    ]

                                );

                                ?>

                            </td>


                            <!-- Reason -->

                            <td>

                                <?php

                                echo htmlspecialchars(

                                    $leave[
                                        "reason"
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


                            <!-- Action -->

                            <td>


                            <?php

                            if (

                                $status ===
                                "Pending"

                            ) {

                            ?>


                                <form
                                    method="POST"
                                    class="d-flex gap-2"
                                >


                                    <input
                                        type="hidden"
                                        name="leave_id"
                                        value="<?php

                                        echo (int)

                                        $leave["id"];

                                        ?>"
                                    >


                                    <button
                                        type="submit"
                                        name="status"
                                        value="Approved"
                                        class="
                                        btn
                                        btn-success
                                        btn-sm
                                        "
                                    >

                                        Approve

                                    </button>


                                    <button
                                        type="submit"
                                        name="status"
                                        value="Rejected"
                                        class="
                                        btn
                                        btn-danger
                                        btn-sm
                                        "
                                    >

                                        Reject

                                    </button>


                                    <input
                                        type="hidden"
                                        name="update_leave"
                                        value="1"
                                    >


                                </form>


                            <?php

                            } else {

                            ?>


                                <span class="text-muted">

                                    Decision Completed

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
                                colspan="7"
                                class="
                                text-center
                                text-muted
                                "
                            >

                                No leave requests found.

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