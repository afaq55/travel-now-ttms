<?php
// inc/bus_functions.php

require_once __DIR__ . '/../db.php'; // Ensure DB connection is included

// ✅ If get_pdo() is not defined in db.php, define it here
if (!function_exists('get_pdo')) {
    function get_pdo() {
        static $pdo = null;
        if ($pdo === null) {
            try {
                $DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
                $DB_NAME = getenv('DB_NAME') ?: 'travel_agency';
                $DB_USER = getenv('DB_USER') ?: 'root';
                $DB_PASS = getenv('DB_PASS') ?: '';

                $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die('Database Connection Failed: ' . $e->getMessage());
            }
        }
        return $pdo;
    }
}

/* -----------------------------
   Fetch a single bus by ID
----------------------------- */
function get_bus($id) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT * FROM buses WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/* -----------------------------
   Get all booked seats for a bus on a specific date
----------------------------- */
function get_booked_seats($bus_id, $travel_date) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("
        SELECT seat_no FROM bus_bookings 
        WHERE bus_id = ? AND travel_date = ?
    ");
    $stmt->execute([$bus_id, $travel_date]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/* -----------------------------
   Create a new booking record
----------------------------- */
function create_booking($user_id, $bus_id, $seat, $date, $payment_status, $cnic, $phone) {
    $pdo = get_pdo();
    
    // Check if seat is already booked
    $check_sql = "SELECT COUNT(*) FROM bus_bookings 
                  WHERE bus_id = ? AND seat_no = ? AND travel_date = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$bus_id, $seat, $date]);
    $count = $check_stmt->fetchColumn();
    
    if ($count > 0) {
        return [
            'success' => false,
            'message' => "Seat #$seat is already booked for this date. Please select another seat."
        ];
    }
    
    // Insert the booking
    $sql = "INSERT INTO bus_bookings (user_id, bus_id, seat_no, travel_date, payment_status, cnic, phone) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $bus_id, $seat, $date, $payment_status, $cnic, $phone]);
    
    return [
        'success' => true,
        'booking_id' => $pdo->lastInsertId()
    ];
}

/* -----------------------------
   Fetch a booking by its ID
----------------------------- */
function get_booking($booking_id) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("
        SELECT 
            bb.id AS booking_id,
            bb.user_id,
            bb.bus_id,
            bb.seat_no,
            bb.travel_date,
            bb.payment_status,
            b.bus_name,
            b.from_city,
            b.to_city,
            b.price
        FROM bus_bookings bb
        JOIN buses b ON bb.bus_id = b.id
        WHERE bb.id = ?
    ");
    $stmt->execute([$booking_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>