<?php

session_start();

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");

    exit();
}

if ($_SESSION["role"] !== "employee") {

    header("Location: dashboard.php");

    exit();
}

require_once __DIR__ . '/database.php';


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $taskId = (int) $_POST["task_id"];

    $submissionText = trim(
        $_POST["submission_text"]
    );


    if (empty($submissionText)) {

        header(
            "Location: my_tasks.php?error=empty"
        );

        exit();
    }


    $updateQuery = "
        UPDATE onboarding_tasks
        SET
            submission_text = ?,
            submitted_at = NOW(),
            status = 'Completed'
        WHERE id = ?
    ";


    $updateStatement = mysqli_prepare(
        $conn,
        $updateQuery
    );


    mysqli_stmt_bind_param(
        $updateStatement,
        "si",
        $submissionText,
        $taskId
    );


    mysqli_stmt_execute(
        $updateStatement
    );


    header(
        "Location: my_tasks.php?success=1"
    );

    exit();
}


header(
    "Location: my_tasks.php"
);

exit();

?>