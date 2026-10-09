<?php require 'db.php'; ?>
<?php
$id = isset($_GET['id'])? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT * FROM packages WHERE id = ?');
$stmt->execute([$id]);
$pkg = $stmt->fetch();
if(!$pkg) { header('Location: packages.php'); exit; }
?>
<!doctype html>
<html><head>
<link rel="stylesheet" href="assets/css/style.css"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo htmlspecialchars($pkg['title']);?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
</head><body class="bg-light">
<?php include 'partials/navbar.php'; ?>
<main class="container my-5">
  <div class="row">
    <div class="col-md-8">
      <img src="assets/<?php echo htmlspecialchars($pkg['image']);?>" class="img-fluid rounded mb-3" alt="">
      <h3><?php echo htmlspecialchars($pkg['title']);?></h3>
      <p class="text-muted"><?php echo nl2br(htmlspecialchars($pkg['description']));?></p>
    </div>
    <div class="col-md-4">
      <div class="card p-3 shadow-sm">
        <div class="fw-bold h4">PKR <?php echo number_format($pkg['price']);?></div>
        <p class="small text-muted">Duration: <?php echo intval($pkg['duration']);?> days</p>
        <a href="booking.php?id=<?php echo intval($pkg['id']);?>" class="btn btn-primary w-100">Book Now</a>
      </div>
    </div>
  </div>
</main>
<?php include 'partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Reviews -->
<div class="container mt-4">
  <h3>Reviews</h3>
  <?php
  include_once 'db.php';
  $pkg_id = intval($_GET['id'] ?? 0);
  if ($pkg_id) {
      $res = $conn->query("SELECT * FROM reviews WHERE package_id=" . $pkg_id . " ORDER BY created_at DESC");
      if ($res) {
          while ($r = $res->fetch_assoc()) {
              echo '<div class="border p-2 mb-2"><strong>' . htmlspecialchars($r['user_name']) . '</strong> - Rating: ' . intval($r['rating']) . '/5<br>' . nl2br(htmlspecialchars($r['comment'])) . '</div>';
          }
      }
  }
  ?>
  <h4>Leave a review</h4>
  <form action="review.php" method="post" class="mb-4">
    <input type="hidden" name="package_id" value="<?php echo intval($_GET['id'] ?? 0); ?>">
    <div class="mb-2"><input class="form-control" name="user_name" placeholder="Your name" required></div>
    <div class="mb-2"><select class="form-select" name="rating"><option>5</option><option>4</option><option>3</option><option>2</option><option>1</option></select></div>
    <div class="mb-2"><textarea class="form-control" name="comment" placeholder="Your review" required></textarea></div>
    <button class="btn btn-primary" type="submit">Submit Review</button>
  </form>
</div>

</body></html>