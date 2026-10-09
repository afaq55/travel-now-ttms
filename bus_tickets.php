<?php
// bus_tickets.php
session_start();
require 'db.php';
require 'inc/bus_functions.php';
include 'header.php'; // adjust if your project uses a different header include
?>
<div class="container">
  <h2>Search Bus Tickets</h2>
  <form method="GET" action="bus_search_results.php">
    <label>From:</label><input name="from" required>
    <label>To:</label><input name="to" required>
    <label>Date:</label><input type="date" name="date" required>
    <button type="submit">Search</button>
  </form>
</div>
<?php include 'footer.php'; ?>