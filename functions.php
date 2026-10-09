<?php
// === Added helper functions ===

// send_email(to, subject, html_body)
// Uses PHPMailer if available, otherwise falls back to PHP mail()
// Configure SMTP using environment variables or edit defaults below.
function send_email($to, $subject, $html_body) {
    // Try PHPMailer if available
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            // SMTP configuration (edit or set env vars)
            $mail->isSMTP();
            $mail->Host = getenv('SMTP_HOST') ?: 'smtp.example.com';
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USER') ?: 'your_email@example.com';
            $mail->Password = getenv('SMTP_PASS') ?: 'your_smtp_password';
            $mail->SMTPSecure = getenv('SMTP_SECURE') ?: 'tls';
            $mail->Port = getenv('SMTP_PORT') ?: 587;

            $mail->setFrom(getenv('SMTP_FROM') ?: 'no-reply@yourdomain.com', 'Travel Agency');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html_body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log error if you have logging; fallback to mail()
        }
    }

    // Fallback: PHP mail()
    $headers = "MIME-Version: 1.0\r\n" .
               "Content-type: text/html; charset=UTF-8\r\n" .
               "From: Travel Agency <no-reply@yourdomain.com>\r\n";
    return mail($to, $subject, $html_body, $headers);
}

// mock_payment($booking_id, $amount)
// Simulates a payment and returns an array with status and transaction id.
// It will also attempt to insert a payments record if DB connection is available.
function mock_payment($booking_id, $amount, $method='mock') {
    $tx = 'MOCK' . time() . rand(100,999);
    $status = 'success'; // or 'failed'

    // Try saving payment record to DB if connection helper exists
    if (function_exists('get_db_connection')) {
        $conn = get_db_connection();
        if ($conn) {
            $stmt = $conn->prepare("INSERT INTO payments (booking_id, method, amount, transaction_id, status) VALUES (?,?,?,?,?)");
            if ($stmt) {
                $stmt->bind_param("isdss", $booking_id, $method, $amount, $tx, $status);
                $stmt->execute();
                $stmt->close();
            }
            // Also update booking status if bookings table exists
            $upd = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            if ($upd) {
                $s = ($status === 'success') ? 'confirmed' : 'pending';
                $upd->bind_param("si", $s, $booking_id);
                $upd->execute();
                $upd->close();
            }
        }
    }

    return array('status' => $status, 'transaction_id' => $tx);
}
?>