<?php
// send_email.php
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

// Connect DB
$conn = new mysqli("localhost", "root", "", "monicomlab"); 
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Basic input
$to        = $_POST['to'] ?? '';
$subject   = $_POST['subject'] ?? '';
$body      = $_POST['body'] ?? '';
$ticket_id = $_POST['ticket_id'] ?? '';

if (!$to || !$subject || !$body) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

$mail = new PHPMailer(true);
$status = 'failed';
$errorMessage = '';

try {
    // Gmail SMTP settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'shikiraponclara@gmail.com';   // your Gmail
    $mail->Password   = 'oxpz sqop qfyc lcsq';        // your App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
    $mail->Port       = 587;

    // Sender
    $mail->setFrom('shikiraponclara@gmail.com', 'Monicomlab');

    // Recipient
    $mail->addAddress($to);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = nl2br(htmlspecialchars($body));
    $mail->AltBody = strip_tags($body);

    // Send
    if ($mail->send()) {
        $status = 'sent';
        echo json_encode(['success' => true, 'message' => 'Email sent successfully.']);
    } else {
        $errorMessage = $mail->ErrorInfo;
        echo json_encode(['success' => false, 'message' => 'Mailer error: '.$errorMessage]);
    }
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    echo json_encode(['success' => false, 'message' => 'Exception: '.$errorMessage]);
}

// Insert log into DB
$stmt = $conn->prepare("INSERT INTO email_logs (ticket_id, recipient_email, subject, message, status, error_message) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $ticket_id, $to, $subject, $body, $status, $errorMessage);
$stmt->execute();
$stmt->close();

$conn->close();
