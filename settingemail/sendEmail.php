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
$sub_department = $_POST['sub_department']?? 0;
$image      = $_FILES['attachment'];
$duedate   = $_POST['duedate'] ?? '';
$remark    = $_POST['remark'] ?? '';
// รับค่า job_id และ status จาก GET จากหน้า history.php User reject
$job_id = $_GET['job_id'] ?? '';
$cancel = $_GET['status'] ?? '';
echo "เลขที่เอกสาร และ Cancel".$job_id. $cancel."<br>";


echo "เลขปลายทาง".$sub_department."<br>";
echo$empId;


// 2. จัดการการอัปโหลดไฟล์จาก $_FILES['attachment']
// $attachmentPath = null; 
// if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
//     $allowedTypes = ['image/jpeg','image/png','application/pdf'];
//     $fileType     = $_FILES['attachment']['type'];

//     if (in_array($fileType, $allowedTypes)) {
//         // กำหนดโฟลเดอร์เก็บไฟล์ (ตัวอย่าง: folder uploads/)
//         $uploadDir = __DIR__ . '/uploads/';
//         if (!is_dir($uploadDir)) {
//             mkdir($uploadDir, 0777, true);
//         }

//         // สร้างชื่อไฟล์ใหม่กันชื่อซ้ำ
//         $basename    = basename($_FILES['attachment']['name']);
//         $newFilename = date('Ymd_His_') . $basename;
//         $destination = $uploadDir . $newFilename;

//         if (move_uploaded_file($_FILES['attachment']['tmp_name'], $destination)) {
//             // เก็บพาธแบบ relative ใช้บันทึกลง DB (เวลาแสดงจะอ้าง path นี้)
//             $attachmentPath = 'uploads/' . $newFilename;
//         } else {
//             die('ไม่สามารถบันทึกไฟล์แนบได้');
//         }
//     } else {
//         die('ชนิดไฟล์ที่อัปโหลดไม่ถูกต้อง (รองรับเฉพาะ JPG, PNG, PDF)');
//     }
// }

// ตรวจสอบว่ามี problem_id จากฟอร์มหรือไม่
$problemId = isset($_POST['problem_id']) ? (int)$_POST['problem_id'] : 0;
if ($problemId <= 0) {
    die('กรุณาเลือก Problem Category ให้ถูกต้อง');
}
// เชื่อมต่อฐานข้อมูล
$connect = new mysqli('localhost', 'amt', 'P@ssw0rd!amt', 'dbhelp', 3306);
if ($connect->connect_error) 
{
    die('DB Connection Error: ' . $connect->connect_error);
}


// สร้าง requestNo ใหม่
$result = $connect->query("SELECT job_id FROM db_job WHERE job_id LIKE 'IT25%' ORDER BY job_id DESC LIMIT 1");
if (!$result) {
    die("ดึงRequest no ไม่สำเร็จ: " . $connect->error);
}
$row = $result->fetch_assoc();

if ($row && preg_match('/IT25(\d{3})/', $row['job_id'], $matches)) {
    $num = (int)$matches[1] + 1; // ดึงเลข 3 หลัก แล้ว +1
    $requestNo = 'IT25' . str_pad($num, 3, '0', STR_PAD_LEFT); // เติม 0 ให้ครบ 3 หลัก
} else {
    $requestNo = 'IT25001'; // ถ้าไม่มีหรือไม่ match pattern
}
echo"เลขที่เอกสารใหม่".$requestNo."<br>";
// ตรวจสอบค่าก่อน insert
if (empty($empId) || empty($depId) || empty($problemId) || empty($details)) {
    die('ข้อมูลไม่ครบ');
}



// ดึงชื่อหมวดปัญหา
$stmt = $connect->prepare("SELECT problem_name FROM db_problem WHERE problem_id = ?");
if (!$stmt) {
    die('Prepare failed (select problem): ' . $connect->error);
}
$stmt->bind_param('i', $problemId);
$stmt->execute();
$result = $stmt->get_result();
$row    = $result->fetch_assoc();
$problemName = $row['problem_name'] ?? 'ไม่ระบุหมวด';
$stmt->close();
echo "รหัสปัญหา".$problemId;
// เริ่ม query
$sql11 = "SELECT * FROM emp_dep Where dep_id=$depId";
echo $sql11."<br>";
$result11 = $connect->query($sql11);

if ($result11->num_rows > 0) {
    // ดึงแถวแรกเท่านั้น
    $row = $result11->fetch_assoc();

    // แสดงค่าทั้งแถว (ตรวจสอบ debug)

    // ตรวจลำดับความสำคัญ: dep_scid > dep_dpid > dep_clev
    if (isset($row["dep_scid"]) && trim($row["dep_scid"]) !== "")
	{
        $sendemail = $row["dep_scid"];			
        
    }
	elseif (isset($row["dep_dpid"]) && trim($row["dep_dpid"]) !== "") {
        $sendemail = $row["dep_dpid"];
        
    } elseif (isset($row["dep_clev"]) && trim($row["dep_clev"]) !== "") {
        $sendemail = $row["dep_clev"];
        
    } else {
        $sendemail = null;
    }
}
echo "<br>"."testพนักงาน".$sendemail;
// ตรวจสอบว่าเป็น Email Manager ที่เป็นหน่วย OP
echo"test2".$row["dep_scid"];

if ($row["dep_scid"] == $empId && trim($row["dep_dpid"]) != "0" && !empty(trim($row["dep_dpid"]))) {
    $sendemail = $row["dep_dpid"];
} else if ($row["dep_scid"] == $empId && (trim($row["dep_dpid"]) == "0" || empty(trim($row["dep_dpid"])))) {
    $sendemail = $row["dep_clev"];
} 
  else if ($row["dep_clev"]==$empId)
  {	  
	  $sendemail='A1209';
  }
else 
{ 
   $sendemail;
}

//echo "ผลลัพธ์ที่ได้: " . $sendemail;
//เริ่มตรวจสอบประเภทปัญหา

if ($problemId==7)
{
	 $sendemail = $row["dep_clev"];
}
else if ($problemId==9)
{
	$sendemail='A1263';
}	
else
{
	$sendemail;
}
//echo"ก่อนถึง if =".$problemId."<br>";
//echo$jobId=$requestNo."<br>";
// case Cancel
if($cancel=10)
{
	
	$job_id;
$job_status = 'cancel';
echo "เลขที่เอกสาร".$job_id;
	//echo "เท่ากับ 9".$mgrapprove=$sendemail."<br>";
//echo"mgrapprove".$mgrapprove."<br>";

/*
// insert ข้อมูลใหม่
$stmt = $connect->prepare(
   'UPDATE `db_job` SET `job_status` = '?' WHERE `db_job`.`job_id` = '?'');
if (!$stmt) {
    die('Prepare failed (insert job): ' . $connect->error);
}
$job_id;
$job_status = 'cancel';
$stmt->bind_param('ss',$job_status, $jobId);
$stmt->execute();
if ($stmt->error) {
    die('Execute failed: ' . $stmt->error);
	$stmt->close();
	*/
}
//สิ้นสุด Case Cancel

if($problemId==9)
{
	$mgrapprove=$sendemail;
	//echo "เท่ากับ 9".$mgrapprove=$sendemail."<br>";
//echo"mgrapprove".$mgrapprove."<br>";

$jobId=$requestNo;
// insert ข้อมูลใหม่
$stmt = $connect->prepare(
   'INSERT INTO db_job (job_id, emp_id, dep_id, problem_id, job_details,mgrapprove,job_status,dep_output) VALUES (?,?,?,?,?,?,?,?)');
if (!$stmt) {
    die('Prepare failed (insert job): ' . $connect->error);
}

$job_status = 'in_progress';
$stmt->bind_param('ssisssss', $jobId, $empId, $depId, $problemId, $details,$mgrapprove, $job_status,$sub_department);
$stmt->execute();
if ($stmt->error) {
    die('Execute failed: ' . $stmt->error);
	$stmt->close();
}

}
else
{
$mgrapprove=$sendemail;
//echo"mgrapprove".$mgrapprove."<br>";
$jobId=$requestNo;
// insert ข้อมูลใหม่
$stmt = $connect->prepare(
   'INSERT INTO db_job (job_id, emp_id, dep_id, problem_id, job_details,mgrapprove,job_status,dep_output) VALUES (?,?,?,?,?,?,?,?)');
if (!$stmt) {
    die('Prepare failed (insert job): ' . $connect->error);
}

$job_status = 'waiting_approval';
$stmt->bind_param('ssisssss', $jobId, $empId, $depId, $problemId, $details,$mgrapprove, $job_status,$sub_department);
$stmt->execute();
if ($stmt->error) {
    die('Execute failed: ' . $stmt->error);
}
$stmt->close();
}
//สิ้นสุดประเภทปัญหา

// ตรวจสอบว่า $sendemail เป็นอีเมลหรือยัง ถ้าไม่ใช่ ให้ดึงอีเมลจาก employee

if (!filter_var($sendemail, FILTER_VALIDATE_EMAIL)) {
    // $sendemail เป็น emp_id, ต้องดึงอีเมลจาก employee
    $empSql = "SELECT emp_email FROM employee WHERE emp_id='" . $connect->real_escape_string($sendemail) . "'";
    $empResult = $connect->query($empSql);
    if ($empResult && $empResult->num_rows > 0) {
        $empRow = $empResult->fetch_assoc();
        $sendemail = $empRow['emp_email'] ?? null;
    } else {
        $sendemail = null;
    }
}

//echo $sendemail;
$problemMap = [
    1 => 'Hardware',
    2 => 'Software',
    3 => 'Network',
    4 => 'Erp',
    5 => 'Email',
    6 => 'Port USB',
	7 => 'Vpn',
    8 => 'Folder',
	9 => 'Link Meeting'

	
];

$problemID = $problemMap[$problemId] ?? 'unknown';// ผู้รับ
// $sendemail= $row["dep_mmai"];

// $row["dep_mmai"]="sitikorn@alliedmetals.com";
// 4. ส่งอีเมลผ่าน PHPMailer
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
        
        $mail->addAddress($sendemail); // ส่งถึง manager
        
        // Mapping หมวดปัญหา 1-7 เป็นชื่อ


        //Content
        $mail->isHTML(true);
$mailSubject = "IT REQUEST ONLINE ";
$mailBody .= "JOB NO: {$jobId}<br>";
$mailBody .= "REQUESTER: {$fullName} ({$empId})<br>";
$mailBody .= "SECTION: {$department}<br>";
$mailBody .= "JOB CATEGORY: {$problemID}<br>";
$mailBody .= "DETAILS: " . nl2br(htmlspecialchars($details)) . "<br>";
$mailBody .= "You can open IT JOB Online"."<br>"."http://192.168.1.173/it_request/login/login.php";

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

    header('Location: ../php/success.php?job_id=' . $jobId);
    exit;

} catch (Exception $e) {
    echo "อีเมลส่งไม่ได้เนื่องจาก: " . $mail->ErrorInfo;
    exit;
}


?>
