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

echo $sql;
$mysqli = new mysqli('localhost', 'amt', 'P@ssw0rd!amt', 'dbhelp', 3306);
if ($mysqli->connect_error) {
  die('DB Connection Error: ' . $mysqli->connect_error);
}

$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// 1) นับจำนวนแถวที่มีสถานะ pending
$countSql = "SELECT COUNT(*) AS total FROM db_job WHERE job_status = 'pending'";
$countResult = $mysqli->query($countSql);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// 2) ดึงรายการในหน้านั้น
$stmt = $mysqli->prepare("SELECT db_job.job_id, db_job.job_details,db_job.remark_it,db_job_assignments.duedate FROM db_job INNER JOIN db_job_assignments ON db_job.job_id=db_job_assignments.job_id WHERE job_status = 'pending' ORDER BY job_id ASC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$jobs = $result->fetch_all(MYSQLI_ASSOC);



?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Project Status</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
  <link href='https://cdn.boxicons.com/fonts/animations.min.css' rel='stylesheet'>
  <link href='../css/button.css' rel='stylesheet'>
</head>

<body class="bg-green-50 uppercase">
  <style>
    .share-tech-regular {
      font-family: "Share Tech", sans-serif;
      font-weight: 400;
      font-style: normal;
    }
  </style>

   <header class="bg-white shadow-md">
    <div class="container mx-auto px-2 md:px-4 py-2
              flex flex-col sm:flex-row justify-between items-center">
      <!-- โลโก้ / ชื่อเว็บ -->
      <h1
        onclick="window.location.href='dashboard.php'"
        class="text-2xl font-bold text-green-500 hover:text-green-600
               transition-transform transform hover:scale-105"
      >
        IT REQUEST
        <span class="text-gray-800">ONLINE</span>
      </h1>

      <!-- เมนูหลัก: ใช้ ul > li > a -->
      <nav class="w-full sm:w-auto">
        <ul class="flex flex-wrap justify-center sm:justify-start space-x-2 sm:space-x-6">
          <!-- Home (ทุกคนเห็น) -->
          <li class="flex-1 sm:flex-none">
            <a
              href="./dashboard.php"
              class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1"
            >
              HOME
              <span
                class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"
              ></span>
            </a>
          </li>
          <?php if (isset($_SESSION['dep_id']) && $_SESSION['dep_id'] == 17 && isset($_SESSION['emp_id']) && $_SESSION['emp_id'] !== 'A1263'): ?>
            <li class="flex-1 sm:flex-none">
              <a
                href="../IT_Support/Admin.php"
                class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1"
              >
                job ASSIGN
                <span
                  class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"
                ></span>
              </a>
            </li>
          <?php endif; ?>
          <!-- Manager (role >= 2) -->
          <?php if (isset($_SESSION['role']) && $_SESSION['role'] >= 2 && $_SESSION['emp_id'] !== 'A1263'): ?>
            <li class="flex-1 sm:flex-none">
              <a
                href="../manager/Admin.php"
                class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1"
              >
                APPROVED
                <span
                  class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"
                ></span>
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
                class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1"
              >
                APPROVED
                <span
                  class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"
                ></span>
              </a>
            </li>
          <?php endif; ?>

          <!-- Check Request (role == 1) -->
          <?php if (isset($_SESSION['role']) && $_SESSION['role']): ?>
            <li class="flex-1 sm:flex-none">
              <a
                href="./checkrequest.php"
                class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1"
              >
                ACCEPTED
                <span
                  class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"
                ></span>
              </a>
            </li>
          <?php endif; ?>

            <!-- job status (ทุกคนเห็น) -->
          <li class="flex-1 sm:flex-none">
            <a
              href="./jobstatus.php"
              class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1"
            >
              job status
              <span
                class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"
              ></span>
            </a>
          </li>

          <!-- Request history (ทุกคนเห็น) -->
          <li class="flex-1 sm:flex-none">
            <a
              href="./history.php"
              class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1"
            >
              job HISTORY
              <span
                class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"
              ></span>
            </a>
          </li>

          <!-- Status (ทุกคนเห็น) -->
          <li class="flex-1 sm:flex-none">
            <a
              href="./status.php"
              class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1"
            >
              queue
              <span
                class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"
              ></span>
            </a>
          </li>

          <!-- Logout (ทุกคนเห็น) -->
          <li class="flex-1 sm:flex-none">
            <a
              href="../login/logout.php"
              class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1"
            >
              LOGOUT
              <span
                class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"
              ></span>
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
  <!-- <div class="relative overflow-hidden shadow-lg  h-40 sm:h-52 md:h-64 lg:h-72 group">
    <img
      src="../image/banner_06.png"
      alt="Go Green Banner"
      class="w-full h-full object-cover transition-transform duration-700 ease-in-out group-hover:scale-105 group-hover:brightness-90"
      style="object-position: center 50%;"
    >
    <div class="absolute inset-0 bg-gradient-to-r from-green-400/40 via-transparent to-green-600/30 pointer-events-none"></div>
    <div class="absolute bottom-4 left-4 sm:left-6 text-white drop-shadow-lg">
      <h2 class="text-xl sm:text-2xl font-bold tracking-wide">Switch to Digital</h2>
      <p class="text-xs sm:text-sm mt-1 opacity-90">ร่วมสร้างสรรค์สิ่งแวดล้อมที่ดีขึ้นกับเรา</p>
    </div>
  </div> -->

  <!-- เนื้อหาหลัก -->
  <div class="w-full mx-auto flex flex-col">
    <!-- Progress Steps (ถ้าใช้ Alpine.js ต้อง include Alpine.js ด้วย) -->
    <div class="flex flex-col sm:flex-row items-center sm:space-x-4">
      <template x-for="(step, i) in steps" :key="i">
        <div class="flex items-center mb-4 sm:mb-0">
          <div
            :class="step.status === 'done' ? 'bg-green-500' : step.status === 'current' ? 'bg-orange-400' : 'bg-gray-300'"
            class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm"
          >
            <template x-if="step.status === 'done'">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </template>
            <template x-if="step.status !== 'done'">
              <span x-text="i + 1"></span>
            </template>
          </div>
          <span class="ml-2 text-gray-600 text-sm" x-text="step.name"></span>
          <div x-show="i < steps.length - 1" class="hidden sm:block flex-1 h-0.5 bg-gray-300 mx-4"></div>
        </div>
      </template>
    </div>

<!-- Task Table Section -->
<div class="w-full overflow-x-auto">
  <div class="bg-white shadow-xl  overflow-hidden border border-green-100">
    <!-- Header บนตาราง -->
    <div class="bg-gradient-to-r flex justify-center from-green-400 via-emerald-500 to-green-600 px-6 py-4 sm:px-8 sm:py-6">
      <h3 class="text-white text-lg sm:text-xl font-bold tracking-wide flex items-center gap-2">
      📋 Queue
      <!-- <i class="bx bxs-fire bx-bounce"></i> -->
      </h3>
    </div>

    <!-- ตารางข้อมูล -->
    <table class="min-w-full text-center table-auto bg-white">
      <thead class="bg-green-50 text-emerald-800 text-sm sm:text-base uppercase font-bold tracking-wide">
        <tr>
          <th class="px-4 sm:px-6 py-3">No.</th>
          <th class="px-4 sm:px-6 py-3">Job no</th>
          <th class="px-4 sm:px-6 py-3">Details</th>
          <th class="px-4 sm:px-6 py-3">Due date</th>
          <th class="px-4 sm:px-6 py-3">remark</th>
        </tr>
      </thead>
   <tbody class="divide-y divide-green-100 text-sm sm:text-base">
  <?php if (count($jobs) > 0): ?>
    <?php foreach ($jobs as $index => $job): ?>
      <tr class="hover:bg-green-50 transition">
        <td class="px-4 sm:px-6 py-3 text-gray-700 font-medium">
          <?= ($offset + $index + 1) ?>
        </td>
        <td class="px-4 sm:px-6 py-3 text-emerald-700 font-semibold">
          <?= htmlspecialchars($job['job_id']) ?>
        </td>

        <!-- job_details: ถ้ายาวเกิน 10 ให้ตัด + ทำให้คลิกดู popup ได้, ถ้าไม่เกิน 10 ให้โชว์เต็มเลย -->
        <?php
          $original = $job['job_details'];
          $escapedFull = htmlspecialchars($original, ENT_QUOTES);
		  $duedate=$job['duedate'];
		  $remarkit=$job['remark_it'];
          if (mb_strlen($original, 'UTF-8') > 10) {
            // ตัด 10 ตัวแรก แล้วต่อ "..."
            $shortText = mb_substr($original, 0, 10, 'UTF-8');
            $escapedShort = htmlspecialchars($shortText, ENT_QUOTES) . '...';

            // แสดง truncated พร้อมทำให้คลิกดูเต็มใน popup
            $cellContent = '<span '
                         . 'class="text-black-600 cursor-pointer no-underline view-detail " '
                         . 'data-detail="' . $escapedFull . '">'
                         . $escapedShort
                         . '</span>';
          } else {
            // ไม่เกิน 10 ให้โชว์เต็มเลย (ไม่ต้องคลิก)
            $cellContent = $escapedFull;
          }
        ?>
        <td class="px-4 sm:px-6 py-3 text-gray-700">
          <?= $cellContent ?>
        </td>
		 <td class="px-4 sm:px-6 py-3 text-gray-700">
          <?= $duedate ?>
        </td>
		 <td class="px-4 sm:px-6 py-3 text-gray-700">
          <?= $remarkit ?>
        </td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr>
      <td colspan="3" class="px-4 sm:px-6 py-3 text-gray-400 italic">
        No pending jobs found
      </td>
    </tr>
  <?php endif; ?>
</tbody>

    </table>
    <!-- Pagination -->
        <div class="mt-4 mb-4 flex flex-wrap justify-center space-x-1 pb-2">
          <?php
          $maxPagesToShow = 5;
          $startPage = max(1, $page - floor($maxPagesToShow / 2));
          $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
          $startPage = max(1, $endPage - $maxPagesToShow + 1);

          for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a
              href="?page=<?= $i ?>"
              class="px-2 sm:px-3 py-1 border rounded <?= $i == $page ? 'bg-green-500 text-white' : 'bg-white text-gray-700 hover:bg-green-100' ?>"
            >
              <?= $i ?>
            </a>
          <?php endfor; ?>

          <?php if ($endPage < $totalPages): ?>
            <a
              href="?page=<?= $endPage + 1 ?>"
              class="px-2 sm:px-3 py-1 border rounded bg-white text-gray-700 hover:bg-green-100"
            >
              ถัดไป →
            </a>
          <?php endif; ?>
  </div>
</div>


        
        </div>
      </div>
    </div>
  </div>
</body>

</html>