<?php
session_start();

if (!isset($_SESSION['emp_id'])) {
    header('Location: ../login/login.php');  // หรือ path ที่ถูกต้องของคุณ
    exit();
}

$emp_id = $_SESSION['emp_id'];

// เชื่อมฐานข้อมูล
$connect = new mysqli('localhost', 'amt', 'P@ssw0rd!amt', 'dbhelp', 3306);
// Check connection
if ($connect->connect_error) {
    die("something wrong.:" . $connect->connect_error);
}

// ดึง request_no ล่าสุด
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

// แสดงผล
//echo "เลขที่เอกสารใหม่: " . $requestNo;

// แสดงผล
//echo "เลขที่เอกสารใหม่: " . $requestNo;
// ดึงข้อมูลพนักงานจาก emp_id ที่ login เข้ามา
$stmt = $connect->prepare("
 SELECT 
  e.emp_name,
  d.dep_name,
  d.dep_id,
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

// ดึงรายการ department สำหรับ select
$depQuery = "SELECT dep_id, dep_name FROM emp_dep ORDER BY dep_id ASC";
$depResult = $connect->query($depQuery);

$optionsHtml = '';
while ($row = $depResult->fetch_assoc()) {
    $selected = ($row['dep_id'] == ($user['dep_id'] ?? '')) ? 'selected' : '';
    $optionsHtml .= '<option value="' . $row['dep_id'] . '" ' . $selected . '>' . htmlspecialchars($row['dep_name']) . '</option>';
}
$connect->close();

// กำหนดตัวแปรสำหรับเอาไปใช้ใน HTML
$empName    = $user['emp_name'] ?? '';
$department = $user['dep_name'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/transform.css">
    <title>IT Service Request Form</title>
</head>

<body style="text-transform: uppercase;">
    <div class="form-container" id="form-container">
        <div class="logo" style="display: flex; align-items:center; gap: 10px;">
            <img src="../image/am-ss-logo (New).jpg" alt="Logo" style="width: 100%; max-width:250px; height: auto;">
        </div>
        <div class="form-header">
            <h1 style="display:flex; justify-content:center; align-items:center; width:100%;">IT REQUEST FORM</h1>
        </div>
        <!-- Popup -->
        <div class="popup-overlay" id="popup">
            <div class="popup-content">
                <span class="popup-close" id="popup-close">✖</span>
                <h2>*โปรดอ่านก่อน"submit"*</h2>


                กรณีที่พนักงานมีความประสงค์จะขอสิทธิ์ใช้งาน <span style="color: red;">VPN</span> หรือ <span style="color: red;">Authorized</span> ขอความกรุณา อย่า รวมเรื่องร้องขออื่นๆ มาในแบบฟอร์มเดียวกัน
                เพื่อให้การดำเนินงานรวดเร็วและถูกต้อง <br><br>
                กรณีเลือกแบบฟอร์มใช้หัวข้องานไม่ถูกต้องจะถูกปฎิเสธคำขอ<span style="color: red;">"Reject"</span>เนื่องจากไม่เป็นไปตามมาตรฐาน ISO <br>โปรด <span style="color: red;">"submit"</span>ใหม่ให้ถูกต้อง
                <br><br>
                <button id="popup-ok">รับทราบ</button>
            </div>
        </div>

        <form enctype="multipart/form-data" id="problemForm">
            <!-- Employee ID -->
            <div class="form-group">
                <div class="form-group-half">
                    <label for="employee-id">Employee ID</label>
                    <input type="text" id="employee-id" value="<?= htmlspecialchars($emp_id) ?>" readonly>
                </div>
                <!-- Building Number -->
                <div class="form-group-half">
                    <label for="building-number">job no.</label>
                    <input type="text" id="building-number" value="<?= htmlspecialchars($requestNo) ?>" readonly>
                </div>
            </div>

            <!-- Full Name -->
            <div class="form-group">
                <div class="form-group-half">
                    <label for="full-name">Full Name</label>
                    <input type="text" id="full-name" value="<?= htmlspecialchars($empName) ?>" readonly>
                </div>
            </div>

            <!-- Department and Email -->
            <div class="form-group">
                <div class="form-group-half">
                    <label for="department">section</label>
                    <input type="text" id="department" name="department" value="<?= htmlspecialchars($department) ?>" readonly>
                </div>
            </div>



            <!-- Problem Category -->
            <div class="form-group">
                <label>job Category</label>
                <div class="checkbox-group">
                    <label><input type="radio" name="problem_id" value="1"> Hardware</label>
                    <label><input type="radio" name="problem_id" value="2"> Software</label>
                    <label><input type="radio" name="problem_id" value="3"> Network</label>
                    <label><input type="radio" name="problem_id" value="4"> ERP</label>
                    <label><input type="radio" name="problem_id" value="5"> Email</label>
                    <label><input type="radio" name="problem_id" value="6"> Port USB</label>
                    <label><input type="radio" name="problem_id" value="7"> VPN</label>
                    <label><input type="radio" name="problem_id" value="8"> Folder</label>
                    <label><input type="radio" name="problem_id" value="9"> Link Meeting</label>
                </div>
                <div id="departmentInput" class="department" style="display: none; margin-top: 10px;">
                    <select name="sub_department" id="problemSelect">
                        <option value="" disabled selected hidden>Authorized</option>
                        <?= $optionsHtml ?>
                    </select>
                </div>

                <!--select department -->
                <script>
                    const radios = document.querySelectorAll('input[name="problem_id"]');
                    const departmentInput = document.getElementById('departmentInput');
                    const departmentSelect = document.getElementById('problemSelect');
                    const form = document.getElementById('problemForm');

                    // แสดง/ซ่อน dropdown ตาม radio ที่เลือก
                    radios.forEach(radio => {
                        radio.addEventListener('change', function() {
                            if (this.value === '8') {
                                departmentInput.style.display = 'block';
                            } else {
                                departmentInput.style.display = 'none';
                                departmentSelect.value = ""; // reset ค่า dropdown
                            }
                        });
                    });

                    //ตรวจสอบก่อน submit
                    form.addEventListener('submit', function(e) {
                        const selectedRadio = document.querySelector('input[name="problem_id"]:checked');
                        if (selectedRadio && selectedRadio.value === '8') {
                            if (!departmentSelect.value) {
                                alert("กรุณาเลือกแผนก หากเลือก Folder");
                                e.preventDefault(); //ไม่ให้ส่งฟอร์ม
                            }
                        }
                    });
                </script>


            </div>

            <!-- Details Problem -->
            <div class="form-group">
                <label for="Details">Details</label>
                <textarea name="details" id="Details"></textarea>
            </div>

            <!-- Comments Manager -->
            <div class="form-group">
                <label for="attachment">ATTACHED DOCUMENTS (jpg/png/pdf)</label>
                <input type="file" id="attachment" name="attachment" accept="image/*,application/pdf">
            </div>
            <!-- Approved by -->
            <!-- <div class="form-group">
                <div class="form-group-half">
                    <label for="approved-by">Approved by</label>
                    <input type="text" id="approved-by" disabled>
                </div>
                <div class="form-group-half">
                    <label for="date">Date/Time</label>
                    <input type="date" id="date" disabled>
                </div>
            </div> -->
            <!-- DESCRIPTION OF WORK DONE -->
            <!-- <div class="form-group">
                <label for="description">Description of Work Done</label>
                <textarea id="description" disabled></textarea>
            </div> -->
            <!-- Support by -->
            <!-- <div class="form-group">
                <div class="form-group-half">
                    <label for="support-by">Support by</label>
                    <input type="text" id="sSupport-by" disabled>
                </div>
                <div class="form-group-half">
                    <label for="date">Date/time</label>
                    <input type="date" id="date" disabled>
                </div>
            </div> -->
            <!-- Accepted by -->
            <!-- <div class="form-group">
                <div class="form-group-half">
                    <label for="accepted-by">Accepted by</label>
                    <input type="text" id="accepted-by" disabled>
                </div>
                <div class="form-group-half">
                    <label for="date" class="light-text">Date/Time</label>
                    <input type="date" id="date" disabled>
                </div>
            </div> -->
            <!-- Submit Button -->
            <div class="form-footer">
                <button type="submit" formaction="../settingemail/sendEmail.php" formmethod="post">SUBMIT</button>
            </div>
        </form>
        <div class="footer-content" style="display: flex; justify-content:flex-end; align-items: center; margin-top: 20px;">
            <footer><p> FR-IT-16-V02-R00-01/10/25</p></footer>
        </div>
    </div>
    <script src="../js/itrequest.js"></script>
</body>

</html>