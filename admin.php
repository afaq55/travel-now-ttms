<?php
session_start();
require 'db.php';
require 'functions.php';

// -------------------- Admin check --------------------
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_role = $_SESSION['user_role'] ?? '';
if (strtolower(trim($user_role)) !== 'admin') {
    echo 'Access denied.';
    exit;
}

// Fetch user for navbar
$user = null;
if (isset($_SESSION['user_id']) && isset($pdo)) {
    $stmt = $pdo->prepare('SELECT id, name, role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} else {
    $user = [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'user'
    ];
}

// Simple flash helpers
function flash($key, $msg = null) {
    if ($msg === null) {
        if (!empty($_SESSION['flash'][$key])) {
            $m = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $m;
        }
        return null;
    } else {
        $_SESSION['flash'][$key] = $msg;
    }
}

// -------------------- Handle POST actions --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- Add package ---
    if ($action === 'add_package') {
        $title = trim($_POST['title'] ?? '');
        $short = trim($_POST['short_desc'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $price = (int)($_POST['price'] ?? 0);
        $duration = (int)($_POST['duration'] ?? 0);

        if ($title === '' || $short === '' || $desc === '' || $price <= 0) {
            flash('error', 'Please fill all required fields with valid values.');
            header('Location: admin.php');
            exit;
        }

        $imgName = null;
        if (!empty($_FILES['image']['tmp_name'])) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
            $imgName = time() . '-' . basename($_FILES['image']['name']);
            $target = $uploadDir . $imgName;
            move_uploaded_file($_FILES['image']['tmp_name'], $target);
        }

        $stmt = $pdo->prepare('INSERT INTO packages (title, short_desc, description, price, duration, image, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$title, $short, $desc, $price, $duration, $imgName]);
        flash('success', 'Package added successfully.');
        header('Location: admin.php');
        exit;
    }

    // --- Edit package ---
    if ($action === 'edit_package') {
        $package_id = (int)($_POST['package_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $short = trim($_POST['short_desc'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $price = (int)($_POST['price'] ?? 0);
        $duration = (int)($_POST['duration'] ?? 0);

        if ($title === '' || $short === '' || $desc === '' || $price <= 0 || $package_id <= 0) {
            flash('error', 'Please fill all required fields with valid values.');
            header('Location: admin.php');
            exit;
        }

        // Check if new image is uploaded
        $imgName = null;
        if (!empty($_FILES['image']['tmp_name'])) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
            $imgName = time() . '-' . basename($_FILES['image']['name']);
            $target = $uploadDir . $imgName;
            move_uploaded_file($_FILES['image']['tmp_name'], $target);
            
            // Update with new image
            $stmt = $pdo->prepare('UPDATE packages SET title = ?, short_desc = ?, description = ?, price = ?, duration = ?, image = ? WHERE id = ?');
            $stmt->execute([$title, $short, $desc, $price, $duration, $imgName, $package_id]);
        } else {
            // Update without changing image
            $stmt = $pdo->prepare('UPDATE packages SET title = ?, short_desc = ?, description = ?, price = ?, duration = ? WHERE id = ?');
            $stmt->execute([$title, $short, $desc, $price, $duration, $package_id]);
        }

        flash('success', 'Package updated successfully.');
        header('Location: admin.php');
        exit;
    }

    // --- Delete package ---
    if ($action === 'delete_package') {
        $packageId = (int)($_POST['package_id'] ?? 0);
        if ($packageId > 0) {
            $stmt = $pdo->prepare('DELETE FROM packages WHERE id = ?');
            $stmt->execute([$packageId]);
            flash('success', 'Package deleted.');
        }
        header('Location: admin.php');
        exit;
    }

    // --- Add bus ---
    if ($action === 'add_bus') {
        $bus_name = trim($_POST['bus_name'] ?? '');
        $bus_type = trim($_POST['bus_type'] ?? '');
        $from_city = trim($_POST['from_city'] ?? '');
        $to_city = trim($_POST['to_city'] ?? '');
        $departure_time = $_POST['departure_time'] ?? '';
        $total_seats = (int)($_POST['total_seats'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);

        if ($bus_name === '' || $from_city === '' || $to_city === '' || $total_seats <= 0 || $price <= 0) {
            flash('error', 'Please fill all required bus fields.');
            header('Location: admin.php');
            exit;
        }

        $imgName = null;
        if (!empty($_FILES['image']['tmp_name'])) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
            $imgName = time() . '-' . basename($_FILES['image']['name']);
            $target = $uploadDir . $imgName;
            move_uploaded_file($_FILES['image']['tmp_name'], $target);
        }

        $stmt = $pdo->prepare('INSERT INTO buses (bus_name, bus_type, from_city, to_city, departure_time, total_seats, available_seats, price, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$bus_name, $bus_type, $from_city, $to_city, $departure_time, $total_seats, $total_seats, $price, $imgName]);

        flash('success', 'Bus added successfully.');
        header('Location: admin.php');
        exit;
    }

    // --- Delete bus ---
    if ($action === 'delete_bus') {
        $busId = (int)($_POST['bus_id'] ?? 0);
        if ($busId > 0) {
            $stmt = $pdo->prepare('DELETE FROM buses WHERE id = ?');
            $stmt->execute([$busId]);
            flash('success', 'Bus deleted.');
        }
        header('Location: admin.php');
        exit;
    }

    // --- Delete booking ---
    if ($action === 'delete_booking') {
        $bookingId = (int)($_POST['booking_id'] ?? 0);
        if ($bookingId > 0) {
            $stmt = $pdo->prepare('DELETE FROM bookings WHERE id = ?');
            $stmt->execute([$bookingId]);
            flash('success', 'Booking deleted.');
        }
        header('Location: admin.php');
        exit;
    }

    // ========== AGENT MANAGEMENT (INJECTED MODULE) ==========
    // --- Add Agent ---
    if ($action === 'add_agent') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($name === '' || $email === '' || $password === '') {
            flash('error', 'All agent fields are required.');
            header('Location: admin.php');
            exit;
        }

        $check = $pdo->prepare("SELECT id FROM users WHERE email=?");
        $check->execute([$email]);

        if ($check->fetch()) {
            flash('error', 'Email already exists.');
            header('Location: admin.php');
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users
            (name, email, password, role)
            VALUES
            (?, ?, ?, 'agent')
        ");

        $stmt->execute([$name, $email, $hashedPassword]);

        flash('success', 'Agent created successfully.');
        header('Location: admin.php');
        exit;
    }

    // --- Delete Agent ---
    if ($action === 'delete_agent') {
        $agent_id = (int)($_POST['agent_id'] ?? 0);

        $stmt = $pdo->prepare("
            DELETE FROM users
            WHERE id = ?
            AND role = 'agent'
        ");
        $stmt->execute([$agent_id]);

        flash('success', 'Agent deleted.');
        header('Location: admin.php');
        exit;
    }
}

// -------------------- Fetch data for display --------------------
$packages = $pdo->query('SELECT * FROM packages ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query('SELECT id, name, email, role FROM users ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
$buses = $pdo->query('SELECT * FROM buses ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);

// Fetch agents (role='agent')
$agents = $pdo->query("
    SELECT id, name, email
    FROM users
    WHERE role = 'agent'
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Existing package bookings
$bookings = $pdo->query("
    SELECT b.id, b.user_name, b.email, b.phone, b.package_id, b.status, b.created_at, p.title AS package_title
    FROM bookings b
    LEFT JOIN packages p ON b.package_id = p.id
    ORDER BY b.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Bus bookings
$bus_bookings = $pdo->query("
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
    JOIN users u ON bb.user_id = u.id
    JOIN buses bs ON bb.bus_id = bs.id
    ORDER BY bb.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Dashboard counts
$total_packages = (int)$pdo->query('SELECT COUNT(*) FROM packages')->fetchColumn();
$total_bookings = (int)$pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
$total_buses = (int)$pdo->query('SELECT COUNT(*) FROM buses')->fetchColumn();
$total_bus_bookings = (int)$pdo->query('SELECT COUNT(*) FROM bus_bookings')->fetchColumn();
$total_users = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$total_agents = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'agent'")->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel — TravelNow</title>

    <!-- Google Fonts + Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- External CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">

<!-- === EXTERNAL GLASS NAVBAR === -->
<?php require_once 'partials/navbar.php'; ?>



<main class="container my-5">
    <!-- Admin Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">
            <i class="fas fa-shield-alt me-2 text-primary"></i>
            Admin Panel
        </h3>
        <span class="badge bg-primary">Administrator</span>
    </div>

    <!-- Flash Messages -->
    <?php if ($m = flash('success')): ?>
        <div class="alert alert-success mb-4"><?= htmlspecialchars($m) ?></div>
    <?php endif; ?>
    <?php if ($m = flash('error')): ?>
        <div class="alert alert-danger mb-4"><?= htmlspecialchars($m) ?></div>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- SECTION 1: DASHBOARD STATISTICS -->
    <!-- ============================================ -->
    <div class="row g-4 mb-5">
        <div class="col-12">
            <h5 class="fw-bold mb-3 section-title">
                <i class="fas fa-chart-line text-primary"></i> Dashboard Overview
            </h5>
        </div>
        
        <!-- Stats Cards -->
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card stat-card p-3 text-center">
                <i class="fas fa-box fa-2x text-primary mb-2"></i>
                <h3 class="fw-bold"><?= $total_packages ?></h3>
                <p class="text-muted small mb-0">Packages</p>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card stat-card p-3 text-center">
                <i class="fas fa-bus fa-2x text-success mb-2"></i>
                <h3 class="fw-bold"><?= $total_buses ?></h3>
                <p class="text-muted small mb-0">Buses</p>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card stat-card p-3 text-center">
                <i class="fas fa-ticket-alt fa-2x text-warning mb-2"></i>
                <h3 class="fw-bold"><?= $total_bookings ?></h3>
                <p class="text-muted small mb-0">Pkg Bookings</p>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card stat-card p-3 text-center">
                <i class="fas fa-chair fa-2x text-info mb-2"></i>
                <h3 class="fw-bold"><?= $total_bus_bookings ?></h3>
                <p class="text-muted small mb-0">Bus Bookings</p>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card stat-card p-3 text-center">
                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                <h3 class="fw-bold"><?= $total_users ?></h3>
                <p class="text-muted small mb-0">Total Users</p>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card stat-card p-3 text-center">
                <i class="fas fa-user-tie fa-2x text-purple mb-2"></i>
                <h3 class="fw-bold"><?= $total_agents ?></h3>
                <p class="text-muted small mb-0">Agents</p>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- SECTION 2: ADD FORMS & PACKAGES TABLE -->
    <!-- ============================================ -->
    <div class="row g-4">
        <!-- Left Column: Forms -->
        <div class="col-lg-4">
            
            <!-- Add Package Form -->
            <div class="card p-4 mb-4">
                <h5 class="fw-bold mb-3 form-title">
                    <i class="fas fa-plus-circle text-primary me-2"></i>Add Package
                </h5>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_package">
                    <div class="mb-3">
                        <input class="form-control" name="title" placeholder="Title" required>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="short_desc" placeholder="Short description" required>
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control" name="description" placeholder="Full description" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="price" type="number" placeholder="Price (PKR)" required>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="duration" type="number" placeholder="Duration (days)">
                    </div>
                    <div class="mb-3">
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <button class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Add Package
                    </button>
                </form>
            </div>

            <!-- Add Bus Form -->
            <div class="card p-4 mb-4">
                <h5 class="fw-bold mb-3 form-title">
                    <i class="fas fa-bus text-success me-2"></i>Add Bus
                </h5>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_bus">
                    <div class="mb-3">
                        <input class="form-control" name="bus_name" placeholder="Bus Name" required>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="bus_type" placeholder="Bus Type (AC / Non-AC)" required>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="from_city" placeholder="From City" required>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="to_city" placeholder="To City" required>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="departure_time" type="datetime-local" required>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="total_seats" type="number" placeholder="Total Seats" required>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="price" type="number" placeholder="Ticket Price (PKR)" required>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <button class="btn btn-success w-100">
                        <i class="fas fa-plus-circle me-2"></i>Add Bus
                    </button>
                </form>
            </div>

            <!-- Add Agent Form -->
            <div class="card p-4 mb-4">
                <h5 class="fw-bold mb-3 form-title">
                    <i class="fas fa-user-tie text-purple me-2"></i>Create Agent
                </h5>
                <form method="post">
                    <input type="hidden" name="action" value="add_agent">
                    <div class="mb-3">
                        <input class="form-control" type="text" name="name" placeholder="Agent Name" required>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" type="email" name="email" placeholder="Agent Email" required>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" type="password" name="password" placeholder="Password" required>
                    </div>
                    <button class="btn btn-purple w-100">
                        <i class="fas fa-user-plus me-2"></i>Create Agent
                    </button>
                </form>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- SECTION 3: DATA TABLES (RIGHT COLUMN) -->
        <!-- ============================================ -->
        <div class="col-lg-8">
            
            <!-- Packages Table with Edit & Delete -->
            <div class="card p-4 mb-4">
                <h5 class="fw-bold mb-3 table-title">
                    <i class="fas fa-box text-primary me-2"></i>Manage Packages
                </h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($packages)): ?>
                                <tr><td colspan="6" class="text-center">No packages found.</td></tr>
                            <?php else: foreach ($packages as $package): ?>
                                <tr>
                                    <td><?= $package['id'] ?></td>
                                    <td>
                                        <?php if ($package['image']): ?>
                                            <img src="uploads/<?= htmlspecialchars($package['image']) ?>" width="50" height="50" style="object-fit: cover; border-radius: 5px;">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($package['title']) ?></td>
                                    <td>PKR <?= number_format($package['price']) ?></td>
                                    <td><?= $package['duration'] ?> days</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <!-- Edit Button -->
                                            <button class="btn btn-warning btn-sm edit-package-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editPackageModal"
                                                    data-id="<?= $package['id'] ?>"
                                                    data-title="<?= htmlspecialchars($package['title']) ?>"
                                                    data-short="<?= htmlspecialchars($package['short_desc']) ?>"
                                                    data-description="<?= htmlspecialchars($package['description']) ?>"
                                                    data-price="<?= $package['price'] ?>"
                                                    data-duration="<?= $package['duration'] ?>"
                                                    data-image="<?= htmlspecialchars($package['image']) ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            
                                            <!-- Delete Button -->
                                            <form method="post" onsubmit="return confirm('Delete this package?');">
                                                <input type="hidden" name="action" value="delete_package">
                                                <input type="hidden" name="package_id" value="<?= $package['id'] ?>">
                                                <button class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Buses Table -->
            <div class="card p-4 mb-4">
                <h5 class="fw-bold mb-3 table-title">
                    <i class="fas fa-bus text-success me-2"></i>Manage Buses
                </h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Route</th>
                                <th>Type</th>
                                <th>Seats</th>
                                <th>Price</th>
                                <th>Departure</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($buses)): ?>
                                <tr><td colspan="8" class="text-center">No buses found.</td></tr>
                            <?php else: foreach ($buses as $bus): ?>
                                <tr>
                                    <td><?= $bus['id'] ?></td>
                                    <td><?= htmlspecialchars($bus['bus_name']) ?></td>
                                    <td><?= htmlspecialchars($bus['from_city']) ?> → <?= htmlspecialchars($bus['to_city']) ?></td>
                                    <td><?= htmlspecialchars($bus['bus_type']) ?></td>
                                    <td><?= $bus['available_seats'] ?>/<?= $bus['total_seats'] ?></td>
                                    <td>PKR <?= number_format($bus['price']) ?></td>
                                    <td><?= htmlspecialchars($bus['departure_time']) ?></td>
                                    <td>
                                        <form method="post" onsubmit="return confirm('Delete this bus?');">
                                            <input type="hidden" name="action" value="delete_bus">
                                            <input type="hidden" name="bus_id" value="<?= $bus['id'] ?>">
                                            <button class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Agents Table -->
            <div class="card p-4 mb-4">
                <h5 class="fw-bold mb-3 table-title">
                    <i class="fas fa-user-tie text-purple me-2"></i>Manage Agents
                </h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($agents)): ?>
                                <tr><td colspan="4" class="text-center">No agents yet.</td></tr>
                            <?php else: foreach ($agents as $agent): ?>
                                <tr>
                                    <td><?= $agent['id'] ?></td>
                                    <td><?= htmlspecialchars($agent['name']) ?></td>
                                    <td><?= htmlspecialchars($agent['email']) ?></td>
                                    <td>
                                        <form method="post" onsubmit="return confirm('Delete this agent?');">
                                            <input type="hidden" name="action" value="delete_agent">
                                            <input type="hidden" name="agent_id" value="<?= $agent['id'] ?>">
                                            <button class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bus Bookings Table -->
            <div class="card p-4">
                <h5 class="fw-bold mb-3 table-title">
                    <i class="fas fa-chair text-info me-2"></i>Bus Bookings
                </h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Bus</th>
                                <th>Route</th>
                                <th>Seat</th>
                                <th>Travel Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bus_bookings)): ?>
                                <tr><td colspan="8" class="text-center">No bus bookings yet.</td></tr>
                            <?php else: foreach ($bus_bookings as $b): ?>
                                <tr>
                                    <td><?= $b['booking_id'] ?></td>
                                    <td><?= htmlspecialchars($b['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($b['email']) ?></td>
                                    <td><?= htmlspecialchars($b['bus_name']) ?></td>
                                    <td><?= htmlspecialchars($b['from_city']) ?> → <?= htmlspecialchars($b['to_city']) ?></td>
                                    <td><?= htmlspecialchars($b['seat_no']) ?></td>
                                    <td><?= htmlspecialchars($b['travel_date']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $b['payment_status'] === 'confirmed' ? 'success' : 'warning' ?>">
                                            <?= htmlspecialchars($b['payment_status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Single Edit Package Modal (Outside the loop) -->
<div class="modal fade" id="editPackageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit text-warning me-2"></i>Edit Package
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" enctype="multipart/form-data" id="editPackageForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_package">
                    <input type="hidden" name="package_id" id="edit_package_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title</label>
                        <input class="form-control" name="title" id="edit_title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Short Description</label>
                        <input class="form-control" name="short_desc" id="edit_short_desc" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Price (PKR)</label>
                        <input class="form-control" name="price" type="number" id="edit_price" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Duration (days)</label>
                        <input class="form-control" name="duration" type="number" id="edit_duration">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Current Image</label>
                        <div id="edit_current_image" class="mb-2"></div>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-2"></i>Update Package
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer mt-auto py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 small opacity-75">
                    &copy; <?php echo date('Y'); ?> TravelNow. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="#" class="text-decoration-none me-3 text-primary">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="text-decoration-none me-3 text-primary">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="text-decoration-none text-primary">
                    <i class="fab fa-twitter"></i>
                </a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Edit Package JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all edit buttons
    const editButtons = document.querySelectorAll('.edit-package-btn');
    
    editButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            // Get data from button attributes
            const id = this.dataset.id;
            const title = this.dataset.title;
            const short = this.dataset.short;
            const description = this.dataset.description;
            const price = this.dataset.price;
            const duration = this.dataset.duration;
            const image = this.dataset.image;
            
            // Set values in the modal form
            document.getElementById('edit_package_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_short_desc').value = short;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_duration').value = duration;
            
            // Show current image
            const imageContainer = document.getElementById('edit_current_image');
            if (image && image !== '') {
                imageContainer.innerHTML = '<img src="uploads/' + image + '" width="100" style="border-radius: 5px;">';
            } else {
                imageContainer.innerHTML = '<p class="text-muted">No image uploaded</p>';
            }
        });
    });
});
</script>

</body>
</html>