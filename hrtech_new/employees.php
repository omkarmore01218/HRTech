<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/database.php';

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $employeeId = trim($_POST["employee_id"]);
    $fullName = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $department = trim($_POST["department"]);
    $designation = trim($_POST["designation"]);
    $joiningDate = $_POST["joining_date"];

    $sql = "INSERT INTO employees
            (
                employee_id,
                full_name,
                email,
                phone,
                department,
                designation,
                joining_date
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sssssss",
        $employeeId,
        $fullName,
        $email,
        $phone,
        $department,
        $designation,
        $joiningDate
    );

    if (mysqli_stmt_execute($stmt)) {
        $message = "Employee added successfully!";
        $messageType = "success";
    } else {
        $message = "Error: " . mysqli_error($conn);
        $messageType = "danger";
    }
}

$employees = mysqli_query(
    $conn,
    "SELECT * FROM employees ORDER BY id DESC"
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

    <title>Employee Management - HRTech</title>

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

<nav class="navbar navbar-dark bg-primary shadow-sm">
  <div class="container d-flex align-items-center flex-nowrap gap-2">
    <a
            class="navbar-brand"
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

<div class="container mt-4">

    <h2>Employee Management</h2>

    <?php if ($message != "") { ?>

        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php } ?>


    <div class="card shadow mt-4">

        <div class="card-body">

            <h4>Add New Employee</h4>

            <form method="POST">

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label>
                            Employee ID
                        </label>

                        <input
                            type="text"
                            name="employee_id"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="col-md-6 mb-3">

                        <label>
                            Full Name
                        </label>

                        <input
                            type="text"
                            name="full_name"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="col-md-6 mb-3">

                        <label>
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="col-md-6 mb-3">

                        <label>
                            Phone
                        </label>

                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                        >

                    </div>


                    <div class="col-md-4 mb-3">

                        <label>
                            Department
                        </label>

                        <input
                            type="text"
                            name="department"
                            class="form-control"
                        >

                    </div>


                    <div class="col-md-4 mb-3">

                        <label>
                            Designation
                        </label>

                        <input
                            type="text"
                            name="designation"
                            class="form-control"
                        >

                    </div>


                    <div class="col-md-4 mb-3">

                        <label>
                            Joining Date
                        </label>

                        <input
                            type="date"
                            name="joining_date"
                            class="form-control"
                        >

                    </div>

                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Add Employee
                </button>

            </form>

        </div>

    </div>


    <div class="card shadow mt-4">

        <div class="card-body">

            <h4>Employee List</h4>

            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Department</th>

                            <th>Designation</th>

                            <th>Status</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php while (
                        $employee =
                        mysqli_fetch_assoc($employees)
                    ) { ?>

                        <tr>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee[
                                        "employee_id"
                                    ]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee[
                                        "full_name"
                                    ]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee[
                                        "email"
                                    ]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee[
                                        "department"
                                    ]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee[
                                        "designation"
                                    ]
                                );
                                ?>
                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $employee["status"] ?? "Active"
                                );
                                ?>

                            </td>

                        </tr>

                    <?php } ?>

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