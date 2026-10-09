<?php
// bus_search_results.php
session_start();
require 'db.php';
require 'inc/bus_functions.php';
include 'header.php';

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$date = $_GET['date'] ?? '';

$buses = [];
if($from && $to) {
    $buses = get_buses($from, $to, $date);
}
?>
<div class="container">
  <h2>Available Buses: <?= htmlspecialchars($from) ?> → <?= htmlspecialchars($to) ?> on <?= htmlspecialchars($date) ?></h2>
  <?php if(!$buses): ?>
    <p>No buses found.</p>
  <?php else: ?>
    <table>
      <tr><th>Bus</th><th>Departure</th><th>Fare</th><th>Seats</th><th></th></tr>
      <?php foreach($buses as $b): ?>
      <tr>
        <td><?= htmlspecialchars($b['name']) ?></td>
        <td><?= htmlspecialchars($b['departure_time']) ?></td>
        <td><?= htmlspecialchars($b['fare']) ?></td>
        <td><?= htmlspecialchars($b['seats']) ?></td>
        <td><a href="bus_book.php?bus_id=<?= $b['id'] ?>&date=<?= urlencode($date) ?>">Book Now</a></td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>