<?php
require_once '../login/login_process.php';
ob_start();
// ป้องกันการoutput ล่วงหน้า

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/autoload.php';
// เปิด error reporting
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

session_start();
// ตรวจสอบสิทธิ์ Manager/IT
if (empty($_SESSION['emp_id']) || empty($_SESSION['dep_id'])) {
    header('Location: login.php');
    exit;
}

// รับพารามิเตอร์
$jobId  = filter_input(INPUT_GET, 'req_no', FILTER_VALIDATE_INT);
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
if (!$jobId || !in_array($action, ['approve','reject'], true)) {
    die('Invalid parameters');
}

// เชื่อมฐานข้อมูล
// $mysqli = new mysqli('localhost','amt','P@ssw0rd!amt','dbhelp',3306);
// if ($mysqli->connect_error) {
//     die('DB Connection Error: ' . $mysqli->connect_error);
// }

// 1) อัปเดตสถานะงาน
$newStatus = ($action === 'approve') ? 'in_progress' : 'rejected';
$stmt = $mysqli->prepare(
    'UPDATE db_job SET job_status = ?, job_updated_at = NOW() WHERE job_id = ?'
);
$stmt->bind_param('ss', $newStatus, $jobId);
if (!$stmt->execute()) {
    die('Update failed: ' . $stmt->error);
}
$stmt->close();

// 2) ดึงข้อมูลงานและพนักงาน
$stmt = $mysqli->prepare(
    'SELECT j.emp_id, j.problem_id, j.job_details, e.emp_name, e.emp_email, p.problem_name
     FROM db_job j
     JOIN employee e   ON j.emp_id     = e.emp_id
     JOIN db_problem p ON j.problem_id = p.problem_id
     WHERE j.job_id = ?'
);
$stmt->bind_param('s', $jobId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();
$mysqli->close();
echo $jobId;
$empId       = $data['emp_id'];
$empName     = $data['emp_name'];
$empEmail    = $data['emp_email'];
$problemName = $data['problem_name'];
$details     = $data['job_details'];

// 3) เตรียมเนื้อหาอีเมล
if ($action === 'approve') {
    // ส่งแจ้ง IT Support
    $recipient = 'Naret@alliedmetals.com';
    $subject   = "Job #{$jobId} Approved by Manager";
    $body  = "Job ID: {$jobId}\n";
    $body .= "Requester: {$empName} ({$empId})\n";
    $body .= "Problem: {$problemName}\n\n";
    $body .= "Details:\n{$details}\n\n";
    $body .= "Please proceed to the next step.";
} else {
    // ส่งแจ้งกลับผู้ขอ
    $recipient = $empEmail;
    $subject   = "Job #{$jobId} ถูกปฏิเสธ";
    $body  = "เรียน {$empName},\n\n";
    $body .= "คำร้องหมายเลข {$jobId} ของคุณถูกปฏิเสธโดยผู้จัดการ\n";
    $body .= "หมวดปัญหา: {$problemName}\n";
    $body .= "รายละเอียด:\n{$details}\n\n";
    $body .= "กรุณาติดต่อผู้จัดการแผนกหากมีข้อสงสัย";
}

// 4) ส่งอีเมลผ่าน PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'mail.alliedmetals.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'pdpa@alliedmetals.com';
    $mail->Password   = 'Allied01';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('naret@Alliedmetals.com', 'IT Request System');
    $mail->addAddress($recipient);
    $mail->CharSet   = 'UTF-8';
    $mail->Encoding  = 'base64';
    $mail->isHTML(false);

    $mail->Subject = $subject;
    $mail->Body    = $body;
    //$mail->send();
} catch (Exception $e) {
    error_log('Mailer Error: ' . $mail->ErrorInfo);
}

// 5) Redirect กลับ dashboard
//header('Location: dashboard.php');
exit;
?>