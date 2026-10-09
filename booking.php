<?php
session_start();
require 'db.php';
require 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

/* =========================
   BUS BOOKINGS
========================= */

$stmt = $pdo->prepare("
    SELECT
        bb.*,
        b.bus_name,
        b.bus_type,
        b.from_city,
        b.to_city,
        b.departure_time,
        b.arrival_time,
        b.price
    FROM bus_bookings bb
    LEFT JOIN buses b ON bb.bus_id = b.id
    WHERE bb.user_id = ?
    ORDER BY bb.id DESC
");

$stmt->execute([$user_id]);
$busBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   PACKAGE BOOKINGS
========================= */

$stmt = $pdo->prepare("
    SELECT
        bk.*,
        p.title,
        p.duration
    FROM bookings bk
    LEFT JOIN packages p ON bk.package_id = p.id
    WHERE bk.user_id = ?
    ORDER BY bk.id DESC
");

$stmt->execute([$user_id]);
$packageBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Contact Us — TravelNow</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (if navbar uses them) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Your custom stylesheet -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'partials/navbar.php'; ?>

<div class="container py-5">

    <h2 class="mb-4">My Bookings</h2>

    <!-- =========================
         BUS BOOKINGS
    ========================== -->

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">My Bus Bookings</h5>
        </div>

        <div class="card-body">

            <?php if (empty($busBookings)): ?>

                <div class="alert alert-info">
                    No bus bookings found.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Bus</th>
                            <th>Type</th>
                            <th>Route</th>
                            <th>Seat</th>
                            <th>CNIC</th>
                            <th>Phone</th>
                            <th>Travel Date</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Invoice</th>
                        </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($busBookings as $booking): ?>

                        <tr>

                            <td>#<?= $booking['id'] ?></td>

                            <td><?= htmlspecialchars($booking['bus_name']) ?></td>

                            <td><?= htmlspecialchars($booking['bus_type']) ?></td>

                            <td>
                                <?= htmlspecialchars($booking['from_city']) ?>
                                →
                                <?= htmlspecialchars($booking['to_city']) ?>
                            </td>

                            <td><?= htmlspecialchars($booking['seat_no']) ?></td>

                            <td><?= htmlspecialchars($booking['cnic']) ?></td>

                            <td><?= htmlspecialchars($booking['phone']) ?></td>

                            <td><?= htmlspecialchars($booking['travel_date']) ?></td>

                            <td>PKR <?= number_format($booking['price']) ?></td>

                            <td>
                                <span class="badge bg-success">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </td>

                            <td>
                                <span class="badge bg-primary">
                                    <?= ucfirst($booking['payment_status']) ?>
                                </span>
                            </td>

                            <td>
                                <a href="invoice.php?id=<?= $booking['id'] ?>"
                                   class="btn btn-sm btn-primary">
                                    Download
                                </a>
                            </td>

                        </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>
    </div>

    <!-- =========================
         PACKAGE BOOKINGS
    ========================== -->

    <div class="card shadow-sm border-0">

        <div class="card-header bg-success text-white">
            <h5 class="mb-0">My Package Bookings</h5>
        </div>

        <div class="card-body">

            <?php if (empty($packageBookings)): ?>

                <div class="alert alert-info">
                    No package bookings found.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Package</th>
                            <th>Duration</th>
                            <th>Passengers</th>
                            <th>Travel Date</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Invoice</th>
                        </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($packageBookings as $booking): ?>

                        <tr>

                            <td>#<?= $booking['id'] ?></td>

                            <td><?= htmlspecialchars($booking['title']) ?></td>

                            <td><?= htmlspecialchars($booking['duration']) ?> Days</td>

                            <td><?= htmlspecialchars($booking['pax']) ?></td>

                            <td><?= htmlspecialchars($booking['travel_date']) ?></td>

                            <td>
                                PKR <?= number_format($booking['total_price']) ?>
                            </td>

                            <td>
                                <span class="badge bg-success">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </td>

                            <td>
                                <a href="package_invoice.php?id=<?= $booking['id'] ?>"
                                   class="btn btn-sm btn-success">
                                    Download
                                </a>
                            </td>

                        </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php include 'partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>