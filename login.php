<?php
session_start();
include 'db.php';
include 'partials/navbar.php';

$error = '';
$email = '';

// CSRF Token generate
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Simple brute force protection
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF Check
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {

        $error = 'Invalid request. Please try again.';

    }

    // Rate limiting
    elseif (
        $_SESSION['login_attempts'] >= 5 &&
        time() - $_SESSION['last_attempt_time'] < 600
    ) {

        $error = 'Too many login attempts. Try again in 10 minutes.';

    } else {

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $_SESSION['last_attempt_time'] = time();

        // Validation
        if ($email === '' || $password === '') {

            $error = 'Email and password are required.';

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = 'Invalid email format.';

        } elseif (strlen($password) < 6) {

            $error = 'Invalid email or password.';

        } else {

            if (!$conn) {

                $error = 'Database connection error.';

            } else {

                $stmt = $conn->prepare(
                    "SELECT id, name, email, password, role
                     FROM users
                     WHERE email = ?
                     LIMIT 1"
                );

                if ($stmt) {

                    $stmt->bind_param('s', $email);
                    $stmt->execute();

                    $result = $stmt->get_result();
                    $row = $result ? $result->fetch_assoc() : null;

                    $stmt->close();

                    if ($row) {

                        $storedPassword = $row['password'];
                        $loginSuccess = false;

                        // Password verify
                        if (
                            $storedPassword &&
                            password_verify($password, $storedPassword)
                        ) {

                            $loginSuccess = true;

                        }

                        // Legacy plain text password support
                        elseif (
                            $storedPassword &&
                            $password === $storedPassword
                        ) {

                            $loginSuccess = true;

                            // Auto rehash
                            $newHash = password_hash(
                                $password,
                                PASSWORD_DEFAULT
                            );

                            $update = $conn->prepare(
                                "UPDATE users
                                 SET password = ?
                                 WHERE id = ?"
                            );

                            if ($update) {

                                $update->bind_param(
                                    'si',
                                    $newHash,
                                    $row['id']
                                );

                                $update->execute();
                                $update->close();
                            }
                        }

                        // Login success
                        if ($loginSuccess) {

                            session_regenerate_id(true);

                            $_SESSION['user_id'] = $row['id'];
                            $_SESSION['user_name'] = $row['name'];
                            $_SESSION['user_email'] = $row['email'];
                            $_SESSION['user_role'] = $row['role'];

                            $_SESSION['login_attempts'] = 0;

                        // Redirect by role

if ($row['role'] === 'admin') {

    header('Location: admin.php');

} elseif ($row['role'] === 'agent') {

    header('Location: agent.php');

} else {

    header('Location: index.php');
}

exit;

                        } else {

                            $_SESSION['login_attempts']++;

                            $error = 'Invalid email or password.';
                        }

                    } else {

                        $_SESSION['login_attempts']++;

                        $error = 'Invalid email or password.';
                    }

                } else {

                    $error = 'Database query failed.';
                }
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

<title>Login - Travel Agency</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body class="bg-light">

<div class="container">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card mt-5 shadow">

                <div class="card-body">

                    <h4 class="card-title mb-3">
                        Login
                    </h4>

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

                    <form
                    method="post"
                    action="login.php"
                    id="loginForm"
                    novalidate>

                        <!-- CSRF -->
                        <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php
                        echo htmlspecialchars(
                            $_SESSION['csrf_token']
                        );
                        ?>">

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
                            ?>"
                            required>

                        </div>

                        <!-- Password -->
                        <div class="mb-3">

                            <label class="form-label">
                                Password
                            </label>

                            <input
                            type="password"
                            name="password"
                            class="form-control"
                            required
                            minlength="6">

                        </div>

                        <!-- Button -->
                        <div class="d-grid gap-2">

                            <button class="btn btn-primary">
                                Login
                            </button>

                        </div>

                        <!-- Links -->
                        <div
                        class="d-flex justify-content-between align-items-center mt-3">

                            <a href="forgot_password.php">
                                Forgot password?
                            </a>

                            <a href="register.php">
                                Register
                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- JavaScript Validation -->
<script>

document.addEventListener('DOMContentLoaded', function () {

    const form =
    document.getElementById('loginForm');

    const email =
    document.querySelector('input[name="email"]');

    const password =
    document.querySelector('input[name="password"]');

    form.addEventListener('submit', function (e) {

        let errors = [];

        // Remove old alert
        const oldAlert =
        document.querySelector('.js-alert');

        if (oldAlert) {
            oldAlert.remove();
        }

        // Email validation
        const emailValue = email.value.trim();

        if (emailValue === '') {

            errors.push('Email is required.');

        } else {

            const emailPattern =
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailPattern.test(emailValue)) {

                errors.push(
                    'Please enter valid email address.'
                );
            }
        }

        // Password validation
        const passwordValue = password.value;

        if (passwordValue === '') {

            errors.push('Password is required.');

        } else if (passwordValue.length < 6) {

            errors.push(
                'Password must be at least 6 characters.'
            );
        }

        // Show errors
        if (errors.length > 0) {

            e.preventDefault();

            const alertBox =
            document.createElement('div');

            alertBox.className =
            'alert alert-danger js-alert';

            let html = '<ul class="mb-0">';

            errors.forEach(function (error) {

                html += '<li>' + error + '</li>';

            });

            html += '</ul>';

            alertBox.innerHTML = html;

            form.prepend(alertBox);
        }

    });

});

</script>

</body>
</html>