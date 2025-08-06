<?php
session_start();
session_regenerate_id(true);

// ตั้งค่าการเชื่อมต่อ
$host     = "localhost";
$user     = "amt";
$password = "P@ssw0rd!amt";
$db       = "dbhelp";
$port     = "3306";
$conn = new mysqli($host, $user, $password, $db, $port);
if ($conn->connect_error) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: ({$conn->connect_errno}) {$conn->connect_error}");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $employee_id    = $_POST['emp_id']       ?? '';
    $password_input = $_POST['emp_password'] ?? '';

    // 1) ดึงข้อมูลพนักงาน + role
    $sql  = "SELECT e.emp_username, e.emp_password, e.emp_name, e.dep_id, d.dep_name, p.emp_position_id AS role
         FROM employee AS e
         JOIN emp_dep  AS d ON e.dep_id = d.dep_id
         JOIN emp_position AS p ON e.emp_position_id = p.emp_position_id
         WHERE e.emp_username = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: ({$conn->errno}) {$conn->error}");
    }
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // 2) ตรวจสอบว่าเจอ user หรือไม่
    if ($row = $result->fetch_assoc()) {
        //  ถ้าใช้ hash:  password_verify($password_input, $row['emp_password'])
        if ($password_input === $row['emp_password']) {
            // 3) เก็บข้อมูลที่จำเป็นไว้ใน Session
            $_SESSION['emp_id']   = $row['emp_username'];
            $_SESSION['emp_name'] = $row['emp_name'];
            $_SESSION['dep_id']   = $row['dep_id'];
            $_SESSION['dep_name'] = $row['dep_name'];
            $_SESSION['role']     = $row['role'];

            //สร้าง boolean flag
            $_SESSION['Officer-Supervisor']     = $row['role'] === '1';
            $_SESSION['Section Manager']        = $row['role'] === '2';
            $_SESSION['Department Manager']     = $row['role'] === '3';
            $_SESSION['C Level ']               = $row['role'] === '4';
            $_SESSION['CEO']                    = $row['role'] === '5';
            $_SESSION['Admin']                  = $row['role'] === '6';


            $_SESSION['role'];
            if ($_SESSION['role'] && $_SESSION['emp_id'] == 'A1263') {
                header('Location: process_login_IT.php');
            } else if ($_SESSION['role'] >= '2') {
                header('Location: process_login_manager.php');
            } else if ($_SESSION['role'] && $_SESSION['emp_id']) {
                header('Location: process_login.php');
            } else {
                header('Location: login.php');
            }
            exit;

        } else {
            echo "❌ รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        echo "❌ ไม่พบผู้ใช้งานนี้";
    }

    $stmt->close();
}

$conn->close();
