<?php
session_start();
if (!isset($_SESSION['emp_id'])) {
  header('Location: login.php');
  exit();
}
//ปุ่มกดกลับ
$back = '#';
// echo $_SESSION['role'];
if ($_SESSION['role'] > 2 && ($_SESSION['emp_id'] == 'A1263')) {
  $back = ('../manager_IT/dashboardmgr_it.php');
} else if ($_SESSION['role'] >= 2) {
  $back = ('../manager/dashboardmgr.php');
} else if ($_SESSION['role'] && ($_SESSION['dep_id'] == 17)) {
  $back = ('../IT_Support/dashboard_ITsuport.php');
} else {
  $back = ('dashboard.php');
}


$empId = $_SESSION['emp_id'] ?? null;

if (!$empId) {
  die("ไม่พบรหัสพนักงาน");
}

$mysqli = new mysqli('localhost', 'amt', 'P@ssw0rd!amt', 'dbhelp', 3306);
if ($mysqli->connect_error) {
  die('DB Connection Error: ' . $mysqli->connect_error);
}

$limit = 10; // จำนวนแถวต่อหน้า
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

//รายการที่ต้องการแสดง
$jobstatus = ['completed', 'cancel', 'rejected'];
//เตรียม SQL แบบ dynamic สำหรับ IN (?,?,?)
$placeholders = implode(',', array_fill(0, count($jobstatus), '?'));

//นับจำนวนรายการทั้งหมดที่เข้าเงื่อนไข
$sql = "SELECT job_id, job_details, detail_Rejected, job_status FROM db_job WHERE emp_id = ? AND job_status IN ($placeholders) LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($sql);

// ประเภทข้อมูล: emp_id (s) + jobstatus 3 ตัว (sss) + limit (i) + offset (i)
$types = "s" . str_repeat('s', count($jobstatus)) . "ii";
$params = array_merge([$empId], $jobstatus, [$limit, $offset]); // รวมค่าที่จะ bind
$stmt->bind_param($types, ...$params); // bind parameter ทั้งหมด
$stmt->execute(); // รัน query
$result = $stmt->get_result(); // ดึงผลลัพธ์
$jobs = $result->fetch_all(MYSQLI_ASSOC); // ดึงทั้งหมดในรูป array
$stmt->close(); // ปิด statement
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Project Status</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href='../css/button.css' rel='stylesheet'>
</head>

<body class="bg-green-50 uppercase ">

  <header class="bg-white shadow-md">
    <div class="container mx-auto px-2 md:px-4 py-2
              flex flex-col sm:flex-row justify-between items-center">
      <!-- โลโก้ / ชื่อเว็บ -->
      <h1
        onclick="window.location.href='dashboard.php'"
        class="text-2xl font-bold text-green-500 hover:text-green-600
               transition-transform transform hover:scale-105">
        IT REQUEST
        <span class="text-gray-800">ONLINE</span>
      </h1>

      <!-- เมนูหลัก: ใช้ ul > li > a -->
      <nav class="w-full sm:w-auto mt-4 sm:mt-0">
        <ul class="flex flex-wrap justify-center sm:justify-start space-x-2 sm:space-x-6">
          <!-- Home (ทุกคนเห็น) -->
          <li class="flex-1 sm:flex-none">
            <a
              href="./dashboard.php"
              class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1">
              HOME
              <span
                class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"></span>
            </a>
          </li>
          <?php if (isset($_SESSION['dep_id']) && $_SESSION['dep_id'] == 17 && isset($_SESSION['emp_id']) && $_SESSION['emp_id'] !== 'A1263'): ?>
            <li class="flex-1 sm:flex-none">
              <a
                href="../IT_Support/Admin.php"
                class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1">
                job ASSIGN
                <span
                  class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"></span>
              </a>
            </li>
          <?php endif; ?>
          <!-- Manager (role >= 2) -->
          <?php if (isset($_SESSION['role']) && $_SESSION['role'] >= 2 && $_SESSION['emp_id'] !== 'A1263'): ?>
            <li class="flex-1 sm:flex-none">
              <a
                href="../manager/Admin.php"
                class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1">
                APPROVED
                <span
                  class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"></span>
              </a>
            </li>
          <?php endif; ?>

          <!-- IT Manager (role > 2 && emp_id == 'A1263') -->
          <?php if (
            isset($_SESSION['role']) && $_SESSION['role'] > 2
            && isset($_SESSION['emp_id']) && $_SESSION['emp_id'] === 'A1263'
          ): ?>
            <li class="flex-1 sm:flex-none">
              <a
                href="../manager_IT/Admin_IT.php"
                class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1">
                APPROVED
                <span
                  class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"></span>
              </a>
            </li>
          <?php endif; ?>

          <!-- Check Request (role == 1) -->
          <?php if (isset($_SESSION['role']) && $_SESSION['role']): ?>
            <li class="flex-1 sm:flex-none">
              <a
                href="./checkrequest.php"
                class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1">
                ACCEPTED
                <span
                  class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"></span>
              </a>
            </li>
          <?php endif; ?>
          <!-- Status (ทุกคนเห็น) -->
          <li class="flex-1 sm:flex-none">
            <a
              href="./jobstatus.php"
              class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1">
              job STATUS
              <span
                class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"></span>
            </a>
          </li>
          <!-- Request history (ทุกคนเห็น) -->
          <li class="flex-1 sm:flex-none">
            <a
              href="./history.php"
              class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1">
              job HISTORY
              <span
                class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"></span>
            </a>
          </li>

          <!-- Status (ทุกคนเห็น) -->
          <li class="flex-1 sm:flex-none">
            <a
              href="./status.php"
              class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1">
              queue
              <span
                class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"></span>
            </a>
          </li>

          <!-- Logout (ทุกคนเห็น) -->
          <li class="flex-1 sm:flex-none">
            <a
              href="../login/logout.php"
              class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1">
              LOGOUT
              <span
                class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"></span>
            </a>
          </li>
        </ul>
      </nav>

      <!-- ปุ่ม Create a request -->
      <a href="../documents/tranform.php" class="create mt-3 sm:mt-0 px-4 py-2">
        <span>Create a request →</span>
        <div class="liquid"></div>
      </a>
    </div>
  </header>
  <!-- Banner / Hero -->
  <!-- <div class="relative overflow-hidden shadow-lg h-40 sm:h-52 md:h-64 lg:h-72 group">
    <img
      src="../image/banner_06.png"
      alt="Go Green Banner"
      class="w-full h-full object-cover transition-transform duration-700 ease-in-out group-hover:scale-105 group-hover:brightness-90"
      style="object-position: center 50%;">
    <div class="absolute inset-0 bg-gradient-to-r from-green-400/40 via-transparent to-green-600/30 pointer-events-none"></div>
    <div class="absolute bottom-4 left-4 sm:left-6 text-white drop-shadow-lg">
      <h2 class="text-xl sm:text-2xl font-bold tracking-wide">Switch to Digital</h2>
      <p class="text-xs sm:text-sm mt-1 opacity-90">ร่วมสร้างสรรค์สิ่งแวดล้อมที่ดีขึ้นกับเรา</p>
    </div>
  </div> -->




  <div class="bg-white  shadow-lg w-full pt-6  flex flex-col items-center">
    <!-- Header -->
    <!-- Header Title -->
    <div class="flex justify-center items-center mb-6">
      <h2 class="text-2xl font-bold text-emerald-700 tracking-wide">📌 job HISTORY</h2>
    </div>

    <!-- Progress Steps (Eco Style) -->
    <div class="flex items-center mb-6 space-x-4 w-full px-4">
      <template x-for="(step, i) in steps" :key="i">
        <div class="flex items-center">
          <div
            :class="step.status === 'done' ? 'bg-emerald-500' : step.status === 'current' ? 'bg-lime-400' : 'bg-gray-300'"
            class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-semibold shadow-md">
            <template x-if="step.status === 'done'">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </template>
            <template x-if="step.status !== 'done'">
              <span x-text="i+1"></span>
            </template>
          </div>
          <span class="ml-2 text-sm text-green-800 font-medium" x-text="step.name"></span>
          <div x-show="i < steps.length - 1" class="flex-1 h-0.5 bg-green-200 mx-3"></div>
        </div>
      </template>
    </div>

    <!-- Task Table (Eco Theme) -->
    <div class="overflow-x-auto w-full">
      <div class="shadow-lg rounded-xl overflow-hidden border border-green-100">
        <table class="min-w-full text-left bg-white">
          <thead class="bg-green-50 text-emerald-800 uppercase text-sm font-bold tracking-wide">
            <tr>
              <th class="px-4 py-3 text-center">job No.</th>
              <th class="px-4 py-3 text-center">Details</th>
              <th class="px-4 py-3 text-center">reason for rejection</th>
              <th class="px-4 py-3 text-center">job status</th>
              <!-- <th class="px-4 py-3 text-center">ACTION</th> -->
            </tr>
          </thead>
          <tbody class="divide-y divide-green-100 text-sm">
            <?php if (count($jobs) > 0): ?>
              <?php foreach ($jobs as $index => $job): ?>
                <tr class="hover:bg-green-50 transition">
                  <!-- เลข Job ID -->
                  <td class="px-4 py-2 text-green-600 font-medium text-center align-middle">
                    <?= htmlspecialchars($job['job_id']) ?>
                  </td>

                  <!-- job_details: แสดงแค่ 10 ตัวอักษร แล้วเก็บฉบับเต็มใน data-detail -->
                  <?php
                  $fullDetails   = htmlspecialchars($job['job_details'], ENT_QUOTES);
                  $truncatedJob  = mb_strlen($job['job_details'], 'UTF-8') > 10
                    ? htmlspecialchars(mb_substr($job['job_details'], 0, 10, 'UTF-8'), ENT_QUOTES) . '...<span style="color:red;">(เพิ่มเติม)</span>'
                    : $fullDetails;
                  ?>
                  <td class="px-4 py-2 text-gray-800 text-center align-middle">
                    <span
                      class="text-gray-600 cursor-pointer view-detail"
                      data-detail="<?= $fullDetails ?>">
                      <?= $truncatedJob ?>
                    </span>
                  </td>

                  <!-- detail_Rejected: แสดงแค่ 10 ตัวอักษรเช่นกัน -->
                  <?php
                  $fullRejected  = htmlspecialchars($job['detail_Rejected'], ENT_QUOTES);
                  $truncatedRej  = mb_strlen($job['detail_Rejected'], 'UTF-8') > 10
                    ? htmlspecialchars(mb_substr($job['detail_Rejected'], 0, 10, 'UTF-8'), ENT_QUOTES) . '...<span style="color:red;">(เพิ่มเติม)</span>'
                    : $fullRejected;
                  ?>
                  <td class="px-4 py-2 text-gray-500 italic text-center align-middle">
                    <span
                      class="text-blue-600 cursor-pointer view-detail"
                      data-detail="<?= $fullRejected ?>">
                      <?= $truncatedRej ?>
                    </span>
                  </td>
                  <!-- สถานะงาน -->
                  <td class="px-4 py-2 font-semibold text-center align-middle">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold
            <?php
                switch ($job['job_status']) {
                  case 'rejected':
                    echo 'bg-red-100 text-red-700';
                    break;
                  case 'in_progress':
                    echo 'bg-yellow-100 text-yellow-700';
                    break;
                  case 'done':
                    echo 'bg-emerald-100 text-emerald-700';
                    break;
                  case 'pending':
                    echo 'bg-lime-100 text-lime-700';
                    break;
                  case 'waiting_approval':
                    echo 'bg-green-200 text-green-800';
                    break;
                     case 'completed':
                    echo 'bg-green-200 text-green-800';
                    break;
                     case 'cancel':
                    echo 'bg-red-200 text-red-800';
                    break;
                  default:
                    echo 'bg-gray-100 text-gray-700';
                    break;
                }
            ?>
          ">
                      <?= htmlspecialchars($job['job_status']) ?>
                    </span>
                  </td>
                  <!-- <td class="text-center align-middle px-4 py-2">
                    <div class="flex flex-row gap-2 items-center justify-center">
                      <?php if ($job['job_status'] === 'waiting_approval'): ?>
                        <a
                          class="Reject bg-red-100 hover:bg-red-600 hover:text-white text-red-700 px-4 py-1 rounded shadow transition flex items-center justify-center"
                          style="min-width: 80px;"
                          href="../settingemail/sendEmail1.php?job_id=<?= urlencode($job['job_id']) ?>&status=10">
                          Cancel
                        </a>
                      <?php endif; ?>
                    </div>
                  </td> -->
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="px-4 sm:px-6 py-3 text-gray-400 italic text-center">
                  No jobs found for you
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
        <?php if (count($jobs) > 0): ?>
          <div class="mt-4 flex justify-center items-center space-x-1 p-5">
            <?php
            $maxPagesToShow = 5;
            $startPage = max(1, $page - floor($maxPagesToShow / 2));
            $endPage   = min($totalPages, $startPage + $maxPagesToShow - 1);
            $startPage = max(1, $endPage - $maxPagesToShow + 1); // ปรับกลับกรณีท้ายสุด
            ?>

            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
              <a
                href="?page=<?= $i ?>"
                class="px-3 py-1 border rounded <?= ($i == $page)
                                                  ? 'bg-green-500 text-white'
                                                  : 'bg-white text-gray-700 hover:bg-green-100' ?>">
                <?= $i ?>
              </a>
            <?php endfor; ?>

            <?php if ($endPage < $totalPages): ?>
              <a
                href="?page=<?= $endPage + 1 ?>"
                class="px-3 py-1 border rounded bg-white text-gray-700 hover:bg-green-100">
                ถัดไป →
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  </div>
  <!-- Modal Overlay -->
  <div
    id="detailModal"
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
    <!-- เปลี่ยนจาก w-full max-w-lg เป็น inline-block เพื่อให้ขยายอัตโนมัติตามเนื้อหา -->
    <div
      class="bg-white rounded-lg shadow-lg p-4 mx-4 inline-block max-h-[90vh] overflow-auto">
      <div class="flex justify-between items-start border-b pb-2 mb-2">
        <h2 class="text-lg font-bold">รายละเอียดฉบับเต็ม</h2>
        <button id="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <!-- เปลี่ยน w-full ให้กลายเป็นความกว้างเริ่มต้น แล้วลากขยายได้: -->
      <textarea
        id="modalContent"
        readonly
        class="min-w-[300px] h-40 p-2 border border-gray-300 rounded resize"></textarea>
    </div>
  </div>



  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const detailElements = document.querySelectorAll('.view-detail');
      const modalOverlay = document.getElementById('detailModal');
      const modalTextarea = document.getElementById('modalContent');
      const closeButton = document.getElementById('closeModal');

      detailElements.forEach(function(el) {
        el.addEventListener('click', function() {
          const fullText = this.getAttribute('data-detail') || '';
          // ใส่ค่าเข้า textarea แทน textContent
          modalTextarea.value = fullText;
          modalOverlay.classList.remove('hidden');
          // โฟกัสไปที่ textarea เผื่อจะ scroll ดูได้สะดวก
          modalTextarea.focus();
        });
      });

      closeButton.addEventListener('click', function() {
        modalOverlay.classList.add('hidden');
        modalTextarea.value = '';
      });

      modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
          modalOverlay.classList.add('hidden');
          modalTextarea.value = '';
        }
      });
    });
  </script>

</body>

</html>