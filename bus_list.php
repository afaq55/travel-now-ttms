<?php
require 'db.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Bus Tickets — Travel Agency</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="container py-5">
  <h2 class="mb-4">Available Buses</h2>
  <form method="get" class="row mb-4">
    <div class="col-md-3"><input type="text" name="from" class="form-control" placeholder="From City"></div>
    <div class="col-md-3"><input type="text" name="to" class="form-control" placeholder="To City"></div>
    <div class="col-md-3"><input type="date" name="date" class="form-control"></div>
    <div class="col-md-3"><button class="btn btn-primary w-100">Search</button></div>
  </form>

  <div class="row g-3">
    <?php
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';

    $sql = "SELECT * FROM buses WHERE available_seats > 0";
    $params = [];

    if ($from !== '') {
        $sql .= " AND from_city LIKE ?";
        $params[] = "%$from%";
    }
    if ($to !== '') {
        $sql .= " AND to_city LIKE ?";
        $params[] = "%$to%";
    }

    $sql .= " ORDER BY departure_time ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() === 0) {
        echo '<p>No buses found for your search.</p>';
    }

    while ($bus = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<div class="col-md-4">';
        echo '<div class="card h-100 shadow-sm">';
        if (!empty($bus['image'])) {
            echo '<img src="assets/' . htmlspecialchars($bus['image']) . '" class="card-img-top" alt="Bus">';
        }
        echo '<div class="card-body">';
        echo '<h5>' . htmlspecialchars($bus['bus_name']) . '</h5>';
        echo '<p class="text-muted">' . htmlspecialchars($bus['from_city']) . ' → ' . htmlspecialchars($bus['to_city']) . '</p>';
        echo '<p>Type: ' . htmlspecialchars($bus['bus_type']) . '</p>';
        echo '<p><strong>PKR ' . number_format($bus['price']) . '</strong></p>';
        echo '<p><small>Departure: ' . htmlspecialchars($bus['departure_time']) . '</small></p>';
        echo '<a href="bus_booking.php?id=' . $bus['id'] . '" class="btn btn-outline-primary w-100">Book Now</a>';
        echo '</div></div></div>';
    }
    ?>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
