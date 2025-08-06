<?php
session_start();
// ตรวจสอบให้ผู้ใช้ล็อกอินและมีสิทธิ์ Manager/IT
if (!isset($_SESSION['role']) || $_SESSION['role'] < 2) {
    header('Location: login.php');
    exit;
}

//ปุ่มกดกลับ
$back = '#';
// echo $_SESSION['role'];
if ($_SESSION['role'] > 2 && ($_SESSION['emp_id'] == 'A1263')) {
    $back = ('../manager_IT/dashboardmgr_it.php');
} else if ($_SESSION['role'] >= 2) {
    $back = ('../manager/dashboardmgr.php');
} else {
    $back = ('dashboard.php');
}

// เชื่อมต่อฐานข้อมูล
$mysqli = new mysqli('localhost', 'amt', 'P@ssw0rd!amt', 'dbhelp', 3306);
if ($mysqli->connect_error) {
    die('DB Connection Error: ' . $mysqli->connect_error);
}

//ดึงข้อมูลจากตาราง db_job ที่มีสถานะ 'waiting_approval'และ mgrapprove ให้ตรงกับ emp_id ของผู้ใช้
$empIdd = isset($_SESSION['emp_id']) ? $_SESSION['emp_id'] : null;
$stmt = $mysqli->prepare("
    SELECT
        j.job_id,
        j.emp_id,
        e.emp_name,
        p.problem_name AS problem_category,
        j.job_status,
        j.job_created_at,
        j.mgrapprove,
        j.job_details
    FROM db_job AS j
    JOIN employee   AS e ON j.emp_id     = e.emp_id
    JOIN db_problem AS p ON j.problem_id = p.problem_id
    WHERE j.job_status = 'waiting_approval'
        AND j.mgrapprove = ?
    ORDER BY j.job_created_at ASC
");
// $job_details='job_details';
$stmt->bind_param('s', $empIdd);
$stmt->execute();
$result = $stmt->get_result();
$jobs = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/button.css">


    <!-- <link rel="stylesheet" href="../css/tranform.css"> -->
</head>

<body class="bg-green-50 font-sans transition-colors duration-300 uppercase">
    <style>
        body {
            font-family: Arial, sans-serif;
            /* background-color:rgb(59, 46, 46); */
            margin: 0;
            padding: 0;
        }

        .form-container {
            width: 100vw;
            height: 100vh;
            max-height: 100vh;
            max-width: 100vw;
            margin: 0;
            padding: 0;
            background-color: #fff;
            border-radius: 0;
            box-shadow: none;
        }

        .form-header {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            table-layout: auto;
            border-style: solid;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        @theme {
            --animate-wiggle: wiggle 1s ease-in-out infinite;

            @keyframes wiggle {

                0%,
                100% {
                    transform: rotate(-3deg);
                }

                50% {
                    transform: rotate(3deg);
                }
            }
        }
    </style>
    <!-- Navbar -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4 md:px-6 py-2 flex justify-between items-center">
            <!-- โลโก้ / ชื่อเว็บ -->
            <h1
                onclick="window.location.href='../php/dashboard.php'"
                class="text-2xl font-bold text-green-500 hover:text-green-600
               transition-transform transform hover:scale-105">
                IT REQUEST
                <span class="text-gray-800">ONLINE</span>
            </h1>

            <!-- เมนูหลัก: ใช้ ul > li > a -->
            <nav class="w-full sm:w-auto">
                <ul class="flex flex-wrap justify-center sm:justify-start space-x-2 sm:space-x-6">
                    <!-- Home (ทุกคนเห็น) -->
                    <li class="flex-1 sm:flex-none">
                        <a
                            href="../php/dashboard.php"
                            class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1">
                            HOME
                            <span
                                class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"></span>
                        </a>
                    </li>

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
                                href="../php/checkrequest.php"
                                class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1">
                                ACCEPTED
                                <span
                                    class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"></span>
                            </a>
                        </li>
                    <?php endif; ?>
                      <!-- job status (ทุกคนเห็น) -->
                    <li class="flex-1 sm:flex-none">
                        <a
                            href="../php/jobstatus.php"
                            class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1">
                            job status
                            <span
                                class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"></span>
                        </a>
                    </li>
                    <!-- Request history (ทุกคนเห็น) -->
                    <li class="flex-1 sm:flex-none">
                        <a
                            href="../php/history.php"
                            class="relative block text-center sm:text-left text-gray-600 hover:text-green-500 transition-colors duration-200 group py-1">
                            job HISTORY
                            <span
                                class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500 transition-all duration-300 group-hover:w-full"></span>
                        </a>
                    </li>

                    <!-- Status (ทุกคนเห็น) -->
                    <li class="flex-1 sm:flex-none">
                        <a
                            href="../php/status.php"
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
    </div>
    <div class="form-container" style="form-container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 shadow-md bg-white rounded-lg">
        <div class="form-header">
            <div>
                <div class="bg-gradient-to-r from-green-100 via-blue-100 to-purple-100 rounded-xl shadow-lg p-8 mb-6 border border-gray-200">
                    <div class="flex flex-col items-center justify-center space-y-3">
                        <div class="relative">
                        </div>
                        <h1 class="text-3xl font-extrabold text-sky-600 tracking-tight drop-shadow">considered by</h1>
                        <p class="text-gray-500 text-base text-center max-w-md">
                            ผู้จัดการ/หัวหน้างาน พิจารณาคำขอ
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-center">
            <button class="inline-flex gap-2 rounded-md border border-indigo-100 bg-white px-3 py-2 text-sm font-semibold text-indigo-600 shadow-sm transition-colors duration-150 hover:border-indigo-600 hover:bg-indigo-600 hover:text-white dark:border-transparent" onclick="window.location.reload()">
                <svg viewBox="0 0 20 20" class="size-5 fill-current">
                    <path d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H3.989a.75.75 0 0 0-.75.75v4.242a.75.75 0 0 0 1.5 0v-2.43l.31.31a7 7 0 0 0 11.712-3.138.75.75 0 0 0-1.449-.39Zm1.23-3.723a.75.75 0 0 0 .219-.53V2.929a.75.75 0 0 0-1.5 0V5.36l-.31-.31A7 7 0 0 0 3.239 8.188a.75.75 0 1 0 1.448.389A5.5 5.5 0 0 1 13.89 6.11l.311.31h-2.432a.75.75 0 0 0 0 1.5h4.243a.75.75 0 0 0 .53-.219Z" fill-rule="evenodd" clip-rule="evenodd"></path>
                </svg>
                <font style="vertical-align: inherit;">
                    <font style="vertical-align: inherit;">REFRESH</font>
                </font>
            </button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>job no.</th>
                    <!-- <th>Emp ID</th> -->
                    <th>requester</th>
                    <th>job category</th>
                    <!-- <th>detail</th> -->
                    <th>submit date</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($jobs)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded shadow text-center">
                                ไม่มีงานที่รออนุมัติ
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <tr class="hover:bg-green-50 transition">
                            <td>
                                <div class="detail-btn blinking solid bg-gray-100 rounded-lg shadow p-2 text-gray-800 font-semibold text-center cursor-pointer
            underline decoration-green-500 underline-offset-4
            transition duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg hover:ring-2 hover:ring-green-400"
                                    data-details="<?= htmlspecialchars($job['job_details'], ENT_QUOTES) ?>"><?= htmlspecialchars($job['job_id']) ?>
                                </div>
                            </td>
                            <style>
                            @keyframes blink {
                             0%, 100% { opacity: 1; }
                                50% { opacity: 0.4; }
                        }

                        .blinking {
                            animation: blink 1s infinite;
                        }
                            </style>
                            <!-- <td>
                                <div class="bg-gray-50 rounded-lg shadow p-2 text-gray-700 text-center">
                                    <?= htmlspecialchars($job['emp_id']) ?>
                                </div>
                            </td> -->
                            <td>
                                <div class="bg-gray-100 rounded-lg shadow p-2 text-gray-700 text-center">
                                    <?= htmlspecialchars($job['emp_name']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="bg-gray-100 rounded-lg shadow p-2 text-gray-700 text-center">
                                    <?= htmlspecialchars($job['problem_category']) ?>
                                </div>
                            </td>
                            <!-- <td><?= htmlspecialchars($job['job_details']) ?></td> -->
                            <td>
                                <div class="bg-gray-100 rounded-lg shadow p-2 text-gray-700 text-center">
                                    <?= htmlspecialchars($job['job_created_at']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-row gap-2 items-center justify-center">
                                    <a class="Action bg-green-100 hover:bg-green-600 hover:text-white text-green-700 px-4 py-1 rounded shadow transition"
                                        href="handle_request.php?req_no=<?= $job['job_id'] ?>&action=approve">
                                        APPROVE
                                    </a>
                                    <a class="Reject bg-red-100 hover:bg-red-600 hover:text-white text-red-700 px-4 py-1 rounded shadow transition"
                                        href="#" onclick="cancelTeamModal('<?= $job['job_id'] ?>')">
                                        REJECT
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

 <div
  id="detailModal"
  class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm hidden z-50"
>
  <!-- เปลี่ยนจาก w-11/12 max-w-md เป็น inline-block เพื่อให้ขยายตาม textarea -->
  <div class="bg-white rounded-2xl shadow-xl mx-4 p-6 inline-block max-h-[90vh] overflow-auto">
    <!-- หัวเรื่อง -->
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Request details</h2>

    <!-- เปลี่ยนเป็น textarea ให้ปรับขนาดได้ -->
    <textarea
      id="modalContent"
      readonly
      class="min-w-[300px] h-40 p-2 border border-gray-300 rounded resize"
    ></textarea>

    <!-- ปุ่มปิด -->
    <div class="mt-6 text-right">
      <button
        id="closeModal"
        class="uppercase px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg shadow transition"
      >
        Close
      </button>
    </div>
  </div>
</div>
   <!-- Modal Overlay -->
<div
  id="detailModal"
  class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm hidden z-50"
>
  <!-- ปรับ container ให้ขยายตาม textarea -->
  <div class="bg-white rounded-2xl shadow-xl mx-4 p-0 inline-block max-h-[90vh] overflow-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-4 px-6 pt-6">รายละเอียดงาน</h2>
    <!-- ปรับ textarea ให้เต็มความกว้างและไม่มี padding ซ้อน -->
    <div class="bg-blue-50 rounded-b-2xl px-6 pb-6 pt-2">
      <textarea
        id="modalContent"
        readonly
        class="w-full h-40 p-3 border border-gray-300 rounded resize bg-blue-50 text-gray-800"
        style="min-width:200px; min-height:120px; max-width:100vw; max-height:50vh;"
      ></textarea>
      <div class="mt-6 text-right">
        <button
          id="closeModal"
          class="uppercase px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg shadow transition"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</div>
            </form>
        </div>
        <div id="Cancelteam" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm hidden z-50">
            <div class="bg-white rounded-2xl shadow-xl  mx-auto p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Assignment - Reject</h2>
                <form id="rejectForm" method="post" action="../manager_IT/sendmail_IT.php">
                    <input type="hidden" name="job_id" id="rejectJobId">
                    <input type="hidden" name="action" value="reject">
                    <div class="mb-4">
                        <label for="reject_reason" class="block text-gray-700 font-semibold mb-2">Please specify the reason for refusal.</label>
                        <textarea name="reject_reason" id="reject_reason" rows="4" required
                            class="min-w-[350px] h-40 p-2 border border-gray-300 rounded resize"></textarea>
                    </div>
                    <div class="mt-6 text-right">
                        <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg shadow transition uppercase">
                            ok
                        </button>
                        <button type="button" id="closeCancelTeamModal"
                            class="px-4 py-2 bg-gray-400 hover:bg-gray-600 text-white rounded-lg shadow transition uppercase">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
    </div>
    </div>
    </div>
    </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('detailModal');
            const contentEl = document.getElementById('modalContent');
            const closeBtn = document.getElementById('closeModal');

            // ผูก Event ให้ปุ่มปิด Modal
            closeBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
            });

            // ผูกให้ปุ่มในแต่ละแถวเปิด Modal
            document.querySelectorAll('.detail-btn').forEach(el => {
                el.addEventListener('click', () => {
                    // เติมข้อความลง Modal
                    contentEl.textContent = el.getAttribute('data-details');
                    // แสดง Modal
                    modal.classList.remove('hidden');
                });
            });
        });

        // ฟังก์ชันเปิด Modal reject และกำหนดค่า jobId ที่ต้องการ cancel
        function cancelTeamModal(jobId) {
            console.log("Reject job id:", jobId);
            document.getElementById('Cancelteam').classList.remove('hidden'); // แสดง modal
            document.getElementById('rejectJobId').value = jobId; // กำหนดค่า jobId ลงใน input ซ่อน
        }
        // เมื่อกดปุ่มปิด modal ให้ซ่อน modal ออกไป
        document.getElementById('closeCancelTeamModal').onclick = function() {
            document.getElementById('Cancelteam').classList.add('hidden'); // ซ่อน modal
        };
    </script>

</body>

</html>