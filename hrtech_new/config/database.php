<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "hrtech_db"
);

if (!$conn) {
    die(
        "Database connection failed: "
        . mysqli_connect_error()
    );
}

?>