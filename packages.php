<?php
session_start();
require 'db.php';
require 'functions.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Packages — TravelNow</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'partials/navbar.php'; ?>

<main class="container my-5">
  <div class="row">
    <!-- 🧭 Filters -->
    <div class="col-lg-3">
      <div class="card p-3 mb-3 shadow-sm">
        <h6 class="mb-3">Filter Packages</h6>
        <form method="get">
          <label class="form-label small">Destination</label>
          <input name="q" class="form-control form-control-sm mb-2" placeholder="e.g., Bali" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">

          <label class="form-label small">Min Price (PKR)</label>
          <input name="min" class="form-control form-control-sm mb-2" placeholder="e.g., 50000" value="<?= isset($_GET['min']) ? htmlspecialchars($_GET['min']) : '' ?>">

          <label class="form-label small">Max Price (PKR)</label>
          <input name="max" class="form-control form-control-sm mb-2" placeholder="e.g., 100000" value="<?= isset($_GET['max']) ? htmlspecialchars($_GET['max']) : '' ?>">

          <button class="btn btn-outline-primary btn-sm w-100">Apply Filters</button>
        </form>
      </div>

      <div class="card p-3 shadow-sm">
        <h6>Popular Tags</h6>
        <div class="mt-2">
          <span class="badge bg-light text-dark border">Beach</span>
          <span class="badge bg-light text-dark border">Family</span>
          <span class="badge bg-light text-dark border">Adventure</span>
        </div>
      </div>
    </div>

    <!-- 📦 Packages List -->
    <div class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h5 mb-0">All Packages</h3>
        <div class="small text-muted">Explore our latest travel offers</div>
      </div>

      <div class="row g-3">
        <?php
        // ✅ Build query with filters
        $where = '1';
        $params = [];

        if (!empty($_GET['q'])) {
          $where .= ' AND title LIKE ?';
          $params[] = '%' . $_GET['q'] . '%';
        }
        if (!empty($_GET['min'])) {
          $where .= ' AND price >= ?';
          $params[] = (int)$_GET['min'];
        }
        if (!empty($_GET['max'])) {
          $where .= ' AND price <= ?';
          $params[] = (int)$_GET['max'];
        }

        $stmt = $pdo->prepare("SELECT * FROM packages WHERE $where ORDER BY id DESC");
        $stmt->execute($params);
        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$packages): ?>
          <div class="col-12">
            <div class="alert alert-warning">No packages found. Try different filters.</div>
          </div>
        <?php else: ?>
          <?php foreach ($packages as $row): ?>
            <div class="col-md-6">
              <div class="card shadow-sm border-0 h-100">
                <img src="uploads/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="Package Image">
                <div class="card-body">
                  <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                  <p class="small text-muted"><?= htmlspecialchars($row['short_desc']) ?></p>
                  <p class="mb-2"><strong>Duration:</strong> <?= htmlspecialchars($row['duration']) ?> days</p>
                  <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="fw-bold text-primary">PKR <?= number_format($row['price']) ?></div>
                    <div>
                      <a href="book_package.php?id=<?= intval($row['id']) ?>" class="btn btn-sm btn-primary">
    Book Now
</a>
                      <a href="package_details.php?id=<?= intval($row['id']) ?>" class="btn btn-sm btn-outline-secondary">Details</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>

<?php include 'partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
