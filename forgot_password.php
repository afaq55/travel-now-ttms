<?php
require 'db.php';
date_default_timezone_set('Asia/Karachi');

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expiry, $email);
        $stmt->execute();

        $resetLink = "http://localhost/travel/reset_password.php?token=$token";
        $message = "Password reset link (for testing): <a href='$resetLink'>$resetLink</a>";
    } else {
        $message = "No account found with that email.";
    }

    $stmt->close();
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Forgot Password - Travel Agency</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card mt-5 shadow">
        <div class="card-body">
          <h4 class="card-title mb-3">Forgot Password</h4>

          <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
          <?php endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Email Address</label>
              <input type="email" name="email" class="form-control" placeholder="Enter your registered email" required>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <button type="submit" class="btn btn-primary">Send Reset Link</button>
              <a href="login.php" class="text-decoration-none">Back to Login</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
