<?php
session_start();
require 'db.php';
require 'functions.php';

if (!isset($_GET['id'])) {
    die('Invalid Invoice ID');
}

$booking_id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT
        bk.*,
        p.title,
        p.duration,
        p.price,
        p.short_desc
    FROM bookings bk
    LEFT JOIN packages p ON bk.package_id = p.id
    WHERE bk.id = ?
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
<title>Package Invoice #<?= $invoice['id'] ?></title>

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
        background:#fff;
    }

    .invoice-box{
        margin:0;
        box-shadow:none;
        max-width:100%;
    }
}
</style>
</head>
<body>

<?php include 'partials/navbar.php'; ?>

<div class="container">

    <div class="invoice-box">

        <div class="d-flex justify-content-between mb-4">

            <div>
                <h2>TravelNow</h2>
                <p class="text-muted">Package Booking Invoice</p>
            </div>

            <div class="text-end">
                <h4>Invoice</h4>
                <strong>#<?= $invoice['id'] ?></strong>
            </div>

        </div>

        <hr>

        <div class="row mb-4">

            <div class="col-md-6">

                <h5>Customer Details</h5>

                <p>
                    <strong>Name:</strong>
                    <?= htmlspecialchars($invoice['user_name']) ?>
                </p>

                <p>
                    <strong>Email:</strong>
                    <?= htmlspecialchars($invoice['email']) ?>
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

                <p>
                    <strong>Passengers:</strong>
                    <?= htmlspecialchars($invoice['pax']) ?>
                </p>

            </div>

        </div>

        <table class="table table-bordered">

            <thead class="table-dark">
                <tr>
                    <th>Package</th>
                    <th>Description</th>
                    <th>Duration</th>
                    <th>Price Per Person</th>
                    <th>Passengers</th>
                    <th>Total</th>
                </tr>
            </thead>

            <tbody>

                <tr>

                    <td>
                        <?= htmlspecialchars($invoice['title']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($invoice['short_desc']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($invoice['duration']) ?> Days
                    </td>

                    <td>
                        PKR <?= number_format($invoice['price']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($invoice['pax']) ?>
                    </td>

                    <td>
                        PKR <?= number_format($invoice['total_price']) ?>
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

            </div>

            <div class="col-md-6 text-end">

                <h3>
                    Total Amount:
                    PKR <?= number_format($invoice['total_price']) ?>
                </h3>

            </div>

        </div>

        <hr>

        <div class="text-center text-muted">
            Thank you for choosing TravelNow.
            We wish you a wonderful journey.
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