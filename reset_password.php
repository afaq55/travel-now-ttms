<?php
require 'db.php';
date_default_timezone_set('Asia/Karachi');

$message = '';
$validToken = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check token and expiry
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $validToken = true;
        $user = $result->fetch_assoc();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE reset_token = ?");
            $stmt->bind_param("ss", $new_password, $token);
            $stmt->execute();

            $message = "Password updated successfully! <a href='login.php' class='alert-link'>Login now</a>";
            $validToken = false;
        }
    } else {
        $message = "Invalid or expired token.";
    }
} else {
    $message = "No token provided.";
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Reset Password - Travel Agency</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card mt-5 shadow">
        <div class="card-body">
          <h4 class="card-title mb-3">Reset Password</h4>

          <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
          <?php endif; ?>

          <?php if ($validToken): ?>
            <form method="POST">
              <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter new password" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Reset Password</button>
            </form>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
