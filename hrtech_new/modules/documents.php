<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once __DIR__ . '/../database.php';

$role = strtolower($_SESSION['role'] ?? 'employee');
$userId = (int)($_SESSION['user_id'] ?? 0);
$email = trim($_SESSION['email'] ?? '');
$name = trim($_SESSION['name'] ?? '');

$message = '';

/* ---------------------------------
   PROJECT AND UPLOAD DIRECTORY
--------------------------------- */

$projectRoot = dirname(__DIR__);

$uploadDir = $projectRoot . DIRECTORY_SEPARATOR .
             'uploads' . DIRECTORY_SEPARATOR .
             'employee_documents' . DIRECTORY_SEPARATOR;

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$uploadDir = realpath($uploadDir);

if ($uploadDir === false) {
    die('Upload directory could not be created.');
}

$uploadDir .= DIRECTORY_SEPARATOR;

// URL of the project root
$baseUrl = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

/* ---------------------------------
   CREATE DOCUMENT TABLE
--------------------------------- */

mysqli_query($conn, "
CREATE TABLE IF NOT EXISTS employee_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    document_name VARCHAR(150) NOT NULL,
    category VARCHAR(80) NOT NULL,
    stored_file VARCHAR(255) NOT NULL,
    original_file VARCHAR(255) NOT NULL,
    review_status VARCHAR(30) NOT NULL DEFAULT 'Pending Review',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

/* ---------------------------------
   FIND EMPLOYEE
--------------------------------- */

$employee = null;
$employeeId = 0;

if ($email !== '') {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, full_name, email
         FROM employees
         WHERE LOWER(TRIM(email)) = LOWER(TRIM(?))
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);

    $employee = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );

    mysqli_stmt_close($stmt);
}

if (!$employee && $name !== '') {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, full_name, email
         FROM employees
         WHERE LOWER(TRIM(full_name)) = LOWER(TRIM(?))
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, 's', $name);
    mysqli_stmt_execute($stmt);

    $employee = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );

    mysqli_stmt_close($stmt);
}

$employeeId = (int)($employee['id'] ?? 0);

/* ---------------------------------
   ALLOWED DOCUMENT CATEGORIES
--------------------------------- */

$allowedCategories = [
    'Resume',
    'Identity Proof',
    'Educational Certificate',
    'Offer / Appointment Letter',
    'Experience Letter',
    'Other'
];

/* ---------------------------------
   UPLOAD DOCUMENT
--------------------------------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['upload_document'])) {

    if (!$employeeId) {

        $message = 'Your employee profile was not found. Please contact HR.';

    } elseif (
        !isset($_FILES['document_file']) ||
        $_FILES['document_file']['error'] !== UPLOAD_ERR_OK
    ) {

        $message = 'Please select a file to upload.';

    } else {

        $category = trim($_POST['category'] ?? 'Other');
        $docName = trim($_POST['document_name'] ?? '');
        $file = $_FILES['document_file'];

        $ext = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        $allowedExt = [
            'pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'
        ];

        if (
            !in_array($category, $allowedCategories, true) ||
            $docName === ''
        ) {

            $message = 'Enter a document name and choose a valid category.';

        } elseif (
            !in_array($ext, $allowedExt, true) ||
            $file['size'] > 5 * 1024 * 1024
        ) {

            $message = 'Allowed formats: PDF, JPG, PNG, DOC, DOCX. Maximum size: 5 MB.';

        } else {

            $stored = bin2hex(random_bytes(16)) . '.' . $ext;

            $destination = $uploadDir . $stored;

            if (move_uploaded_file($file['tmp_name'], $destination)) {

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO employee_documents
                    (employee_id, document_name, category,
                     stored_file, original_file)
                    VALUES (?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    'issss',
                    $employeeId,
                    $docName,
                    $category,
                    $stored,
                    $file['name']
                );

                if (mysqli_stmt_execute($stmt)) {

                    $message = 'Document uploaded successfully. It is waiting for HR review.';

                } else {

                    @unlink($destination);

                    $message = 'Could not save document details.';
                }

                mysqli_stmt_close($stmt);

            } else {

                $message = 'Upload failed. Check folder permissions.';
            }
        }
    }
}

/* ---------------------------------
   DELETE DOCUMENT
--------------------------------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['delete_document'])) {

    $id = (int)($_POST['document_id'] ?? 0);

    if ($role === 'admin') {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, stored_file
             FROM employee_documents
             WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, 'i', $id);

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, stored_file
             FROM employee_documents
             WHERE id = ? AND employee_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'ii',
            $id,
            $employeeId
        );
    }

    mysqli_stmt_execute($stmt);

    $row = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );

    mysqli_stmt_close($stmt);

    if ($row) {

        $del = mysqli_prepare(
            $conn,
            "DELETE FROM employee_documents WHERE id = ?"
        );

        mysqli_stmt_bind_param($del, 'i', $id);
        mysqli_stmt_execute($del);
        mysqli_stmt_close($del);

        $path = $uploadDir . basename($row['stored_file']);

        if (is_file($path)) {
            unlink($path);
        }

        $message = 'Document removed.';

    } else {

        $message = 'Document not found or access denied.';
    }
}

/* ---------------------------------
   ADMIN DOCUMENT REVIEW
--------------------------------- */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['review_document']) &&
    $role === 'admin'
) {

    $id = (int)($_POST['document_id'] ?? 0);

    $status = ($_POST['review_status'] ?? '') === 'Approved'
        ? 'Approved'
        : 'Rejected';

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE employee_documents
         SET review_status = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, 'si', $status, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $message = 'Document review status updated.';
}

/* ---------------------------------
   FETCH DOCUMENTS
--------------------------------- */

if ($role === 'admin') {

    $sql = "
        SELECT d.*, e.full_name, e.email
        FROM employee_documents d
        LEFT JOIN employees e
        ON e.id = d.employee_id
        ORDER BY d.uploaded_at DESC
    ";

    $result = mysqli_query($conn, $sql);

} else {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT d.*, e.full_name, e.email
         FROM employee_documents d
         LEFT JOIN employees e
         ON e.id = d.employee_id
         WHERE d.employee_id = ?
         ORDER BY d.uploaded_at DESC"
    );

    mysqli_stmt_bind_param($stmt, 'i', $employeeId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
}

?>

<!doctype html>
<html lang="en">

<head>

<meta charset="utf-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Document Center | HRTech</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link rel="stylesheet" href="../css/style.css">

</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-primary shadow-sm">

    <div class="container">

        <a class="navbar-brand fw-bold" href="../dashboard.php">
            HRTech · Document Center
        </a>

        <div class="d-flex gap-2">

            <a class="btn btn-outline-light btn-sm"
               href="../dashboard.php">
                ← Back
            </a>

            <a class="btn btn-danger btn-sm"
               href="../logout.php">
                Logout
            </a>

        </div>

    </div>

</nav>


<div class="container py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">

        <div>

            <h2 class="mb-1">Document Center</h2>

            <p class="text-muted mb-0">
                Securely upload, view and manage employee documents.
            </p>

        </div>

        <span class="badge text-bg-primary">
            Maximum 5 MB per file
        </span>

    </div>


    <?php if ($message): ?>

        <div class="alert alert-info">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <?php if ($role !== 'admin'): ?>

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <h5>Upload a Document</h5>

            <p class="text-muted small">
                Accepted formats: PDF, JPG, PNG, DOC and DOCX.
            </p>

            <form method="post"
                  enctype="multipart/form-data"
                  class="row g-3">

                <div class="col-md-4">

                    <label class="form-label">Document Name</label>

                    <input
                    class="form-control"
                    name="document_name"
                    required
                    placeholder="e.g. MCA Semester Marksheet">

                </div>


                <div class="col-md-4">

                    <label class="form-label">Category</label>

                    <select class="form-select"
                            name="category"
                            required>

                        <?php foreach ($allowedCategories as $cat): ?>

                            <option value="<?= htmlspecialchars($cat) ?>">
                                <?= htmlspecialchars($cat) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="col-md-4">

                    <label class="form-label">Choose File</label>

                    <input
                    class="form-control"
                    type="file"
                    name="document_file"
                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                    required>

                </div>


                <div class="col-12">

                    <button
                    class="btn btn-primary"
                    type="submit"
                    name="upload_document"
                    value="1">

                        Upload Document

                    </button>

                </div>

            </form>

        </div>

    </div>

    <?php endif; ?>


    <div class="card shadow-sm border-0">

        <div class="card-body">

            <h5 class="mb-3">

                <?= $role === 'admin'
                    ? 'All Employee Documents'
                    : 'My Documents' ?>

            </h5>


            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            <?php if ($role === 'admin'): ?>

                                <th>Employee</th>

                            <?php endif; ?>

                            <th>Document</th>
                            <th>Category</th>
                            <th>Uploaded</th>
                            <th>Status</th>
                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if ($result && mysqli_num_rows($result) > 0): ?>

                        <?php while ($doc = mysqli_fetch_assoc($result)): ?>

                        <tr>

                            <?php if ($role === 'admin'): ?>

                                <td>

                                    <?= htmlspecialchars($doc['full_name'] ?? 'Unknown') ?>

                                    <br>

                                    <small class="text-muted">
                                        <?= htmlspecialchars($doc['email'] ?? '') ?>
                                    </small>

                                </td>

                            <?php endif; ?>


                            <td>

                                <?= htmlspecialchars($doc['document_name']) ?>

                                <br>

                                <small class="text-muted">
                                    <?= htmlspecialchars($doc['original_file']) ?>
                                </small>

                            </td>


                            <td>
                                <?= htmlspecialchars($doc['category']) ?>
                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    date('d M Y', strtotime($doc['uploaded_at']))
                                ) ?>
                            </td>


                            <td>

                                <span class="badge
                                <?= $doc['review_status'] === 'Approved'
                                    ? 'text-bg-success'
                                    : ($doc['review_status'] === 'Rejected'
                                        ? 'text-bg-danger'
                                        : 'text-bg-warning') ?>">

                                    <?= htmlspecialchars($doc['review_status']) ?>

                                </span>

                            </td>


                            <td class="d-flex flex-wrap gap-1">

                                <?php
                                $storedFile = basename($doc['stored_file']);
                                $physicalPath = $uploadDir . $storedFile;
                                $fileUrl = $baseUrl .
                                    '/uploads/employee_documents/' .
                                    rawurlencode($storedFile);
                                ?>

                                <?php if (is_file($physicalPath)): ?>

                                    <a
                                    class="btn btn-sm btn-outline-primary"
                                    target="_blank"
                                    href="<?= htmlspecialchars($fileUrl) ?>">

                                        View / Download

                                    </a>

                                <?php else: ?>

                                    <span class="badge text-bg-danger">
                                        File Missing
                                    </span>

                                <?php endif; ?>


                                <?php if (
                                    $role === 'admin' &&
                                    $doc['review_status'] === 'Pending Review'
                                ): ?>

                                    <form method="post">

                                        <input type="hidden"
                                               name="document_id"
                                               value="<?= (int)$doc['id'] ?>">

                                        <input type="hidden"
                                               name="review_document"
                                               value="1">

                                        <button
                                        class="btn btn-sm btn-success"
                                        name="review_status"
                                        value="Approved">

                                            Approve

                                        </button>

                                    </form>


                                    <form method="post">

                                        <input type="hidden"
                                               name="document_id"
                                               value="<?= (int)$doc['id'] ?>">

                                        <input type="hidden"
                                               name="review_document"
                                               value="1">

                                        <button
                                        class="btn btn-sm btn-outline-danger"
                                        name="review_status"
                                        value="Rejected">

                                            Reject

                                        </button>

                                    </form>

                                <?php endif; ?>


                                <form method="post"
                                      onsubmit="return confirm('Remove this document?')">

                                    <input type="hidden"
                                           name="document_id"
                                           value="<?= (int)$doc['id'] ?>">

                                    <button
                                    class="btn btn-sm btn-outline-danger"
                                    name="delete_document"
                                    value="1">

                                        Delete

                                    </button>

                                </form>

                            </td>

                        </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="<?= $role === 'admin' ? 6 : 5 ?>"
                                class="text-center text-muted py-4">

                                No documents uploaded yet.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>