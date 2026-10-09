<?php
session_start();
require 'db.php';
require 'functions.php';

/* ==========================
   AGENT AUTH
========================== */

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_role = $_SESSION['user_role'] ?? '';

if (strtolower(trim($user_role)) !== 'agent') {
    die('Access denied.');
}

$agent_id = $_SESSION['user_id'];

/* ==========================
   FLASH MESSAGE
========================== */

function flash($key, $msg = null)
{
    if ($msg === null) {
        if (!empty($_SESSION['flash'][$key])) {
            $m = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $m;
        }
        return null;
    }

    $_SESSION['flash'][$key] = $msg;
}

/* ==========================
   USER DATA
========================== */

$stmt = $pdo->prepare("
    SELECT id,name,email,role
    FROM users
    WHERE id=?
");

$stmt->execute([$agent_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/* ==========================
   ADD BUS
========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'add_bus') {

        $bus_name = trim($_POST['bus_name'] ?? '');
        $bus_type = trim($_POST['bus_type'] ?? '');
        $from_city = trim($_POST['from_city'] ?? '');
        $to_city = trim($_POST['to_city'] ?? '');
        $departure_time = $_POST['departure_time'] ?? '';
        $total_seats = (int)($_POST['total_seats'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);

        if (
            empty($bus_name) ||
            empty($from_city) ||
            empty($to_city) ||
            $total_seats <= 0 ||
            $price <= 0
        ) {
            flash('error', 'Fill all required fields.');
            header('Location: agent.php');
            exit;
        }

        $imgName = null;

        if (!empty($_FILES['image']['tmp_name'])) {

            $uploadDir = __DIR__ . '/uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

            $imgName = uniqid('bus_', true) . '.' . $ext;

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $uploadDir . $imgName
            );
        }

        $stmt = $pdo->prepare("
            INSERT INTO buses
            (
                agent_id,
                bus_name,
                bus_type,
                from_city,
                to_city,
                departure_time,
                total_seats,
                available_seats,
                price,
                image
            )
            VALUES
            (
                ?,?,?,?,?,?,?,?,?,?
            )
        ");

        $stmt->execute([
            $agent_id,
            $bus_name,
            $bus_type,
            $from_city,
            $to_city,
            $departure_time,
            $total_seats,
            $total_seats,
            $price,
            $imgName
        ]);

        flash('success', 'Bus added successfully.');

        header('Location: agent.php');
        exit;
    }

    /* ==========================
       DELETE OWN BUS
    ========================== */

    if ($action === 'delete_bus') {

        $bus_id = (int)($_POST['bus_id'] ?? 0);

        $stmt = $pdo->prepare("
            DELETE FROM buses
            WHERE id=?
            AND agent_id=?
        ");

        $stmt->execute([
            $bus_id,
            $agent_id
        ]);

        flash('success', 'Bus deleted.');

        header('Location: agent.php');
        exit;
    }
}

/* ==========================
   AGENT BUSES
========================== */

$stmt = $pdo->prepare("
    SELECT *
    FROM buses
    WHERE agent_id=?
    ORDER BY id DESC
");

$stmt->execute([$agent_id]);

$buses = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==========================
   BUS BOOKINGS
========================== */

$stmt = $pdo->prepare("
SELECT
    bb.id AS booking_id,
    u.name AS customer_name,
    u.email,
    bs.bus_name,
    bs.from_city,
    bs.to_city,
    bb.seat_no,
    bb.travel_date,
    bb.payment_status
FROM bus_bookings bb
INNER JOIN users u
ON bb.user_id=u.id
INNER JOIN buses bs
ON bb.bus_id=bs.id
WHERE bs.agent_id=?
ORDER BY bb.id DESC
");

$stmt->execute([$agent_id]);

$bus_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_buses = count($buses);
$total_bookings = count($bus_bookings);

?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Agent Panel</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="css/style.css">
</head>

<body>

<?php include 'partials/navbar.php'; ?>

<div class="container my-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>
            <i class="fas fa-bus text-primary"></i>
            Agent Panel
        </h2>

        <span class="badge bg-primary">
            <?= htmlspecialchars($user['name']) ?>
        </span>

    </div>

    <?php if ($m = flash('success')): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($m) ?>
        </div>
    <?php endif; ?>

    <?php if ($m = flash('error')): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($m) ?>
        </div>
    <?php endif; ?>

    <div class="row">

        <div class="col-lg-4">

            <div class="card shadow-sm mb-4">

                <div class="card-header">
                    Add Bus
                </div>

                <div class="card-body">

                    <form method="POST" enctype="multipart/form-data">

                        <input type="hidden"
                               name="action"
                               value="add_bus">

                        <div class="mb-3">
                            <input type="text"
                                   name="bus_name"
                                   class="form-control"
                                   placeholder="Bus Name"
                                   required>
                        </div>

                        <div class="mb-3">
                            <input type="text"
                                   name="bus_type"
                                   class="form-control"
                                   placeholder="AC / Luxury"
                                   required>
                        </div>

                        <div class="mb-3">
                            <input type="text"
                                   name="from_city"
                                   class="form-control"
                                   placeholder="From City"
                                   required>
                        </div>

                        <div class="mb-3">
                            <input type="text"
                                   name="to_city"
                                   class="form-control"
                                   placeholder="To City"
                                   required>
                        </div>

                        <div class="mb-3">
                            <input type="datetime-local"
                                   name="departure_time"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="mb-3">
                            <input type="number"
                                   name="total_seats"
                                   class="form-control"
                                   placeholder="Seats"
                                   required>
                        </div>

                        <div class="mb-3">
                            <input type="number"
                                   name="price"
                                   class="form-control"
                                   placeholder="Ticket Price"
                                   required>
                        </div>

                        <div class="mb-3">
                            <input type="file"
                                   name="image"
                                   class="form-control">
                        </div>

                        <button class="btn btn-primary w-100">
                            Add Bus
                        </button>

                    </form>

                </div>

            </div>

            <div class="card shadow-sm">

                <div class="card-body text-center">

                    <h4><?= $total_buses ?></h4>
                    <p>Total Buses</p>

                    <hr>

                    <h4><?= $total_bookings ?></h4>
                    <p>Total Bookings</p>

                </div>

            </div>

        </div>

        <div class="col-lg-8">

            <div class="card shadow-sm mb-4">

                <div class="card-header">
                    My Buses
                </div>

                <div class="table-responsive">

                    <table class="table table-bordered">

                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Bus</th>
                            <th>Route</th>
                            <th>Seats</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($buses as $bus): ?>

                            <tr>

                                <td><?= $bus['id'] ?></td>

                                <td><?= htmlspecialchars($bus['bus_name']) ?></td>

                                <td>
                                    <?= htmlspecialchars($bus['from_city']) ?>
                                    →
                                    <?= htmlspecialchars($bus['to_city']) ?>
                                </td>

                                <td>
                                    <?= $bus['available_seats'] ?>
                                    /
                                    <?= $bus['total_seats'] ?>
                                </td>

                                <td>
                                    PKR <?= number_format($bus['price']) ?>
                                </td>

                                <td>

                                    <form method="POST">

                                        <input type="hidden"
                                               name="action"
                                               value="delete_bus">

                                        <input type="hidden"
                                               name="bus_id"
                                               value="<?= $bus['id'] ?>">

                                        <button class="btn btn-danger btn-sm">
                                            Delete
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

            <div class="card shadow-sm">

                <div class="card-header">
                    Bus Bookings
                </div>

                <div class="table-responsive">

                    <table class="table table-bordered">

                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Bus</th>
                            <th>Seat</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($bus_bookings as $booking): ?>

                            <tr>

                                <td><?= $booking['booking_id'] ?></td>

                                <td><?= htmlspecialchars($booking['customer_name']) ?></td>

                                <td><?= htmlspecialchars($booking['email']) ?></td>

                                <td><?= htmlspecialchars($booking['bus_name']) ?></td>

                                <td><?= htmlspecialchars($booking['seat_no']) ?></td>

                                <td><?= htmlspecialchars($booking['travel_date']) ?></td>

                                <td>
                                    <?= htmlspecialchars($booking['payment_status']) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>