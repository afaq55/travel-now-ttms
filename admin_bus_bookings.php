<?php
// admin_bus_bookings.php
session_start();
require 'db.php';
include 'header.php';
if(!isset($_SESSION['is_admin'])) {
    echo '<p>Admin login required.</p>';
    include 'footer.php';
    exit;
}
$res = $conn->query("SELECT bo.*, b.name as bus_name, u.username FROM bus_bookings bo JOIN buses b ON bo.bus_id=b.id LEFT JOIN users u ON u.id=bo.user_id ORDER BY bo.created_at DESC");
$rows = $res->fetch_all(MYSQLI_ASSOC);
?>
<div class="container">
  <h2>Bus Bookings</h2>
  <table>
    <tr><th>ID</th><th>Bus</th><th>User</th><th>Seat</th><th>Date</th><th>Payment</th></tr>
    <?php foreach($rows as $r): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['bus_name']) ?></td>
        <td><?= htmlspecialchars($r['username']) ?></td>
        <td><?= htmlspecialchars($r['seat_no']) ?></td>
        <td><?= htmlspecialchars($r['date']) ?></td>
        <td><?= htmlspecialchars($r['payment_status']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php include 'footer.php'; ?>