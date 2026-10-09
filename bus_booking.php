<?php
session_start();
require 'db.php';
require 'inc/bus_functions.php';
include 'partials/navbar.php';

// Add this function here directly
function get_booked_seats($bus_id, $travel_date) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("
        SELECT seat_no FROM bus_bookings 
        WHERE bus_id = ? AND travel_date = ?
    ");
    $stmt->execute([$bus_id, $travel_date]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

if (!isset($_SESSION['user_id'])) {
    echo '<div class="container mt-5"><div class="alert alert-warning">Please login to book a seat.</div></div>';
    include 'partials/footer.php';
    exit;
}

// Get bus ID and date from URL
$bus_id = intval($_GET['id'] ?? 0);
$date = $_GET['date'] ?? date('Y-m-d');

$bus = get_bus($bus_id);
if (!$bus) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Invalid bus selected.</div></div>';
    include 'partials/footer.php';
    exit;
}

// Get booked seats
$booked_seats = get_booked_seats($bus_id, $date);

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seat = intval($_POST['seat_no']);
    $cnic = trim($_POST['cnic']);
    $phone = trim($_POST['phone']);
    $payment_status = 'paid'; // Simulated payment

    // Validation
    if (empty($seat) || empty($cnic) || empty($phone)) {
        echo '<div class="container mt-3"><div class="alert alert-danger">Please fill all fields properly.</div></div>';
    } else {
        // Check if seat is already booked
        $check_sql = "SELECT COUNT(*) FROM bus_bookings 
                      WHERE bus_id = ? AND seat_no = ? AND travel_date = ?";
        $pdo = get_pdo();
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$bus_id, $seat, $date]);
        $count = $check_stmt->fetchColumn();
        
        if ($count > 0) {
            echo '<div class="container mt-3"><div class="alert alert-danger">Seat #' . $seat . ' is already booked! Please select another seat.</div></div>';
        } else {
            // Save booking
            $sql = "INSERT INTO bus_bookings (user_id, bus_id, seat_no, travel_date, payment_status, cnic, phone) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['user_id'], $bus_id, $seat, $date, $payment_status, $cnic, $phone]);
            $booking_id = $pdo->lastInsertId();
            header('Location: bus_confirm.php?id=' . $booking_id);
            exit;
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Book Bus — Travel Agency</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  
  <!-- ADDED: Seat Grid CSS -->
  <style>
    /* ===========================
       BUS SEAT GRID
    =========================== */
    .seat-grid {
        display: grid;
        grid-template-columns: repeat(4, 80px);
        gap: 18px;
        justify-content: center;
        margin: 25px 0;
        transition: all 0.3s ease;
    }

    .seat-box {
        width: 70px;
        height: 70px;
        border-radius: 8px;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        transition: .25s;
        user-select: none;
    }

    .seat-box input {
        display: none;
    }

    /* Available Seat */
    .available {
        background: #fff;
        border: 2px solid #198754;
        color: #198754;
    }

    .available:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 18px rgba(0,0,0,.15);
    }

    .available input:checked + span {
        position: absolute;
        inset: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #198754;
        color: #fff;
        border-radius: 6px;
    }

    /* Booked Seat - White background with red border and red cross */
    .booked {
        background: #ffffff;
        border: 2px solid #dc3545;
        color: #dc3545;
        cursor: not-allowed;
        overflow: hidden;
    }

    /* Red Cross (X) */
    .booked::before,
    .booked::after {
        content: '';
        position: absolute;
        width: 80%;
        height: 3px;
        background: #dc3545;
        left: 10%;
        top: 50%;
        border-radius: 2px;
    }

    .booked::before {
        transform: rotate(45deg);
    }

    .booked::after {
        transform: rotate(-45deg);
    }

    /* Seat number on booked seats - red to match */
    .booked span {
        position: relative;
        z-index: 2;
        color: #dc3545;
    }

    /* Seat Number */
    .seat-box span {
        position: relative;
        z-index: 2;
    }

    /* Hide booked seats when filter is active */
    .seat-grid.show-available-only .booked {
        display: none;
    }

    /* Toggle Button */
    .filter-toggle {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        margin: 10px 0 20px 0;
    }

    .filter-toggle .btn {
        min-width: 140px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .filter-toggle .btn.active {
        box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.3);
    }

    .filter-toggle .badge {
        font-size: 14px;
        padding: 5px 12px;
    }

    /* Legend */
    .seat-legend {
        display: flex;
        justify-content: center;
        gap: 40px;
        margin-top: 15px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
    }

    .legend-box {
        width: 24px;
        height: 24px;
        border-radius: 4px;
    }

    .legend-box.available {
        background: #fff;
        border: 2px solid #198754;
    }

    .legend-box.booked {
        background: #ffffff;
        border: 2px solid #dc3545;
        position: relative;
    }

    /* Mini red cross in legend */
    .legend-box.booked::before,
    .legend-box.booked::after {
        content: '';
        position: absolute;
        width: 70%;
        height: 2px;
        background: #dc3545;
        left: 15%;
        top: 50%;
        border-radius: 2px;
    }

    .legend-box.booked::before {
        transform: rotate(45deg);
    }

    .legend-box.booked::after {
        transform: rotate(-45deg);
    }

    /* Animation for seat count */
    .seat-count {
        font-size: 14px;
        color: #6c757d;
        text-align: center;
        margin-top: 5px;
    }
  </style>
</head>

<body class="bg-light">

<header class="py-5 bg-primary text-white text-center">
  <div class="container">
    <h1 class="fw-bold mb-2">Book Your Bus Seat</h1>
    <p class="lead mb-0">Quick, secure, and easy — reserve your spot in just a few clicks</p>
  </div>
</header>

<main class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h3 class="card-title mb-3"><?= htmlspecialchars($bus['bus_name']) ?></h3>
          <p class="text-muted mb-2">
            <strong>Route:</strong> <?= htmlspecialchars($bus['from_city']) ?> → <?= htmlspecialchars($bus['to_city']) ?>
          </p>
          <p class="text-muted mb-2">
            <strong>Travel Date:</strong> <?= htmlspecialchars($date) ?>
          </p>
          <p class="text-muted mb-3">
            <strong>Fare:</strong> PKR <?= number_format($bus['price']) ?>
          </p>

          <!-- DELETED: Already Booked Seats alert block -->

          <form method="POST" id="bookingForm">
            <!-- REPLACED: Entire seat selection with new grid -->
            <div class="mb-4">
                <label class="form-label fw-bold">Select Your Seat</label>

                <!-- ADDED: Toggle/Filter Buttons -->
                <div class="filter-toggle">
                    <button type="button" class="btn btn-outline-secondary active" id="showAllBtn">
                        <i class="bi bi-grid"></i> All Seats
                    </button>
                    <button type="button" class="btn btn-outline-success" id="showAvailableBtn">
                        <i class="bi bi-eye"></i> Available Only
                    </button>
                    <span class="badge bg-success" id="availableCount">
                        <?= $bus['available_seats'] - count($booked_seats) ?> available
                    </span>
                </div>

                <div class="seat-grid" id="seatGrid">
                    <?php
                    $totalSeats = $bus['available_seats'];
                    for($i = 1; $i <= $totalSeats; $i++):
                        $booked = in_array($i, $booked_seats);
                    ?>
                        <?php if($booked): ?>
                            <div class="seat-box booked" data-seat="<?= $i ?>">
                                <span><?= $i ?></span>
                                <div class="cross"></div>
                            </div>
                        <?php else: ?>
                            <label class="seat-box available" data-seat="<?= $i ?>">
                                <input type="radio" name="seat_no" value="<?= $i ?>" required>
                                <span><?= $i ?></span>
                            </label>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>

                <div class="seat-legend">
                    <div class="legend-item">
                        <div class="legend-box available"></div>
                        Available
                    </div>
                    <div class="legend-item">
                        <div class="legend-box booked"></div>
                        Booked
                    </div>
                </div>
            </div>

            <div class="mb-3">
              <label class="form-label">CNIC Number:</label>
              <input 
                name="cnic" 
                type="text" 
                class="form-control w-75" 
                placeholder="e.g. 35202-1234567-8" 
                pattern="\d{5}-\d{7}-\d" 
                required>
              <div class="form-text">Enter your valid CNIC number in the format 12345-1234567-1</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Phone Number:</label>
              <input 
                name="phone" 
                type="tel" 
                class="form-control w-75" 
                placeholder="e.g. 03XXXXXXXXX" 
                pattern="03[0-9]{9}" 
                required>
              <div class="form-text">Enter your 11-digit phone number (e.g. 03001234567)</div>
            </div>

            <div class="alert alert-info">
              <strong>Note:</strong> This uses a dummy payment gateway — for testing only.
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">Confirm & Pay</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include 'partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- ADDED: Toggle Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const seatGrid = document.getElementById('seatGrid');
    const showAllBtn = document.getElementById('showAllBtn');
    const showAvailableBtn = document.getElementById('showAvailableBtn');
    const availableCount = document.getElementById('availableCount');
    
    // Count available seats
    const totalSeats = <?= $bus['available_seats'] ?>;
    const bookedSeats = <?= json_encode($booked_seats) ?>;
    const availableSeats = totalSeats - bookedSeats.length;
    
    // Show all seats
    showAllBtn.addEventListener('click', function() {
        seatGrid.classList.remove('show-available-only');
        showAllBtn.classList.add('active');
        showAllBtn.classList.remove('btn-outline-secondary');
        showAllBtn.classList.add('btn-secondary');
        showAvailableBtn.classList.remove('active');
        showAvailableBtn.classList.add('btn-outline-success');
        showAvailableBtn.classList.remove('btn-success');
        availableCount.textContent = availableSeats + ' available';
    });
    
    // Show only available seats
    showAvailableBtn.addEventListener('click', function() {
        seatGrid.classList.add('show-available-only');
        showAvailableBtn.classList.add('active');
        showAvailableBtn.classList.remove('btn-outline-success');
        showAvailableBtn.classList.add('btn-success');
        showAllBtn.classList.remove('active');
        showAllBtn.classList.remove('btn-secondary');
        showAllBtn.classList.add('btn-outline-secondary');
        availableCount.textContent = 'Showing ' + availableSeats + ' available';
    });
    
    // Initial state - show all
    showAllBtn.classList.add('active', 'btn-secondary');
    showAllBtn.classList.remove('btn-outline-secondary');
});
</script>

</body>
</html>