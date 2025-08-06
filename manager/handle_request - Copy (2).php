<?php
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
$jobId          = $_REQUEST['job_id'] ?? $_GET['req_no'] ?? null;
$action         = $_REQUEST['action'] ?? null;
$reject_reason  = filter_input(INPUT_POST, 'reject_reason', FILTER_SANITIZE_STRING);
var_dump(" $jobId.$action.$reject_reason");



// เชื่อมฐานข้อมูล
$mysqli = new mysqli('localhost','amt','P@ssw0rd!amt','dbhelp',3306);
if ($mysqli->connect_error) {
    die('DB Connection Error: ' . $mysqli->connect_error);
}
// เริ่ม query เอาประเภทปัญหาออกมา
$sql11 = "SELECT * FROM db_job Where job_id='$jobId'";
echo $sql11;
$result11 = $mysqli->query($sql11);

if ($result11->num_rows > 0) 
{
    // ดึงแถวแรกเท่านั้น
    $row = $result11->fetch_assoc();
	$problem_id=$row["problem_id"];
	$jobvpn=$row["jobvpn"];
}
//echo $problem_id;
//echo $jobvpn;
// 1) อัปเดตสถานะงาน

  

if($action === 'approve' && ($problem_id < 6 || $jobvpn == 1))
{
$mgrapprove='A1263';	
$newStatus = ($action === 'approve') ? 'in_progress' : 'rejected';
$stmt = $mysqli->prepare(
    'UPDATE db_job SET job_status = ?, job_updated_at = NOW(),mgrapprove = ? WHERE job_id = ?'
);
$stmt->bind_param('sss', $newStatus,$mgrapprove,$jobId);
if (!$stmt->execute()) {
    die('Update failed: ' . $stmt->error);}

$stmt->close();
}
else if ($action === 'approve' &&($problem_id==7))
{
$jobvpn='1';
$mgrapprove='A1209';	
$newStatus = ($action === 'approve') ? 'waiting_approval' : 'rejected';
$stmt = $mysqli->prepare(
    'UPDATE db_job SET job_status = ?, job_updated_at = NOW(),mgrapprove = ?, jobvpn = ? WHERE job_id = ?'
);


$stmt->bind_param('ssis', $newStatus,$mgrapprove,$jobvpn,$jobId);
if (!$stmt->execute()) {
    die('Update failed: ' . $stmt->error);}

$stmt->close();
}


// 2) ดึงข้อมูลงานและพนักงาน
$stmt = $mysqli->prepare(
    'SELECT j.emp_id, j.problem_id, j.job_details,j.mgrapprove ,e.emp_name, e.emp_email, p.problem_name
     FROM db_job j
     JOIN employee e   ON j.mgrapprove     = e.emp_id
     JOIN db_problem p ON j.problem_id = p.problem_id
     WHERE j.job_id = ?'
);
$stmt->bind_param('s', $jobId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();
$mysqli->close();

$empId       = $data['emp_id'];
$empName     = $data['emp_name'];
$sendemail    = $data['emp_email'];

$problemName = $data['problem_name'];
$details     = $data['job_details'];
echo $sendemail ; 
//echo$sendemail;

    // แสดงค่าทั้งแถว (ตรวจสอบ debug)

    // ตรวจลำดับความสำคัญ: dep_scid > dep_dpid > dep_clev
// 3) เตรียมเนื้อหาอีเมล
if ($action === 'approve') {
    // ส่งแจ้ง IT Support
    $recipient = $sendemail;
    $subject   = "Job #{$jobId} Approved by Manager";
    $body  = "Job ID: {$jobId}\n";
    $body .= "Requester: {$empName} ({$empId})\n";
    $body .= "Problem: {$problemName}\n\n";
    $body .= "Details:\n{$details}\n\n";
    $body .= "Please proceed to the next step.";
} 
$mysqli = new mysqli('localhost','amt','P@ssw0rd!amt','dbhelp',3306);
if ($mysqli->connect_error) {
    die('DB Connection Error: ' . $mysqli->connect_error);
}
if ($action === 'reject' && !empty($reject_reason)) {
    $stmt = $mysqli->prepare(
        'UPDATE db_job SET job_status = ?, job_updated_at = NOW(), detail_Rejected = ? WHERE job_id = ?');
    $newStatus = 'rejected';
    $stmt->bind_param('sss', $newStatus, $reject_reason, $jobId);
    if (!$stmt->execute()) {
        die('Update failed: ' . $stmt->error);
    }
    $stmt->close();
}
    // ส่งแจ้งกลับผู้ขอ
    // ดึงอีเมลของผู้ขอ (ไม่ใช่ mgrapprove)
    // ดึงอีเมลของผู้ขอ (emp_id ของผู้ขอ ไม่ใช่ mgrapprove)
    $mysqli = new mysqli('localhost','amt','P@ssw0rd!amt','dbhelp',3306);
if ($mysqli->connect_error) {
    die('DB Connection Error: ' . $mysqli->connect_error);
}
    if ($action === 'reject') {
        $recipient = '';
        $stmt_req = $mysqli->prepare(
            'SELECT e.emp_email, e.emp_name FROM db_job j JOIN employee e ON j.emp_id = e.emp_id WHERE j.job_id = ?'
        );
        $stmt_req->bind_param('s', $jobId);
        $stmt_req->execute();
        $result_req = $stmt_req->get_result()->fetch_assoc();
        if ($result_req && !empty($result_req['emp_email'])) {
            $recipient = $result_req['emp_email'];
            $empName = $result_req['emp_name'];
        }
		echo "test".$recipient;
        $stmt_req->close();

        $subject   = "Job #{$jobId} ถูกปฏิเสธ";
        $body  = "เรียน {$empName},\n\n";
        $body .= "คำร้องหมายเลข {$jobId} ของคุณถูกปฏิเสธโดยผู้จัดการ\n";
        $body .= "หมวดปัญหา: {$problemName}\n";
        $body .= "รายละเอียด";
        $body .= "เหตุผลที่ถูกปฏิเสธ: {$reject_reason}\n\n";
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

    // $mail->setFrom('naret@Alliedmetals.com', 'IT Request System');
    // // $mail->addAddress($recipient);
    // $mail->CharSet   = 'UTF-8';
    // $mail->Encoding  = 'base64';
    // $mail->isHTML(false);

    // $mail->Subject = $subject;
    // $mail->Body    = $body;
    // $mail->send();
} catch (Exception $e) {
    error_log('Mailer Error: ' . $mail->ErrorInfo);
}

// echo "ส่งอีเมลเรียบร้อยแล้วไปที่ $recipient";
// 5) Redirect กลับ dashboard
//header('Location: dashboard.php');
exit;
?>