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


// เชื่อมต่อฐานข้อมูล
$connect = new mysqli('localhost', 'amt', 'P@ssw0rd!amt', 'dbhelp', 3306);
if ($connect->connect_error) 
{
    die('DB Connection Error: ' . $connect->connect_error);
}

if($cancel=10)
{
	
$job_id;
$job_status = 'cancel';
	

// insert ข้อมูลใหม่
$stmt = $connect->prepare('UPDATE db_job SET job_status = ? WHERE db_job.job_id = ?');


if (!$stmt) {
    die('Prepare failed (insert job): ' . $connect->error);
}
//echo "เลขที่เอกสาร3456".$job_id;
$job_id;
$job_status = 'cancel';
$stmt->bind_param('ss',$job_status, $job_id);
$stmt->execute();
var_dump($stmt);

if ($stmt->error) {
    die('Execute failed: ' . $stmt->error);
	$stmt->close();
	
}
//สิ้นสุด Case Cancel

}
   header('Location: ../php/success.php?job_id=' . $jobId);
    exit;



?>
