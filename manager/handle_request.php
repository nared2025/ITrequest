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
//var_dump(" $jobId.$action.$reject_reason");



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
	$folder_status=$row["folder_status"];
	$detail_Rejected  = $row['detail_Rejected'];
}
//echo $problem_id."<br>"."ไม่อนุมัติเพราะ".$detail_Rejected."<br>";
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
// ตรวจสอบประเภทปัญหา Folder
else if ($action === 'approve' &&($problem_id==8)&&($folder_status==0))
{
	$sql1111 = "SELECT * FROM db_job INNER JOIN emp_dep ON db_job.dep_output=emp_dep.dep_id Where job_id='$jobId'";
echo$sql1111."<br>";
echo "test8-0"."<br>";
$result1111 = $mysqli->query($sql1111);
if ($result1111->num_rows > 0) 
{
    // ดึงแถวแรกเท่านั้น
    $row = $result1111->fetch_assoc();
	$sendemail=$row["dep_clev"];
	$jobstatus=1;
$stmt = $mysqli->prepare(
    'UPDATE db_job SET  job_updated_at = NOW(),mgrapprove = ?,folder_status = ? WHERE job_id = ?'
);
$stmt->bind_param('sis', $sendemail,$jobstatus,$jobId);
if (!$stmt->execute()) {
    die('Update failed: ' . $stmt->error);}

$stmt->close();
}
echo "emailล่าสุด".$sendemail."<br>";
}
//ตรวจสอบFolder ว่าหลัง Manager ปลายทางอนุมัติแล้วจะไปหา IT Manager
else if ($action === 'approve' &&($problem_id==8)&&($folder_status==1))
{
	echo "test8-1"."<br>";
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


//สิ้นสุดตรวจสอบFolder ว่าหลัง Manager ปลายทางอนุมัติแล้วจะไปหา IT Manager


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
echo $sendemail."<br>" ; 
//echo$sendemail;

    // แสดงค่าทั้งแถว (ตรวจสอบ debug)

    // ตรวจลำดับความสำคัญ: dep_scid > dep_dpid > dep_clev
// 3) เตรียมเนื้อหาอีเมล
if ($action === 'approve') {
	$recipient=$sendemail;
    // ส่งแจ้ง IT Support
     $subject= "IT REQUEST ONLINE ";
    $body  = "Waiting for your approval<br>";
    $body .= "JOB NO: {$jobId} <br>";
    $body .= "JOB CATEGORY: {$problemName}<br>";
    $body .= "DETAILS:{$details}<br>";
    $body .= "You can open IT Request Online"."<br>"."http://192.168.1.173/it_request/login/login.php";
	


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
  //  $mailBody = "เรียนคุณ {$empName},<br>";
  //  $mailBody.= "คำร้องหมายเลข {$jobId} ของคุณถูกปฏิเสธโดยผู้จัดการ<br>";
   // $mailBody.= "หมวดปัญหา: {$problemName}<br>";
//    $mailBody .= "รายละเอียด:{$details}<br>";
 //   $mailBody .= "กรุณาติดต่อผู้จัดการแผนกหากมีข้อสงสัย";

        $mail->Subject = $subject;
        $mail->Body = $body;

    // echo $body;

 // อัปโหลดและแนบไฟล์ (ถ้ามี)
  

    // ส่งอีเมล
   $mail->send();
	
           }
	catch (Exception $e)
	{
    error_log('Mailer Error: ' . $mail->ErrorInfo);	
    }
} 
$mysqli = new mysqli('localhost','amt','P@ssw0rd!amt','dbhelp',3306);
if ($mysqli->connect_error) {
    die('DB Connection Error: ' . $mysqli->connect_error);
}
if ($action === 'reject' && !empty($reject_reason)) 
{
    $stmt = $mysqli->prepare(
        'UPDATE db_job SET job_status = ?, job_updated_at = NOW(), detail_Rejected = ? WHERE job_id = ?');
    $newStatus = 'rejected';
    $stmt->bind_param('sss', $newStatus, $reject_reason, $jobId);
      if (!$stmt->execute())
	{
        die('Update failed: ' . $stmt->error);
    }
    $stmt->close();
}
    
    $mysqli = new mysqli('localhost','amt','P@ssw0rd!amt','dbhelp',3306);
if ($mysqli->connect_error) 
{
    die('DB Connection Error: ' . $mysqli->connect_error);
}
    if ($action === 'reject')
		{
        $recipient = '';
        $stmt_req = $mysqli->prepare(
            'SELECT e.emp_email, e.emp_name FROM db_job j JOIN employee e ON j.emp_id = e.emp_id WHERE j.job_id = ?'
        );
        $stmt_req->bind_param('s', $jobId);
        $stmt_req->execute();
        $result_req = $stmt_req->get_result()->fetch_assoc();
        if ($result_req && !empty($result_req['emp_email']))
			{
            $recipient = $result_req['emp_email'];
            $empName = $result_req['emp_name'];
            }
		echo "testemail update".$recipient."<br>";
        
    
    $subject= "Job No. #{$jobId}Reject ";
    $body  = "Dear K. {$empName},<br>";
    $body .= "Job No.{$jobId} was rejected by the Section Manager<br>";
    $body .= "JOB Category: {$problemName}<br>";
    $body .= "DETAILS: {$details}<br>";
	$body .= "REMARK:{$detail_Rejected}<br>";
    $body .= "Please contact the Section Manager if you have any questions";
	


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
  //  $mailBody = "เรียนคุณ {$empName},<br>";
  //  $mailBody.= "คำร้องหมายเลข {$jobId} ของคุณถูกปฏิเสธโดยผู้จัดการ<br>";
   // $mailBody.= "หมวดปัญหา: {$problemName}<br>";
//    $mailBody .= "รายละเอียด:{$details}<br>";
 //   $mailBody .= "กรุณาติดต่อผู้จัดการแผนกหากมีข้อสงสัย";

        $mail->Subject = $subject;
        $mail->Body = $body;

    // echo $body;

 // อัปโหลดและแนบไฟล์ (ถ้ามี)
  

    // ส่งอีเมล
   $mail->send();
	$stmt_req->close();
           }
	catch (Exception $e)
	{
    error_log('Mailer Error: ' . $mail->ErrorInfo);	
    }
	}	

// 5) Redirect กลับ dashboard
header('Location:Admin.php');

exit;

?>