<?php
session_start();
require 'db.php';
require 'functions.php';

// Fetch statistics for KPI cards
$stmt = $pdo->query("SELECT COUNT(*) as total FROM packages WHERE is_published = 1");
$totalPackages = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
$totalBookings = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as total FROM bookings");
$totalCustomers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COALESCE(SUM(total_price), 0) as total FROM bookings");
$totalRevenue = $stmt->fetchColumn();

// Fetch destinations (unique locations from packages)
$stmt = $pdo->query("SELECT COUNT(DISTINCT location) as total FROM packages WHERE is_published = 1");
$totalDestinations = $stmt->fetchColumn();

// Fetch average rating from reviews
$stmt = $pdo->query("SELECT COALESCE(AVG(rating), 0) as avg_rating FROM reviews");
$avgRating = $stmt->fetchColumn();
$avgRating = round($avgRating, 1);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TravelNow — Explore</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/style.css">
</head>

<body class="d-flex flex-column min-vh-100">

<?php include 'partials/navbar.php'; ?>

<!-- HERO SECTION -->
<section class="hero-section">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6 text-center text-lg-start">
        <h1 class="display-4 fw-bold mb-3">
          Discover amazing places.<br>Create unforgettable memories.
        </h1>
        <p class="lead mb-4 opacity-75">
          Premium travel packages with comfort, safety, and 24/7 dedicated support.
        </p>
        <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
          <a href="packages.php" class="btn btn-light btn-lg px-4 py-3 fw-semibold">
            <i class="bi bi-compass me-2"></i>Explore Packages
          </a>
          <a href="#how" class="btn btn-outline-light btn-lg px-4 py-3">
            <i class="bi bi-play-circle me-2"></i>How It Works
          </a>
        </div>
      </div>
      <div class="col-lg-6">
        <img src="assets/j.jpeg" class="img-fluid rounded-4 shadow-lg hero-image">
      </div>
    </div>
  </div>
</section>

<main class="container my-5 flex-grow-1">

  <!-- HOW IT WORKS -->
  <section id="how" class="mb-5">
    <div class="text-center mb-5">
      <h2 class="fw-bold mb-3">How TravelNow Works</h2>
      <p class="text-muted">Three simple steps to your perfect vacation</p>
    </div>

    <div class="row g-4">
      <div class="col-md-4 text-center">
        <i class="bi bi-search display-4 text-primary mb-3"></i>
        <h5 class="fw-bold">Browse Packages</h5>
        <p class="text-muted">Explore destinations with clear pricing and details.</p>
      </div>

      <div class="col-md-4 text-center">
        <i class="bi bi-calendar-check display-4 text-primary mb-3"></i>
        <h5 class="fw-bold">Book & Pay</h5>
        <p class="text-muted">Secure booking with easy payment options.</p>
      </div>

      <div class="col-md-4 text-center">
        <i class="bi bi-airplane display-4 text-primary mb-3"></i>
        <h5 class="fw-bold">Travel & Enjoy</h5>
        <p class="text-muted">We handle everything while you enjoy your trip.</p>
      </div>
    </div>
  </section>

  <!-- FEATURED PACKAGES SLIDER -->
  <section class="mb-5 position-relative">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="fw-bold">Featured Travel Packages</h2>
      <a href="packages.php" class="btn btn-link">View All</a>
    </div>

    <?php
    // Fetch ALL published packages (same as packages.php)
    $stmt = $pdo->query("SELECT * FROM packages WHERE is_published = 1 ORDER BY id DESC");
    $packages = $stmt->fetchAll();
    $totalPackages = count($packages);
    ?>

    <?php if($totalPackages > 0): ?>
      <div id="featuredCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
        
        <div class="carousel-inner">
          <?php 
          $chunks = array_chunk($packages, 3);
          foreach($chunks as $index => $chunk): 
          ?>
            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
              <div class="row">
                <?php foreach($chunk as $row): ?>
                  <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm h-100 package-card">
                      <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>"
                           class="card-img-top"
                           alt="<?php echo htmlspecialchars($row['title']); ?>">
                      <div class="card-body">
                        <h5 class="fw-bold"><?php echo htmlspecialchars($row['title']); ?></h5>
                        <p class="text-muted small"><?php echo htmlspecialchars($row['short_desc']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                          <span class="fw-bold text-primary">
                            PKR <?php echo number_format($row['price']); ?>
                          </span>
                          <a href="book_package.php?id=<?php echo $row['id']; ?>"
                             class="btn btn-primary btn-sm">Book Now</a>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if($totalPackages > 3): ?>
          <button class="carousel-control-prev" type="button" data-bs-target="#featuredCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#featuredCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
          
          <div class="carousel-indicators">
            <?php for($i = 0; $i < count($chunks); $i++): ?>
              <button type="button" 
                      data-bs-target="#featuredCarousel" 
                      data-bs-slide-to="<?php echo $i; ?>" 
                      class="<?php echo $i === 0 ? 'active' : ''; ?>" 
                      aria-current="<?php echo $i === 0 ? 'true' : 'false'; ?>" 
                      aria-label="Slide <?php echo $i + 1; ?>">
              </button>
            <?php endfor; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="text-center">
        <p class="text-muted">No packages available.</p>
      </div>
    <?php endif; ?>
  </section>

  <!-- KPI CARDS SECTION - MOVED TO BOTTOM -->
  <section class="mb-5">
    <div class="text-center mb-4">
      <h2 class="fw-bold mb-2">Travel Statistics</h2>
      <p class="text-muted">Our impact in numbers</p>
    </div>

    <div class="row g-4">
      <!-- KPI Card 1: Total Packages -->
      <div class="col-lg-2 col-md-4 col-6">
        <div class="kpi-card">
          <div class="d-flex align-items-center gap-3">
            <div class="kpi-icon blue">
              <i class="bi bi-suitcase"></i>
            </div>
            <div>
              <div class="kpi-number"><?php echo number_format($totalPackages); ?></div>
              <div class="kpi-label">Packages</div>
            </div>
          </div>
        </div>
      </div>

      <!-- KPI Card 2: Total Bookings -->
      <div class="col-lg-2 col-md-4 col-6">
        <div class="kpi-card">
          <div class="d-flex align-items-center gap-3">
            <div class="kpi-icon green">
              <i class="bi bi-check-circle"></i>
            </div>
            <div>
              <div class="kpi-number"><?php echo number_format($totalBookings); ?></div>
              <div class="kpi-label">Bookings</div>
            </div>
          </div>
        </div>
      </div>

      <!-- KPI Card 3: Happy Customers -->
      <div class="col-lg-2 col-md-4 col-6">
        <div class="kpi-card">
          <div class="d-flex align-items-center gap-3">
            <div class="kpi-icon purple">
              <i class="bi bi-people"></i>
            </div>
            <div>
              <div class="kpi-number"><?php echo number_format($totalCustomers); ?></div>
              <div class="kpi-label">Customers</div>
            </div>
          </div>
        </div>
      </div>

      <!-- KPI Card 4: Revenue -->
      <div class="col-lg-2 col-md-4 col-6">
        <div class="kpi-card">
          <div class="d-flex align-items-center gap-3">
            <div class="kpi-icon orange">
              <i class="bi bi-currency-dollar"></i>
            </div>
            <div>
              <div class="kpi-number"><?php echo number_format($totalRevenue); ?></div>
              <div class="kpi-label">Revenue</div>
            </div>
          </div>
        </div>
      </div>

      <!-- KPI Card 5: Destinations -->
      <div class="col-lg-2 col-md-4 col-6">
        <div class="kpi-card">
          <div class="d-flex align-items-center gap-3">
            <div class="kpi-icon red">
              <i class="bi bi-geo-alt"></i>
            </div>
            <div>
              <div class="kpi-number"><?php echo number_format($totalDestinations); ?></div>
              <div class="kpi-label">Destinations</div>
            </div>
          </div>
        </div>
      </div>

      <!-- KPI Card 6: Rating with Stars -->
      <div class="col-lg-2 col-md-4 col-6">
        <div class="kpi-card">
          <div class="d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #f59e0b, #f97316);">
              <i class="bi bi-star-fill"></i>
            </div>
            <div>
              <div class="kpi-number"><?php echo $avgRating; ?></div>
              <div class="kpi-label">
                <span class="stars">
                  <?php
                  $fullStars = floor($avgRating);
                  $halfStar = ($avgRating - $fullStars) >= 0.5 ? 1 : 0;
                  $emptyStars = 5 - $fullStars - $halfStar;
                  
                  for ($i = 0; $i < $fullStars; $i++) {
                      echo '<i class="bi bi-star-fill" style="color: #f59e0b; font-size: 12px;"></i>';
                  }
                  if ($halfStar) {
                      echo '<i class="bi bi-star-half" style="color: #f59e0b; font-size: 12px;"></i>';
                  }
                  for ($i = 0; $i < $emptyStars; $i++) {
                      echo '<i class="bi bi-star" style="color: #d1d5db; font-size: 12px;"></i>';
                  }
                  ?>
                </span>
                <br><small style="font-size: 10px; color: #6b7280;">Based on reviews</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

</main>

<?php include 'partials/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>