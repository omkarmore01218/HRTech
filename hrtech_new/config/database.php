
<?php

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME') ?: 'hrtech_db';
$port = (int)(getenv('DB_PORT') ?: 3306);
$useSSL = getenv('DB_SSL') === 'true';

$conn = mysqli_init();

if ($useSSL) {
    mysqli_ssl_set(
        $conn,
        null,
        null,
        '/etc/ssl/certs/ca-certificates.crt',
        null,
        null
    );
}

$flags = $useSSL ? MYSQLI_CLIENT_SSL : 0;

if (!mysqli_real_connect(
    $conn,
    $host,
    $user,
    $password,
    $database,
    $port,
    null,
    $flags
)) {
    die("Database connection failed: " . mysqli_connect_error());
}

?>
