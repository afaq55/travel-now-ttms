<?php
session_start();
require 'db.php';
require 'functions.php';

// ✅ Get package ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ✅ Fetch package from database
$stmt = $pdo->prepare('SELECT * FROM packages WHERE id = ?');
$stmt->execute([$id]);
$pkg = $stmt->fetch(PDO::FETCH_ASSOC);

// ✅ If package not found, redirect
if (!$pkg) {
    header('Location: packages.php?error=invalid_package');
    exit;
}

// ✅ Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name  = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $package_id = intval($_POST['package_id'] ?? 0);

    // ✅ Validate fields
    $errors = [];
    if ($user_name === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
    if ($phone === '') $errors[] = 'Phone number is required.';
    if ($package_id <= 0) $errors[] = 'Invalid package.';

    if (empty($errors)) {
        // ✅ Insert booking into database
        $stmt = $pdo->prepare('INSERT INTO bookings (user_name, email, phone, package_id, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$user_name, $email, $phone, $package_id, 'confirmed']);

        $booking_id = $pdo->lastInsertId();
        header('Location: booking_success.php?id=' . $booking_id);
        exit;
    } else {
        $_SESSION['booking_errors'] = $errors;
        header('Location: booking.php?id=' . $package_id);
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Booking - <?= htmlspecialchars($pkg['title'] ?? 'Package') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'partials/navbar.php'; ?>

<main class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card p-4 shadow-sm">
        <h4 class="mb-3">Book: <?= htmlspecialchars($pkg['title'] ?? 'Unknown Package') ?></h4>

        <?php if (!empty($_SESSION['booking_errors'])): ?>
          <div class="alert alert-danger">
            <ul>
              <?php foreach ($_SESSION['booking_errors'] as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
              <?php endforeach; unset($_SESSION['booking_errors']); ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post">
          <!-- ✅ Hidden package_id -->
          <input type="hidden" name="package_id" value="<?= intval($pkg['id']) ?>">

          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input name="name" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input name="phone" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Package</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($pkg['title']) ?>" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label">Price</label>
            <input type="text" class="form-control" value="PKR <?= number_format($pkg['price']) ?>" readonly>
          </div>

          <button class="btn btn-primary w-100">Confirm Booking</button>
        </form>
      </div>
    </div>
  </div>
</main>

<?php include 'partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
