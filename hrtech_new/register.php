<?php

require_once __DIR__ . "/database.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "employee";

    if (
        empty($name) ||
        empty($email) ||
        empty($password)
    ) {

        $message = "Please fill in all fields.";
        $messageType = "danger";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "danger";

    } elseif (strlen($password) < 6) {

        $message = "Password must contain at least 6 characters.";
        $messageType = "danger";

    } else {

        // Check whether email already exists

        $checkSql = "
            SELECT id
            FROM users
            WHERE email = ?
            LIMIT 1
        ";

        $checkStmt = mysqli_prepare(
            $conn,
            $checkSql
        );

        mysqli_stmt_bind_param(
            $checkStmt,
            "s",
            $email
        );

        mysqli_stmt_execute(
            $checkStmt
        );

        $checkResult = mysqli_stmt_get_result(
            $checkStmt
        );

        if (
            mysqli_num_rows(
                $checkResult
            ) > 0
        ) {

            $message =
                "This email is already registered.";

            $messageType =
                "warning";

        } else {

            $hashedPassword =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

            $sql = "
                INSERT INTO users
                (
                    name,
                    email,
                    password,
                    role
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?
                )
            ";

           $stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
die("SQL Error: " . mysqli_error($conn));
}


            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $name,
                $email,
                $hashedPassword,
                $role
            );

            if (
                mysqli_stmt_execute(
                    $stmt
                )
            ) {

                $message =
                    "Registration successful! You can now log in.";

                $messageType =
                    "success";

            } else {

                $message =
                    "Registration failed. Please try again.";

                $messageType =
                    "danger";

            }

        }

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

    <title>
        Register | HRTech
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- HRTech CSS -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <style>

        body {

            min-height: 100vh;

            background:
                linear-gradient(
                    135deg,
                    #0f172a,
                    #1d4ed8,
                    #60a5fa
                );

        }


        .registration-wrapper {

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 30px 15px;

        }


        .registration-card {

            width: 100%;

            max-width: 500px;

            border: none;

            border-radius: 22px;

            overflow: hidden;

            background: white;

            box-shadow:
                0 25px 60px
                rgba(
                    0,
                    0,
                    0,
                    0.30
                );

        }


        .registration-header {

            padding:
                35px
                30px;

            text-align: center;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #0f172a,
                    #2563eb
                );

        }


        .logo-box {

            width: 65px;

            height: 65px;

            display: flex;

            align-items: center;

            justify-content: center;

            margin:
                0
                auto
                15px;

            border-radius: 18px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.18
                );

            font-size: 30px;

        }


        .registration-header h2 {

            margin: 0;

            font-weight: 700;

        }


        .registration-header p {

            margin:
                8px
                0
                0;

            opacity: 0.85;

        }


        .registration-body {

            padding:
                35px
                32px;

        }


        .form-label {

            font-weight: 600;

            color: #334155;

        }


        .form-control,
        .form-select {

            height: 48px;

            border-radius: 10px;

        }


        .register-button {

            height: 50px;

            border: none;

            border-radius: 10px;

            font-size: 16px;

            font-weight: 700;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #1d4ed8
                );

        }


        .register-button:hover {

            background:
                linear-gradient(
                    135deg,
                    #1d4ed8,
                    #1e40af
                );

        }


        .login-text {

            text-align: center;

            margin-top: 22px;

            color: #64748b;

        }


        .login-text a {

            color: #2563eb;

            font-weight: 700;

            text-decoration: none;

        }


        .password-box {

            position: relative;

        }


        .show-password {

            position: absolute;

            top: 50%;

            right: 12px;

            transform:
                translateY(-50%);

            border: none;

            background: transparent;

            color: #2563eb;

            font-weight: 600;

        }


        @media (
            max-width: 576px
        ) {

            .registration-body {

                padding:
                    25px
                    20px;

            }

            .registration-header {

                padding:
                    28px
                    20px;

            }

        }

    </style>

</head>


<body>


<div class="registration-wrapper">


    <div class="registration-card">


        <!-- Header -->

        <div class="registration-header">


            <div class="logo-box">

                👥

            </div>


            <h2>

                HRTech

            </h2>


            <p>

                Create your account

            </p>


        </div>


        <!-- Form -->

        <div class="registration-body">


            <?php if ($message !== "") { ?>


                <div
                    class="
                    alert
                    alert-<?php
                    echo $messageType;
                    ?>
                    "
                >


                    <?php

                    echo htmlspecialchars(
                        $message
                    );

                    ?>


                </div>


            <?php } ?>


            <form method="POST">


                <!-- Name -->

                <div class="mb-3">


                    <label
                        class="form-label"
                    >

                        Full Name

                    </label>


                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        placeholder="Enter your full name"
                        value="<?php
                        echo htmlspecialchars(
                            $_POST[
                                "name"
                            ] ?? ""
                        );
                        ?>"
                        required
                    >


                </div>


                <!-- Email -->

                <div class="mb-3">


                    <label
                        class="form-label"
                    >

                        Email Address

                    </label>


                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter your email"
                        value="<?php
                        echo htmlspecialchars(
                            $_POST[
                                "email"
                            ] ?? ""
                        );
                        ?>"
                        required
                    >


                </div>


                <!-- Password -->

                <div class="mb-3">


                    <label
                        class="form-label"
                    >

                        Password

                    </label>


                    <div class="password-box">


                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            placeholder="Minimum 6 characters"
                            minlength="6"
                            required
                        >


                        <button
                            type="button"
                            class="show-password"
                            onclick="
                            togglePassword()
                            "
                        >

                            Show

                        </button>


                    </div>


                </div>


                <!-- Role -->

                <div class="mb-4">


                    <label
                        class="form-label"
                    >

                        Select Role

                    </label>


                    <select
                        name="role"
                        class="form-select"
                        required
                    >


                        <option
                            value="employee"
                        >

                            Employee

                        </option>


                        <option
                            value="manager"
                        >

                            Manager

                        </option>


                        <option
                            value="admin"
                        >

                            HR Admin

                        </option>


                    </select>


                </div>


                <!-- Button -->

                <button
                    type="submit"
                    class="
                    btn
                    btn-primary
                    w-100
                    register-button
                    "
                >

                    Create Account

                </button>


            </form>


            <div class="login-text">


                Already have an account?


                <a
                    href="login.php"
                >

                    Login

                </a>


            </div>


        </div>


    </div>


</div>


<script>

function togglePassword() {

    const password =
        document.getElementById(
            "password"
        );


    const button =
        document.querySelector(
            ".show-password"
        );


    if (
        password.type ===
        "password"
    ) {

        password.type =
            "text";


        button.innerText =
            "Hide";

    } else {

        password.type =
            "password";


        button.innerText =
            "Show";

    }

}

</script>


</body>

</html>