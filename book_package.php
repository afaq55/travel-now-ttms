<?php
session_start();
require 'db.php';
require 'functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->execute([$id]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    header("Location: packages.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_name   = trim($_POST['user_name']);
    $email       = trim($_POST['email']);
    $phone       = trim($_POST['phone']);
    $pax         = (int)$_POST['pax'];
    $travel_date = $_POST['travel_date'];

    $errors = [];

    if (empty($user_name)) {
        $errors[] = "Name is required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }

    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }

    if ($pax < 1) {
        $errors[] = "Passengers must be at least 1.";
    }

    if (empty($travel_date)) {
        $errors[] = "Travel date is required.";
    }

    if (empty($errors)) {

        $total_price = $package['price'] * $pax;
        $user_id = $_SESSION['user_id'] ?? null;

        $stmt = $pdo->prepare("
            INSERT INTO bookings
            (
                user_id,
                user_name,
                email,
                phone,
                package_id,
                pax,
                travel_date,
                total_price,
                status,
                created_at
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', NOW()
            )
        ");

        $stmt->execute([
            $user_id,
            $user_name,
            $email,
            $phone,
            $id,
            $pax,
            $travel_date,
            $total_price
        ]);

        header("Location: booking_success.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Book Package</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
</head>
<body>

<?php include 'partials/navbar.php'; ?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <div class="card shadow border-0">

                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Book Package</h3>
                </div>

                <div class="card-body">

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="row mb-4">

                        <div class="col-md-4">
                            <img src="uploads/<?= htmlspecialchars($package['image']) ?>"
                                 class="img-fluid rounded"
                                 alt="<?= htmlspecialchars($package['title']) ?>">
                        </div>

                        <div class="col-md-8">

                            <h4><?= htmlspecialchars($package['title']) ?></h4>

                            <p>
                                <?= htmlspecialchars($package['short_desc']) ?>
                            </p>

                            <p>
                                <strong>Duration:</strong>
                                <?= htmlspecialchars($package['duration']) ?> Days
                            </p>

                            <h4 class="text-primary">
                                PKR <?= number_format($package['price']) ?>
                            </h4>

                        </div>

                    </div>

                    <form method="post">

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text"
                                   name="user_name"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text"
                                   name="phone"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Passengers</label>
                            <input type="number"
                                   name="pax"
                                   min="1"
                                   value="1"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Travel Date</label>
                            <input type="date"
                                   name="travel_date"
                                   class="form-control"
                                   required>
                        </div>

                        <button type="submit"
                                class="btn btn-primary w-100">
                            Confirm Booking
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include 'partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>