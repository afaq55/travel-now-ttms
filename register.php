<?php
session_start();
require 'db.php';

$error = '';
$success = '';
$name = '';
$email = '';

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Only CSRF protection
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token']
        )
    ) {

        $error = 'Invalid request.';

    } else {

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($name)) {
            $error = 'Name is required.';
        } elseif (empty($email)) {
            $error = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Valid email is required.';
        } elseif (empty($password)) {
            $error = 'Password is required.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        }

        if (empty($error)) {

            try {

                // Check duplicate email
                $check = $pdo->prepare(
                    "SELECT id
                     FROM users
                     WHERE email = ?
                     LIMIT 1"
                );

                $check->execute([$email]);

                if ($check->fetch()) {

                    $error = 'Email already exists.';

                } else {

                    // Hash password
                    $hash = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    // Insert user
                    $stmt = $pdo->prepare(
                        "INSERT INTO users
                        (name, email, password, role, created_at)
                        VALUES (?, ?, ?, ?, NOW())"
                    );

                    $insert = $stmt->execute([
                        $name,
                        $email,
                        $hash,
                        'user'
                    ]);

                    if ($insert) {

                        $success = 'Account created successfully!';
                        // Clear form fields on success
                        $name = '';
                        $email = '';

                    } else {

                        $error = 'Registration failed.';
                    }
                }

            } catch (PDOException $e) {

                $error = 'Database error.';
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>

<meta charset="utf-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1">

<title>Register</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body class="bg-light">

<?php include 'partials/navbar.php'; ?>

<main class="container my-5">

    <div class="row justify-content-center">

        <div class="col-md-6">

            <div class="card shadow-sm p-4">

                <h4 class="mb-3">
                    Register
                </h4>

                <!-- Error -->
                <?php if ($error): ?>

                    <div class="alert alert-danger">

                        <?php
                        echo htmlspecialchars(
                            $error,
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>

                    </div>

                <?php endif; ?>

                <!-- Success -->
                <?php if ($success): ?>

                    <div class="alert alert-success">

                        <?php
                        echo htmlspecialchars(
                            $success,
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>

                    </div>

                <?php endif; ?>

                <!-- Form -->
                <form method="post">

                    <!-- CSRF -->
                    <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php
                    echo htmlspecialchars(
                        $_SESSION['csrf_token']
                    );
                    ?>">

                    <!-- Name -->
                    <div class="mb-3">

                        <label class="form-label">
                            Name
                        </label>

                        <input
                        type="text"
                        name="name"
                        class="form-control"
                        value="<?php
                        echo htmlspecialchars(
                            $name,
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>">

                    </div>

                    <!-- Email -->
                    <div class="mb-3">

                        <label class="form-label">
                            Email
                        </label>

                        <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?php
                        echo htmlspecialchars(
                            $email,
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>">

                    </div>

                    <!-- Password -->
                    <div class="mb-3">

                        <label class="form-label">
                            Password
                        </label>

                        <input
                        type="password"
                        name="password"
                        class="form-control">

                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">

                        <label class="form-label">
                            Confirm Password
                        </label>

                        <input
                        type="password"
                        name="confirm_password"
                        class="form-control">

                    </div>

                    <!-- Button -->
                    <div class="d-grid">

                        <button class="btn btn-primary">

                            Create Account

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</main>

</body>
</html>