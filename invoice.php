<?php
session_start();
require 'db.php';
require 'functions.php';

if (!isset($_GET['id'])) {
    die('Invalid Invoice ID');
}
include 'partials/navbar.php'; 
$booking_id = (int)$_GET['id'];

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
    WHERE bb.id = ?
");

$stmt->execute([$booking_id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    die('Invoice not found.');
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Invoice #<?= $invoice['id'] ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#f8f9fa;
}

.invoice-box{
    max-width:900px;
    margin:40px auto;
    background:#fff;
    padding:40px;
    border-radius:12px;
    box-shadow:0 0 15px rgba(0,0,0,.1);
}

@media print{
    .no-print{
        display:none !important;
    }

    body{
        background:white;
    }

    .invoice-box{
        box-shadow:none;
        margin:0;
        max-width:100%;
    }
}
</style>

</head>
<body>

<div class="container">

    <div class="invoice-box">

        <div class="d-flex justify-content-between mb-4">

            <div>
                <h2>TravelNow</h2>
                <p class="text-muted">
                    Bus Ticket Invoice
                </p>
            </div>

            <div class="text-end">
                <h4>Invoice</h4>
                <strong>#<?= $invoice['id'] ?></strong>
            </div>

        </div>

        <hr>

        <div class="row mb-4">

            <div class="col-md-6">

                <h5>Passenger Details</h5>

                <p>
                    <strong>CNIC:</strong>
                    <?= htmlspecialchars($invoice['cnic']) ?>
                </p>

                <p>
                    <strong>Phone:</strong>
                    <?= htmlspecialchars($invoice['phone']) ?>
                </p>

            </div>

            <div class="col-md-6 text-md-end">

                <h5>Booking Details</h5>

                <p>
                    <strong>Travel Date:</strong>
                    <?= htmlspecialchars($invoice['travel_date']) ?>
                </p>

                <p>
                    <strong>Booking Date:</strong>
                    <?= htmlspecialchars($invoice['created_at']) ?>
                </p>

            </div>

        </div>

        <table class="table table-bordered">

            <thead class="table-dark">
                <tr>
                    <th>Bus</th>
                    <th>Type</th>
                    <th>Route</th>
                    <th>Seat</th>
                    <th>Departure</th>
                    <th>Price</th>
                </tr>
            </thead>

            <tbody>

                <tr>

                    <td>
                        <?= htmlspecialchars($invoice['bus_name']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($invoice['bus_type']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($invoice['from_city']) ?>
                        →
                        <?= htmlspecialchars($invoice['to_city']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($invoice['seat_no']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($invoice['departure_time']) ?>
                    </td>

                    <td>
                        PKR <?= number_format($invoice['price']) ?>
                    </td>

                </tr>

            </tbody>

        </table>

        <div class="row mt-4">

            <div class="col-md-6">

                <p>
                    <strong>Status:</strong>
                    <?= ucfirst($invoice['status']) ?>
                </p>

                <p>
                    <strong>Payment:</strong>
                    <?= ucfirst($invoice['payment_status']) ?>
                </p>

            </div>

            <div class="col-md-6 text-end">

                <h4>
                    Total:
                    PKR <?= number_format($invoice['price']) ?>
                </h4>

            </div>

        </div>

        <hr>

        <div class="text-center text-muted">
            Thank you for choosing TravelNow.
            Have a safe journey!
        </div>

        <div class="text-center mt-4 no-print">

            <button onclick="window.print()" class="btn btn-primary">
                Print / Download Invoice
            </button>

            <a href="booking.php" class="btn btn-secondary">
                Back to Bookings
            </a>

        </div>

    </div>

</div>

</body>
</html>