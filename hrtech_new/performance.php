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
// Admin / Manager Access
// =====================================

$userRole = strtolower(
    trim(
        $_SESSION["role"] ?? ""
    )
);


if (
    $userRole !== "admin" &&
    $userRole !== "manager"
) {

    header("Location: dashboard.php");

    exit();

}


// =====================================
// Database Connection
// =====================================

require_once __DIR__ . '/database.php';


$message = "";

$error = "";


// =====================================
// Save Performance Review
// =====================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $employeeId =
        isset($_POST["employee_id"])
        ? (int) $_POST["employee_id"]
        : 0;


    $rating =
        isset($_POST["rating"])
        ? (int) $_POST["rating"]
        : 0;


    $feedback =
        isset($_POST["feedback"])
        ? trim($_POST["feedback"])
        : "";


    $reviewDate =
        isset($_POST["review_date"])
        ? trim($_POST["review_date"])
        : date("Y-m-d");


    // Validation

    if ($employeeId <= 0) {

        $error =
            "Please select an employee.";

    } elseif (
        $rating < 1 ||
        $rating > 5
    ) {

        $error =
            "Rating must be between 1 and 5.";

    } elseif ($feedback === "") {

        $error =
            "Please enter feedback.";

    } else {

        $insertQuery = "
            INSERT INTO performance_reviews
            (
                employee_id,
                rating,
                feedback,
                review_date
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?
            )
        ";


        $insertStatement = mysqli_prepare(
            $conn,
            $insertQuery
        );


        if ($insertStatement) {

            mysqli_stmt_bind_param(
                $insertStatement,
                "iiss",
                $employeeId,
                $rating,
                $feedback,
                $reviewDate
            );


            if (
                mysqli_stmt_execute(
                    $insertStatement
                )
            ) {

                $message =
                    "Performance review saved successfully.";

            } else {

                $error =
                    "Performance review could not be saved.";

            }

        } else {

            $error =
                "Database query preparation failed.";

        }

    }

}


// =====================================
// Get Employees
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
// Get Performance Reviews
// =====================================

$reviewListQuery = "
    SELECT
        performance_reviews.id,
        performance_reviews.rating,
        performance_reviews.feedback,
        performance_reviews.review_date,
        employees.full_name,
        employees.employee_id

    FROM performance_reviews

    INNER JOIN employees

    ON performance_reviews.employee_id
    =
    employees.id

    ORDER BY
    performance_reviews.review_date DESC,
    performance_reviews.id DESC
";


$reviewListResult = mysqli_query(
    $conn,
    $reviewListQuery
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

        Performance Review

    </title>


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

        Performance Review

    </h2>


    <p class="text-muted">

        Add and manage employee performance reviews.

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


    <!-- Add Review Form -->

    <div class="card shadow border-0 mb-5">

        <div class="card-body">


            <h4 class="mb-4">

                Add Performance Review

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


                <!-- Rating -->

                <div class="mb-3">

                    <label class="form-label">

                        Rating

                    </label>


                    <select
                        name="rating"
                        class="form-select"
                        required
                    >

                        <option value="">

                            Select Rating

                        </option>

                        <option value="1">

                            1 / 5

                        </option>

                        <option value="2">

                            2 / 5

                        </option>

                        <option value="3">

                            3 / 5

                        </option>

                        <option value="4">

                            4 / 5

                        </option>

                        <option value="5">

                            5 / 5

                        </option>

                    </select>

                </div>


                <!-- Feedback -->

                <div class="mb-3">

                    <label class="form-label">

                        Feedback

                    </label>


                    <textarea
                        name="feedback"
                        class="form-control"
                        rows="4"
                        placeholder="Enter employee performance feedback"
                        required
                    ></textarea>

                </div>


                <!-- Review Date -->

                <div class="mb-3">

                    <label class="form-label">

                        Review Date

                    </label>


                    <input
                        type="date"
                        name="review_date"
                        class="form-control"
                        value="<?php

                        echo date(
                            "Y-m-d"
                        );

                        ?>"
                        required
                    >

                </div>


                <!-- Submit -->

                <button
                    type="submit"
                    class="btn btn-warning"
                >

                    Save Performance Review

                </button>


            </form>


        </div>

    </div>


    <!-- Review List -->

    <div class="card shadow border-0">

        <div class="card-body">


            <h4 class="mb-4">

                Performance Review History

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
                        $reviewListResult &&
                        mysqli_num_rows(
                            $reviewListResult
                        ) > 0
                    ) {

                        while (
                            $review =
                            mysqli_fetch_assoc(
                                $reviewListResult
                            )
                        ) {

                    ?>


                        <tr>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $review["full_name"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $review["employee_id"]
                                );

                                ?>

                            </td>


                            <td>

                                <span
                                    class="
                                    badge
                                    bg-success
                                    "
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $review["rating"]
                                    );

                                    ?>

                                    / 5

                                </span>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $review["feedback"]
                                );

                                ?>

                            </td>


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
                                colspan="5"
                                class="text-center"
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