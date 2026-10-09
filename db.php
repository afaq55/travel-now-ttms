<?php
/**
 * db.php
 * Localhost XAMPP Database Connection
 */

// ================= CONFIG =================

// XAMPP Default Settings
$DB_HOST = 'localhost';
$DB_PORT = '3306';
$DB_USER = 'root';
$DB_PASS = ''; // XAMPP default empty password
$DB_NAME = 'travel_agency';

// ================= MYSQLI =================

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $conn = new mysqli(
        $DB_HOST,
        $DB_USER,
        $DB_PASS,
        $DB_NAME,
        $DB_PORT
    );

    $conn->set_charset('utf8mb4');

} catch (mysqli_sql_exception $e) {

    error_log(
        'MySQLi Connection Error: ' .
        $e->getMessage()
    );

    http_response_code(500);

    exit('Database connection error.');
}

// ================= PDO =================

try {

    $dsn =
    "mysql:host={$DB_HOST};
    port={$DB_PORT};
    dbname={$DB_NAME};
    charset=utf8mb4";

    $pdo = new PDO(

        $dsn,
        $DB_USER,
        $DB_PASS,

        [
            PDO::ATTR_ERRMODE =>
            PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE =>
            PDO::FETCH_ASSOC,

            PDO::ATTR_EMULATE_PREPARES =>
            false,
        ]
    );

} catch (PDOException $e) {

    error_log(
        'PDO Connection Error: ' .
        $e->getMessage()
    );

    http_response_code(500);

    exit('Database connection error.');
}

// ================= HELPERS =================

function db_mysqli(): mysqli
{
    global $conn;
    return $conn;
}

function db_pdo(): PDO
{
    global $pdo;
    return $pdo;
}