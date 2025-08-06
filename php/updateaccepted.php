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

//$jobId      = $_SESSION['job_id'] ?? 0;
$job_id = $_GET['req_no']?? 0;

//$duedate   = $_POST['datetime-local'] ?? '';
//echo "เวลา".$duedate."<br>";
$remark    = $_POST['remark'] ?? '';
// รับค่า job_id และ status จาก GET จากหน้า history.php User reject
echo "เลขที่เอกสาร ".$job_id."<br>";
date_default_timezone_set('Asia/Bangkok');
$datetime = date("Y-m-d H:i:s");


// เชื่อมต่อฐานข้อมูล
$connect = new mysqli('localhost', 'amt', 'P@ssw0rd!amt', 'dbhelp', 3306);
if ($connect->connect_error) 
{
    die('DB Connection Error: ' . $connect->connect_error);
}



// insert ข้อมูลใหม่
$stmt = $connect->prepare('UPDATE db_job_assignments SET duedate= ? WHERE db_job_assignments.job_id = ?;
');


if (!$stmt) {
    die('Prepare failed (insert job): ' . $connect->error);
}
//echo "เลขที่เอกสาร3456".$job_id;
$job_id;
$stmt->bind_param('ss',$datetime, $job_id);
$stmt->execute();
var_dump($stmt);

if ($stmt->error) {
    die('Execute failed: ' . $stmt->error);
	$stmt->close();
	
}
//Update Status Comple
$connect = new mysqli('localhost', 'amt', 'P@ssw0rd!amt', 'dbhelp', 3306);
if ($connect->connect_error) 
{
    die('DB Connection Error: ' . $connect->connect_error);
}



// insert ข้อมูลใหม่
$stmt = $connect->prepare('UPDATE db_job SET job_status= ? WHERE job_id = ?;
');


if (!$stmt) {
    die('Prepare failed (insert job): ' . $connect->error);
}
//echo "เลขที่เอกสาร3456".$job_id;
$job_id;
$jobstatus='completed';
$stmt->bind_param('ss',$jobstatus, $job_id);
$stmt->execute();
var_dump($stmt);

if ($stmt->error) {
    die('Execute failed: ' . $stmt->error);
	$stmt->close();
	
}

//สิ้นสุด Case Cancel

   header('Location: ../php/status.php');
    exit;



?>
