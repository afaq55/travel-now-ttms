<?php
session_start();
require 'db.php';
require_once __DIR__ . '/inc/bus_functions.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get booking ID from URL
$booking_id = intval($_GET['id'] ?? 0);

// Fetch booking details
$booking = get_booking($booking_id);
if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Invalid booking.</div></div>';
    exit;
}

// Fetch bus info
$bus = get_bus($booking['bus_id']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Booking Confirmation - Travel Agency</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="container my-5">
  <div class="card shadow-sm border-0 p-4">
    <div class="card-body">
      <h2 class="text-success mb-3">🎉 Booking Confirmed</h2>

      <p><strong>Booking ID:</strong> <?= htmlspecialchars($booking['booking_id']) ?></p>

      <h4 class="mt-4">Bus Details</h4>
      <p><strong><?= htmlspecialchars($bus['bus_name'] ?? 'Unknown Bus') ?></strong></p>
      <p><?= htmlspecialchars($bus['from_city'] ?? '') ?> → <?= htmlspecialchars($bus['to_city'] ?? '') ?></p>
      <p><strong>Departure Time:</strong> <?= htmlspecialchars($bus['departure_time'] ?? 'N/A') ?></p>

      <h4 class="mt-4">Passenger Details</h4>
      <p><strong>Seat Number:</strong> <?= htmlspecialchars($booking['seat_no']) ?></p>
      <p><strong>Travel Date:</strong> <?= htmlspecialchars($booking['travel_date']) ?></p>
      <p><strong>CNIC:</strong> <?= htmlspecialchars($booking['cnic'] ?? 'Not Provided') ?></p>
      <p><strong>Phone Number:</strong> <?= htmlspecialchars($booking['phone'] ?? 'Not Provided') ?></p>

      <h4 class="mt-4">Payment</h4>
      <p><strong>Status:</strong> <?= htmlspecialchars($booking['payment_status']) ?></p>
      <p><strong>Fare:</strong> PKR <?= number_format($bus['price'] ?? 0) ?></p>

      <div class="alert alert-info mt-3">
        <strong>Note:</strong> Please arrive 30 minutes before departure and bring your CNIC for verification.
      </div>

      <a href="bus_list.php" class="btn btn-primary mt-3">🚌 Back to Buses</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
