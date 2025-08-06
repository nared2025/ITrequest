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
$jobId = htmlspecialchars($_POST['job_id'] ?? '');
$action = htmlspecialchars($_POST['action'] ?? '');
$assignTo = $_POST['assign_to'] ?? '';
$reject_reason = htmlspecialchars($_POST['reject_reason'] ?? '');
//var_dump($jobId.$action.$assignTo.$reject_reason) ;



if (!$jobId || !in_array($action, ['approve','reject'], true)) {
    die('Invalid parameters');
}
    

// ตรวจสอบว่ามี jobId หรือไม่
if (empty($jobId)) {
    die('ไม่พบ job_id ที่ส่งมา');
}

// เชื่อมฐานข้อมูล
$mysqli = new mysqli('localhost','amt','P@ssw0rd!amt','dbhelp',3306);
if ($mysqli->connect_error) {
    die('DB Connection Error: ' . $mysqli->connect_error);
}
date_default_timezone_set('Asia/Bangkok');

$now = new DateTime();
$future = clone $now;
$future->modify('+24 hours');

$dueDate = $future->format('Y-m-d H:i:s'); // ⭐ แปลงเป็น string ก่อนใช้

// แสดงผล
echo "Current Time (Thai): " . $now->format('Y-m-d H:i:s') . "\n";
echo "After 24 Hours (Thai): " . $dueDate . "\n";

// เริ่ม query เอาประเภทปัญหาออกมา
$sql11 = "SELECT * FROM db_job Where job_id='$jobId'";
$result11 = $mysqli->query($sql11);

if ($result11->num_rows > 0) {
    $row = $result11->fetch_assoc();
    $problem_id = $row["problem_id"];
    $jobvpn = $row["jobvpn"];
}

// ใส่ข้อมูลหลัง assign งาน
$mgrapprove = 'A1263';
$newStatus = ($action === 'approve') ? 'in_progress' : 'rejected';

$stmt = $mysqli->prepare(
    'INSERT INTO db_job_assignments (assignment_id, job_id, assigned_emp, duedate)
     VALUES (?, ?, ?, ?)'
);
$stmt->bind_param('ssss', $assignmentId, $jobId, $assignTo, $dueDate); // ⭐ ใช้ string แล้ว
$stmt->execute();
$stmt->close();
//ใส่ข้อมูลหลัง assign งาน
//update job หลังassign งาน
$jobstatus='pending';
$mgrapprove='A1263';	
$stmt = $mysqli->prepare(
    'UPDATE db_job SET job_status = ? WHERE db_job.job_id = ?;'
);
$stmt->bind_param('ss', $jobstatus,$jobId );
$stmt->execute();
$stmt->close();
//update job หลังassign งาน
//echo "acttion=".$action."<br>";
if ($action === 'reject') {
    $jobstatus = 'rejected';
//echo "job_status: $jobstatus<br>";
   // echo "detail_Rejected: $reject_reason<br>";
    //echo "job_id: $jobId<br>";
    $stmt = $mysqli->prepare(
        'UPDATE db_job SET job_status = ?, detail_Rejected = ?, job_updated_at = NOW() WHERE job_id = ?'
    );
    $stmt->bind_param('sss', $jobstatus, $reject_reason, $jobId);
    if (!$stmt->execute()) {
        die('Update failed: ' . $stmt->error);
    }
    $stmt->close();
}

//echo "test==".$assignTo."<br>";
// ถึงตรงนี้// ถึงตรงนี้
if (!filter_var($assignTo, FILTER_VALIDATE_EMAIL)) {
	
    // $sendemail เป็น emp_id, ต้องดึงอีเมลจาก employee
    $empSql = "SELECT emp_email FROM employee WHERE emp_id='" . $mysqli->real_escape_string($assignTo) . "'";
    $empResult = $mysqli->query($empSql);
    if ($empResult && $empResult->num_rows > 0) {
        $empRow = $empResult->fetch_assoc();
        $sendemail = $empRow['emp_email'] ?? null;
    } else {
        $sendemail = null;
    }
}
//echo "1234".$empSql."<br>";
//echo "1234".$sendemail."<br>";
// 2) ดึงข้อมูลงานและพนักงาน
$stmt = $mysqli->prepare(
    'SELECT j.emp_id, j.problem_id, j.job_details,j.mgrapprove,j.detail_Rejected ,e.emp_name, e.emp_email, p.problem_name
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
$sendemail1    = $data['emp_email'];
$detail_Rejected  = $data['detail_Rejected'];
$problemName = $data['problem_name'];
$details     = $data['job_details'];

//echo"ก่อน test".$empId."<br>";
$mysqli = new mysqli('localhost','amt','P@ssw0rd!amt','dbhelp',3306);
if ($mysqli->connect_error) {
    die('DB Connection Error: ' . $mysqli->connect_error);
}
$sql111 = "SELECT * FROM employee Where emp_id='$empId'";
//echo$sql111;
$result111 = $mysqli->query($sql111);
if ($result111->num_rows > 0) 
{
    // ดึงแถวแรกเท่านั้น
    $row = $result111->fetch_assoc();
	$sendmailreject=$row["emp_email"];
	$empName1 =$row["emp_name"];
	
}

//echo"<br>"."email reject=".$sendmailreject."<br>";
    // แสดงค่าทั้งแถว (ตรวจสอบ debug)

    // ตรวจลำดับความสำคัญ: dep_scid > dep_dpid > dep_clev
// 3) เตรียมเนื้อหาอีเมล
if ($action === 'approve') {

    // ส่งแจ้ง IT Support
    $recipient = $sendemail;
    $subject   = "JOB #{$jobId} Approved by IT Manager";
    $body  = "JOB NO: {$jobId}<br>";
    $body .= "REQUESTER: {$empName1} <br>";
    $body .= "JOB CATEGORY: {$problemName}<br>";
    $body .= "DETAILS:{$details}<br>";
    $body .= "Please proceed to the next step.";
	
}
 else if($action === 'reject'){
    // ส่งแจ้งกลับผู้ขอ
	echo $empName1 ;
    $recipient = $sendmailreject;
    $subject= "Job NO #{$jobId} ถูกปฏิเสธ";
    $body  = "Dear K. {$empName1},<br>";
    $body .= "JOB NO.{$jobId}  was rejected by the IT Manager<br>";
    $body .= "JOB Category: {$problemName}<br>";
    $body .= "DETAILS:{$details}<br>";
	$body .= "REMARK:{$detail_Rejected}<br>";
    $body .= "Please contact the IT Manager if you have any questions";

}

$mail = new PHPMailer(true);
try {
   $mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host = 'mail.alliedmetals.com';                     	//Set the SMTP server to send through
        $mail->SMTPAuth = true;                                   	//Enable SMTP authentication
        $mail->Username = 'itrequest@alliedmetals.com';           	//SMTP username
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

        $mail->setFrom('itrequest@alliedmetals.com');                     // ส่งโดย
        
        $mail->addAddress($recipient); // ส่งถึง manager
        
        // Mapping หมวดปัญหา 1-7 เป็นชื่อ


        //Content
        $mail->isHTML(true);
  
    //$subject   = "Job #{$jobId} ถูกปฏิเสธ";
  //  $mailBody = "เรียน {$empName},<br>";
  //  $mailBody.= "คำร้องหมายเลข {$jobId} ของคุณถูกปฏิเสธโดยผู้จัดการ<br>";
   // $mailBody.= "หมวดปัญหา: {$problemName}<br>";
//    $mailBody .= "รายละเอียด:{$details}<br>";
 //   $mailBody .= "กรุณาติดต่อผู้จัดการแผนกหากมีข้อสงสัย";
	echo"test".$subject."<br>";
        $mail->Subject = $subject;
        $mail->Body = $body;

    // echo $body;

 // อัปโหลดและแนบไฟล์ (ถ้ามี)
  

    // ส่งอีเมล
  $mail->send();

  header('Location: ../manager_IT/Admin_IT.php');
    exit;

} catch (Exception $e) {
    echo "อีเมลส่งไม่ได้เนื่องจาก: " . $mail->ErrorInfo;
    exit;
}

?>

