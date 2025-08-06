<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/autoload.php';
// เปิดการแสดง error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 1. เช็ค session และรับค่าตัวแปร
if (!isset($_SESSION['emp_id'], $_SESSION['dep_id'])) {
    header('Location: ../php/tranform.php');
    exit();
}
$jobId      = $_SESSION['job_id'] ?? 0;
$empId      = $_SESSION['emp_id'];
$depId      = $_SESSION['dep_id'];
$details    = trim($_POST['details'] ?? '');
$fullName   = $_SESSION['emp_name'] ?? '';
$department = $_SESSION['dep_name'] ?? '';

// ตรวจสอบว่ามี problem_id จากฟอร์มหรือไม่
$problemId = isset($_POST['problem_id']) ? (int)$_POST['problem_id'] : 0;
if ($problemId <= 0) {
    die('กรุณาเลือก Problem Category ให้ถูกต้อง');
}

// 2. ดึงชื่อหมวดปัญหาจาก db_problem
$mysqli = new mysqli('localhost', 'amt', 'P@ssw0rd!amt', 'dbhelp', 3306);
if ($mysqli->connect_error) {
    die('DB Connection Error: ' . $mysqli->connect_error);
}

$stmt = $mysqli->prepare("SELECT problem_name FROM db_problem WHERE problem_id = ?");
if (!$stmt) {
    die('Prepare failed (select problem): ' . $mysqli->error);
}
$stmt->bind_param('i', $problemId);
$stmt->execute();
$result = $stmt->get_result();
$row    = $result->fetch_assoc();
$problemName = $row['problem_name'] ?? 'ไม่ระบุหมวด';
$stmt->close();

// 3. INSERT ลง db_job และดึง job_id
$stmt = $mysqli->prepare(
    'INSERT INTO db_job (emp_id, dep_id, problem_id, job_details) VALUES (?, ?, ?, ?)'
);
if (!$stmt) {
    die('Prepare failed (insert job): ' . $mysqli->error);
}
$stmt->bind_param('siis', $empId, $depId, $problemId, $details);
if (!$stmt->execute()) {
    die('Execute failed: ' . $stmt->error);
}

$jobId = $mysqli->insert_id;
$stmt->close();
$mysqli->close();
$row["dep_mmai"]="naret@alliedmetals.com";
// 4. ส่งอีเมลผ่าน PHPMailer
$mail = new PHPMailer(true);
try {
   $mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host = 'mail.alliedmetals.com';                     	//Set the SMTP server to send through
        $mail->SMTPAuth = true;                                   	//Enable SMTP authentication
        $mail->Username = 'pdpa@alliedmetals.com';           	//SMTP username
        $mail->Password = 'Allied01';                              	//SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable implicit TLS encryption
        $mail->Port = 587;                                    		//TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $mail->SMTPOptions = array(
            'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
            )
        );
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('pdpa@alliedmetals.com');                     // ส่งโดย
        
        $mail->addAddress($row["dep_mmai"]); 
        
        // Mapping หมวดปัญหา 1-7 เป็นชื่อ
$problemMap = [
    1 => 'computer',
    2 => 'software',
    3 => 'network',
    4 => 'erp',
    5 => 'email',
    6 => 'vpn',
    7 => 'authorized'
];
$problemID = $problemMap[$problemId] ?? 'unknown';// ผู้รับ

        //Content
        $mail->isHTML(true);
$mailSubject = "แจ้งคำขอ IT Request";
$mailBody  = "<b>รายละเอียด</b><br>";
$mailBody .= "เลขที่คำขอ: {$jobId}<br>";
$mailBody .= "ชื่อผู้ขอ: {$fullName} ({$empId})<br>";
$mailBody .= "แผนก: {$department}<br>";
$mailBody .= "หมวดปัญหา: {$problemID}<br>";
$mailBody .= "รายละเอียด: " . nl2br(htmlspecialchars($details)) . "<br>";


        $mail->Subject = $mailSubject;
        $mail->Body = $mailBody;

        echo $mailBody;

 // อัปโหลดและแนบไฟล์ (ถ้ามี)
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg','image/png','application/pdf'];
        $fileType = $_FILES['attachment']['type'];
        if (in_array($fileType, $allowed)) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $filename       = date('Ymd_His_') . basename($_FILES['attachment']['name']);
            $attachmentPath = $uploadDir . $filename;
            move_uploaded_file(
                $_FILES['attachment']['tmp_name'],
                $attachmentPath
            );
            $mail->addAttachment($attachmentPath);
        }
    }

    // ส่งอีเมล
    $mail->send();

    // Redirect ไป success page
    header('Location: ../php/success.php?job_id=' . $jobId);
    exit;

} catch (Exception $e) {
    echo "อีเมลส่งไม่ได้เนื่องจาก: " . $mail->ErrorInfo;
    exit;
}
?>
