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


// Find employee profile

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

    <title>My Performance</title>


    <!-- Bootstrap CSS -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
     <link
    rel="stylesheet"
    href="css/style.css"
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


<!-- Main Content -->

<div class="container mt-5">


    <h2>

        My Performance

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


    <?php if (!$employee) { ?>


        <div class="alert alert-warning">

            Your employee profile was not found.

            Please contact HR.

        </div>


    <?php } else { ?>


        <?php

        // Get employee performance reviews

        $reviewQuery = "
            SELECT
                rating,
                feedback,
                review_date
            FROM performance_reviews
            WHERE employee_id = ?
            ORDER BY review_date DESC
        ";


        $reviewStatement = mysqli_prepare(
            $conn,
            $reviewQuery
        );


        mysqli_stmt_bind_param(
            $reviewStatement,
            "i",
            $employee["id"]
        );


        mysqli_stmt_execute(
            $reviewStatement
        );


        $reviewResult = mysqli_stmt_get_result(
            $reviewStatement
        );

        ?>


        <div class="card shadow border-0">


            <div class="card-body">


                <h4 class="mb-4">

                    Performance Review History

                </h4>


                <div class="table-responsive">


                    <table class="table table-bordered">


                        <thead class="table-primary">


                            <tr>

                                <th>

                                    Rating

                                </th>


                                <th>

                                    Feedback

                                </th>


                                <th>

                                    Review Date

                                </th>

                            </tr>


                        </thead>


                        <tbody>


                        <?php

                        if (
                            mysqli_num_rows(
                                $reviewResult
                            ) > 0
                        ) {

                            while (
                                $review =
                                mysqli_fetch_assoc(
                                    $reviewResult
                                )
                            ) {

                        ?>


                            <tr>


                                <!-- Rating -->


                                <td>


                                    <span
                                        class="
                                        badge
                                        bg-success
                                        fs-6
                                        "
                                    >


                                        <?php

                                        echo
                                        htmlspecialchars(
                                            $review[
                                                "rating"
                                            ]
                                        );

                                        ?>


                                        / 5


                                    </span>


                                </td>


                                <!-- Feedback -->


                                <td>


                                    <?php

                                    echo htmlspecialchars(
                                        $review[
                                            "feedback"
                                        ]
                                    );

                                    ?>


                                </td>


                                <!-- Review Date -->


                                <td>


                                    <?php

                                    echo htmlspecialchars(
                                        $review[
                                            "review_date"
                                        ]
                                    );

                                    ?>


                                </td>


                            </tr>


                        <?php

                            }

                        } else {

                        ?>


                            <tr>


                                <td
                                    colspan="3"
                                    class="
                                    text-center
                                    text-muted
                                    "
                                >


                                    No performance reviews found.


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