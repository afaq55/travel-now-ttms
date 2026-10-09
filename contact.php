<?php
session_start();
require 'db.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Contact Us — TravelNow</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (if navbar uses them) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Your custom stylesheet -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">

<?php include 'partials/navbar.php'; ?>

<main class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h3 class="mb-4">Contact Us</h3>
          <p class="text-muted">Have questions or need support? Fill out the form below and we'll get back to you shortly.</p>

          <form method="post" action="">
            <div class="mb-3">
              <label class="form-label">Your Name</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Message</label>
              <textarea name="message" class="form-control" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Message</button>
          </form>

          <?php
          if ($_SERVER['REQUEST_METHOD'] === 'POST') {
              $name = $_POST['name'];
              $email = $_POST['email'];
              $message = $_POST['message'];

              $stmt = $pdo->prepare("INSERT INTO messages (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
              $stmt->execute([$name, $email, $message]);
              echo "<div class='alert alert-success mt-3'>✅ Your message has been sent successfully!</div>";
          }
          ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include 'partials/footer.php'; ?>
</body>
</html>
