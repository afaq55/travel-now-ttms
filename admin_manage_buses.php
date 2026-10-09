<?php
// admin_manage_buses.php
session_start();
require 'db.php';
include 'inc/bus_functions.php';
include 'header.php';

// Simple auth check - adjust to your admin auth
if(!isset($_SESSION['is_admin'])) {
    echo '<p>Admin login required.</p>';
    include 'footer.php';
    exit;
}

// Handle add/edit/delete
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if($action==='add') {
        $stmt = $conn->prepare("INSERT INTO buses (name,from_city,to_city,fare,departure_time,seats) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param('sssdsi', $_POST['name'], $_POST['from_city'], $_POST['to_city'], $_POST['fare'], $_POST['departure_time'], $_POST['seats']);
        $stmt->execute();
    } elseif($action==='delete') {
        $stmt = $conn->prepare("DELETE FROM buses WHERE id=?");
        $stmt->bind_param('i', $_POST['id']);
        $stmt->execute();
    }
}

// Fetch all
$res = $conn->query("SELECT * FROM buses ORDER BY id DESC");
$buses = $res->fetch_all(MYSQLI_ASSOC);
?>
<div class="container">
  <h2>Manage Buses</h2>
  <h3>Add Bus</h3>
  <form method="POST">
    <input type="hidden" name="action" value="add">
    <label>Name:</label><input name="name" required>
    <label>From:</label><input name="from_city" required>
    <label>To:</label><input name="to_city" required>
    <label>Fare:</label><input name="fare" required>
    <label>Departure:</label><input name="departure_time" required>
    <label>Seats:</label><input name="seats" type="number" required>
    <button type="submit">Add Bus</button>
  </form>

  <h3>Existing Buses</h3>
  <table>
    <tr><th>ID</th><th>Name</th><th>Route</th><th>Fare</th><th>Seats</th><th>Action</th></tr>
    <?php foreach($buses as $b): ?>
      <tr>
        <td><?= $b['id'] ?></td>
        <td><?= htmlspecialchars($b['name']) ?></td>
        <td><?= htmlspecialchars($b['from_city']) ?> → <?= htmlspecialchars($b['to_city']) ?></td>
        <td><?= htmlspecialchars($b['fare']) ?></td>
        <td><?= htmlspecialchars($b['seats']) ?></td>
        <td>
          <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $b['id'] ?>">
            <button type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php include 'footer.php'; ?>