<?php

session_start();

require_once __DIR__ . '/database.php';
$message = "";
$selectedRole = strtolower(trim($_GET["role"] ?? ""));
$validRoles = ["admin", "manager", "employee"];
if (!in_array($selectedRole, $validRoles, true)) {
    $selectedRole = "";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim(
        $_POST["email"] ?? ""
    );

    $password =
        $_POST["password"] ?? "";

    if (
        empty($email) ||
        empty($password)
    ) {

        $message =
            "Please enter your email and password.";

    } else {

        $sql = "
            SELECT *
            FROM users
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = mysqli_prepare(
            $conn,
            $sql
        );

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $email
        );

        mysqli_stmt_execute(
            $stmt
        );

        $result =
            mysqli_stmt_get_result(
                $stmt
            );

        if (
            mysqli_num_rows(
                $result
            ) === 1
        ) {

            $user =
                mysqli_fetch_assoc(
                    $result
                );

            if (
                password_verify(
                    $password,
                    $user["password"]
                ) && (
                    $selectedRole === "" ||
                    strtolower($user["role"]) === $selectedRole
                )
            ) {

                session_regenerate_id(
                    true
                );

                $_SESSION["user_id"] =
                    $user["id"];

                $_SESSION["name"] =
                    $user["name"];

                $_SESSION["email"] =
                    $user["email"];

                $_SESSION["role"] =
                    $user["role"];

                header(
                    "Location: dashboard.php"
                );

                exit();

            } else {

                $message =
                    "Incorrect email, password, or portal selection.";

            }

        } else {

            $message =
                "Incorrect email or password.";

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
        Sign In | HRTech
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <style>

        * {

            box-sizing:
                border-box;

        }


        body {

            margin: 0;

            min-height:
                100vh;

            font-family:
                Arial,
                sans-serif;

            background:
                #eef1ef;

            color:
                #202624;

        }


        .login-page {

            min-height:
                100vh;

            display:
                flex;

        }


        /* Left Corporate Panel */

        .company-panel {

            width:
                48%;

            min-height:
                100vh;

            padding:
                70px;

            display:
                flex;

            flex-direction:
                column;

            justify-content:
                space-between;

            color:
                white;

            background:
                linear-gradient(
                    145deg,
                    #18231f,
                    #263a32
                );

            position:
                relative;

            overflow:
                hidden;

        }


        .company-panel::before {

            content:
                "";

            position:
                absolute;

            width:
                420px;

            height:
                420px;

            border-radius:
                50%;

            top:
                -180px;

            right:
                -180px;

            background:
                rgba(
                    87,
                    150,
                    112,
                    0.18
                );

        }


        .company-panel::after {

            content:
                "";

            position:
                absolute;

            width:
                320px;

            height:
                320px;

            border-radius:
                50%;

            bottom:
                -160px;

            left:
                -100px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.04
                );

        }


        .brand {

            position:
                relative;

            z-index:
                1;

            display:
                flex;

            align-items:
                center;

            gap:
                12px;

            font-size:
                25px;

            font-weight:
                700;

        }


        .brand-icon {

            width:
                48px;

            height:
                48px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                12px;

            background:
                #5d9873;

            font-size:
                22px;

        }


        .company-content {

            position:
                relative;

            z-index:
                1;

            max-width:
                520px;

        }


        .company-content h1 {

            font-size:
                48px;

            line-height:
                1.15;

            font-weight:
                700;

            margin-bottom:
                22px;

        }


        .company-content p {

            font-size:
                18px;

            line-height:
                1.7;

            color:
                #c9d4ce;

        }


        .company-footer {

            position:
                relative;

            z-index:
                1;

            color:
                #9eaea6;

            font-size:
                14px;

        }


        /* Right Login Panel */

        .login-panel {

            width:
                52%;

            min-height:
                100vh;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            padding:
                40px;

            background:
                #f7f8f7;

        }


        .login-box {

            width:
                100%;

            max-width:
                440px;

        }


        .login-heading {

            margin-bottom:
                35px;

        }


        .login-heading h2 {

            font-size:
                34px;

            font-weight:
                700;

            margin-bottom:
                10px;

            color:
                #1d2823;

        }


        .login-heading p {

            color:
                #6c7872;

            margin:
                0;

        }


        .form-label {

            font-size:
                14px;

            font-weight:
                650;

            color:
                #34413b;

            margin-bottom:
                8px;

        }


        .input-group-custom {

            position:
                relative;

        }


        .input-icon {

            position:
                absolute;

            left:
                16px;

            top:
                50%;

            transform:
                translateY(-50%);

            color:
                #7b8982;

            z-index:
                2;

        }


        .form-control {

            height:
                54px;

            padding-left:
                47px;

            border:
                1px solid
                #d6ddd9;

            border-radius:
                10px;

            background:
                white;

            box-shadow:
                none;

        }


        .form-control:focus {

            border-color:
                #5d9873;

            box-shadow:
                0 0 0 4px
                rgba(
                    93,
                    152,
                    115,
                    0.12
                );

        }


        .password-toggle {

            position:
                absolute;

            right:
                12px;

            top:
                50%;

            transform:
                translateY(-50%);

            border:
                none;

            background:
                transparent;

            color:
                #4d695a;

            font-size:
                13px;

            font-weight:
                700;

            z-index:
                3;

        }


        .password-input {

            padding-right:
                60px;

        }


        .login-button {

            width:
                100%;

            height:
                54px;

            border:
                none;

            border-radius:
                10px;

            background:
                #263a32;

            color:
                white;

            font-weight:
                700;

            transition:
                0.2s;

        }


        .login-button:hover {

            background:
                #17251f;

            transform:
                translateY(-1px);

        }


        .register-text {

            margin-top:
                25px;

            text-align:
                center;

            color:
                #6c7872;

        }


        .register-text a {

            color:
                #35684a;

            font-weight:
                700;

            text-decoration:
                none;

        }


        .alert {

            border:
                none;

            border-radius:
                10px;

            margin-bottom:
                22px;

        }


        /* Mobile */

        @media (
            max-width:
            900px
        ) {

            .company-panel {

                display:
                    none;

            }


            .login-panel {

                width:
                    100%;

            }

        }


        @media (
            max-width:
            500px
        ) {

            .login-panel {

                padding:
                    25px
                    20px;

            }


            .login-heading h2 {

                font-size:
                    29px;

            }

        }

    </style>

</head>


<body>


<div class="login-page">


    <!-- Left Company Section -->

    <section class="company-panel">


        <div class="brand">


            <div class="brand-icon">

                H

            </div>


            HRTech


        </div>


        <div class="company-content">


            <h1>

                People.
                Performance.
                Progress.

            </h1>


            <p>

                Manage employees, tasks,
                goals and performance from one
                secure HR platform.

            </p>


        </div>


        <div class="company-footer">


            © 2026 HRTech.
            Employee Management System.


        </div>


    </section>


    <!-- Right Login Section -->

    <section class="login-panel">


        <div class="login-box">


            <div class="login-heading">


                <h2>
                    <?php
                    echo $selectedRole !== ""
                        ? htmlspecialchars(ucfirst($selectedRole)) . " Login"
                        : "Welcome back";
                    ?>
                </h2>


                <p>
                    Sign in to access your HRTech account.
                </p>


            </div>


            <?php if ($message !== "") { ?>


                <div
                    class="
                    alert
                    alert-danger
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


                <!-- Email -->

                <div class="mb-4">


                    <label
                        class="form-label"
                    >

                        Work Email

                    </label>


                    <div
                        class="
                        input-group-custom
                        "
                    >


                        <span
                            class="
                            input-icon
                            "
                        >

                            ✉

                        </span>


                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="name@company.com"
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


                </div>


                <!-- Password -->

                <div class="mb-4">


                    <label
                        class="form-label"
                    >

                        Password

                    </label>


                    <div
                        class="
                        input-group-custom
                        "
                    >


                        <span
                            class="
                            input-icon
                            "
                        >

                            🔒

                        </span>


                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="
                            form-control
                            password-input
                            "
                            placeholder="Enter your password"
                            required
                        >


                        <button
                            type="button"
                            class="
                            password-toggle
                            "
                            onclick="
                            togglePassword()
                            "
                        >

                            Show

                        </button>


                    </div>


                </div>


                <!-- Remember -->

                <div
                    class="
                    form-check
                    mb-4
                    "
                >


                    <input
                        class="
                        form-check-input
                        "
                        type="checkbox"
                        id="remember"
                    >


                    <label
                        class="
                        form-check-label
                        "
                        for="remember"
                    >

                        Keep me signed in

                    </label>


                </div>


                <!-- Login -->

                <button
                    type="submit"
                    class="
                    login-button
                    "
                >

                    Sign In

                </button>


            </form>


            <div class="register-text">


                New to HRTech?


                <a
                    href="register.php"
                >

                    Create an account

                </a>


            </div>


        </div>


    </section>


</div>


<script>

function togglePassword() {

    const password =
        document.getElementById(
            "password"
        );


    const button =
        document.querySelector(
            ".password-toggle"
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
