<?php
session_start();

if (!isset($_SESSION['emp_id'])) {
    header('Location: login.php');  // หรือ path ที่ถูกต้องของคุณ
    exit();
}

$emp_id = $_SESSION['emp_id'];

// เชื่อมฐานข้อมูล
$connect = new mysqli('localhost', 'amt', 'P@ssw0rd!amt', 'dbhelp', 3306);
// Check connection
if ($connect->connect_error) {
    die("something wrong.:" . $connect->connect_error);
}

// // เชื่อมฐานข้อมูล  เอาไว้ test
// $connect = new mysqli('localhost', 'root', '1234', 'db_itrequest', 3306);
// // Check connection
// if ($connect->connect_error) {
//     die("something wrong.:" . $connect->connect_error);
// }

// ดึงข้อมูลพนักงานจาก emp_id ที่ login เข้ามา
$stmt = $connect->prepare("
 SELECT 
  e.emp_name,
  d.dep_name,
  j.job_id
FROM employee AS e
JOIN emp_dep AS d 
  ON e.dep_id = d.dep_id
LEFT JOIN db_job AS j 
  ON e.emp_id = j.emp_id
WHERE e.emp_id = ?
ORDER BY j.job_created_at DESC
LIMIT 1;

");

if (!$stmt) {
    die("Prepare failed: " . $connect->error);
}

// bind พารามิเตอร์ (emp_id จาก session) และ execute
$stmt->bind_param("s", $_SESSION['emp_id']);
$stmt->execute();

// ดึงผลลัพธ์เป็น associative array
$user = $stmt->get_result()->fetch_assoc();

// ปิด statement และ connection (ต้องปิดทุกครั้งหลังใช้งาน)
$stmt->close();
$connect->close();

// กำหนดตัวแปรสำหรับเอาไปใช้ใน HTML
$empName    = $user['emp_name'] ?? '';
$department = $user['dep_name'] ?? '';
$requestNo  = $user['job_id'] ?? 'N/A';

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>User Profile</title>
</head>
<body class="bg-green-50 grid items-center justify-center min-h-screen">
    
    <div class="max-w-screen-xl mx-auto px-12 py-12 bg-white shadow-lg rounded-lg mt-12 border">
        <h1 class="text-3xl font-semibold text-Black-700 mb-8 text-center">User Profile</h1>
        <div class="space-y-8">
            <div class="flex justify-between items-center border-b pb-6">
                <span class="text-Black-600 font-medium text-lg">Full Name:</span>
                <span class="text-Black-800 font-semibold text-lg"><?= htmlspecialchars($empName) ?></span>
            </div>
            <div class="flex justify-between items-center border-b pb-6">
                <span class="text-Black-600 font-medium text-lg">Email:</span>
                <span class="text-Black-800 font-semibold text-lg ml-20">--@Example.com</span>
            </div>
            <div class="flex justify-between items-center border-b pb-6">
                <span class="text-Black-600 font-medium text-lg">ID:</span>
                <span class="text-Black-800 font-semibold text-lg"><?= htmlspecialchars($emp_id) ?></span>
            </div>
            <div class="flex justify-between items-center border-b pb-6">
                <span class="text-Black-600 font-medium text-lg">Department:</span>
                <span class="text-Black-800 font-semibold text-lg">IT</span>
            </div>
        <div class="mt-10 text-center">
            <button class="inline-flex items-center px-8 py-3 bg-green-500 text-white font-semibold rounded-lg shadow-md hover:bg-green-600 transition duration-300" onclick="window.location.href='./dashboard.php'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </button>
        </div>
    </div>
</body>
</html>