<?php
session_start();
// ตรวจสอบให้ผู้ใช้ล็อกอินและมีสิทธิ์ Manager/IT
if (!isset($_SESSION['emp_id']) || !isset($_SESSION['role'])) {
    header('Location: ../login/login.php');
    exit;
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

// เชื่อมต่อฐานข้อมูล
$mysqli = new mysqli('localhost', 'amt', 'P@ssw0rd!amt', 'dbhelp', 3306);
if ($mysqli->connect_error) {
    die('DB Connection Error: ' . $mysqli->connect_error);
}

//ดึงข้อมูลจากตาราง db_job ที่มีสถานะ 'pending'และ mgrapprove ให้ตรงกับ emp_id ของผู้ใช้
if (!isset($_SESSION['emp_id'])) {
    exit("Unauthorized");
}

$limit = 9; // จำนวนรายการต่อหน้า
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// ดึงเฉพาะงาน assigned ให้พนักงาน และ pending
$empIdd = isset($_SESSION['emp_id']) ? $_SESSION['emp_id'] : null;
$stmt = $mysqli->prepare("
    SELECT j.job_id, j.job_details, j.job_status,j.emp_id,e.emp_name,j.problem_id,p.problem_name AS problem_category,a.assigned_date
    FROM db_job AS j
    JOIN db_problem AS p ON p.problem_id = j.problem_id
    JOIN employee AS e ON e.emp_id = j.emp_id
    JOIN db_job_assignments AS a ON a.job_id = j.job_id
    WHERE j.emp_id = ? AND j.job_status = 'pending'
    ORDER BY job_id DESC
    LIMIT ? OFFSET ?;


");
if (!$stmt) {
    die('Prepare failed: ' . $mysqli->error);
}

// $job_details='job_details';
$stmt->bind_param('sii', $empIdd, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$jobs = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();


// นับจำนวนรายการทั้งหมด
$countStmt = $mysqli->prepare("
    SELECT COUNT(*) AS total
    FROM db_job_assignments AS a
    JOIN db_job AS j ON a.job_id = j.job_id
    WHERE j.job_status = 'pending'
      AND a.assigned_emp = ?
");
$countStmt->bind_param('s', $empIdd);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRows = $countResult->fetch_assoc()['total'];
$countStmt->close();

$mysqli->close();

$totalPages = ceil($totalRows / $limit);

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
            <nav class="w-full sm:w-auto">
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
                                JOB ASSIGN
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
                            QUEUE
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
    <div class="form-container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 shadow-md bg-white rounded-lg">
        <div class="form-header">
            <div>
                <div class="bg-gradient-to-r from-green-100 via-blue-100 to-purple-100 rounded-xl shadow-lg p-8 mb-6 border border-gray-200">
                    <div class="flex flex-col items-center justify-center space-y-3">
                        <div class="relative">
                        </div>
                        <h1 class="text-3xl font-extrabold text-sky-600 tracking-tight drop-shadow">ACCEPT REQUEST</h1>
                        <p class="text-gray-500 text-base text-center max-w-md">
                            ตรวจสอบความถูกต้องของงาน และคลิกปุ่ม <span style="color:red;">‘Accept’</span> เพื่อยืนยันการปิดคำขอ
                            หากมีข้อสงสัยหรือปัญหาเพิ่มเติม กรุณาติดต่อทีม IT ได้ทันที


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
       <div class="overflow-x-auto">
  <table class="min-w-full text-sm text-center table-auto">
            <thead>
                <tr>
                    <th>job NO.</th>
                    <!-- <th>Emp ID</th> -->
                    <th>requester</th>
                    <th>job category</th>
                    <th>DETAILs</th>
                    <th>ASSIGN_DATE</th>
                    <th>ACTION</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($jobs)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded shadow text-center">
                                ไม่มีงานดำเนินการ
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <tr class="hover:bg-green-50 transition">
                            <td>
                                <div class="detail-btn bg-gray-100 rounded-lg shadow p-2 text-gray-800 font-semibold text-center cursor-pointer"
                                    data-details="<?= htmlspecialchars($job['job_details'], ENT_QUOTES) ?>"><?= htmlspecialchars($job['job_id']) ?>
                                </div>
                            </td>
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
                            <td>
                                <?php
                                $original    = $job['job_details'];
                                $escapedFull = htmlspecialchars($original, ENT_QUOTES);

                                if (mb_strlen($original, 'UTF-8') > 10) {
                                    // ตัด 10 ตัวอักษร แล้วต่อ "...”
                                    $shortText    = mb_substr($original, 0, 10, 'UTF-8');
                                    $escapedShort = htmlspecialchars($shortText, ENT_QUOTES) . '...<span style="color:red;">(เพิ่มเติม)</span>';

                                    // ห่อด้วย span ที่มี data-detail สำหรับคลิกดู popup
                                    echo '<div class="bg-gray-100 rounded-lg shadow p-2 text-gray-700 text-center">';
                                    echo '<span '
                                        .  'class="cursor-pointer view-detail" '
                                        .  'data-detail="' . $escapedFull . '">'
                                        .  $escapedShort
                                        .  '</span>';
                                    echo '</div>';
                                } else {
                                    // ถ้าไม่เกิน 10 ตัวอักษร ให้โชว์เต็มเลย
                                    echo '<div class="bg-red-100 rounded-lg shadow p-2 text-red-700 text-center">'
                                        .  $escapedFull
                                        .  '</div>';
                                }
                                ?>
                            </td>

                            <td>
                                <div class="bg-gray-100 rounded-lg shadow p-2 text-gray-700 text-center">
                                    <?= htmlspecialchars($job['assigned_date']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-row gap-2 items-center justify-center">
                                    <a class="Action bg-green-100 hover:bg-green-600 hover:text-white text-green-700 px-4 py-1 rounded shadow transition"
                                        href="updateaccepted.php?req_no=<?= $job['job_id'] ?>&action=approve">
                                        ACCEPT
                                    </a>
                                    <!--<a class="Reject bg-red-100 hover:bg-red-600 hover:text-white text-red-700 px-4 py-1 rounded shadow transition"
                                         href="#" onclick="cancelTeamModal('<?= $job['job_id'] ?>')">
                                            Reject
                                        </a>
                                    </div> -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>

        </table>
                            </div>
        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center gap-2 mt-4">
                <?php
                $maxPagesToShow = 5;
                $startPage = max(1, $page - floor($maxPagesToShow / 2));
                $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
                $startPage = max(1, $endPage - $maxPagesToShow + 1);
                ?>

                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 border rounded bg-white text-gray-700 hover:bg-green-100">← ก่อนหน้า</a>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="?page=<?= $i ?>" class="px-3 py-1 border rounded <?= $i == $page ? 'bg-green-500 text-white' : 'bg-white text-gray-700 hover:bg-green-100' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 border rounded bg-white text-gray-700 hover:bg-green-100">ถัดไป →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Overlay -->
    <div
        id="detailModal"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm hidden z-50">
        <!-- container เป็น inline-block เพื่อขยายตามขนาด textarea -->
        <div class="bg-white rounded-2xl shadow-xl mx-4 p-6 inline-block max-h-[90vh] overflow-auto">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">รายละเอียดงาน</h2>

            <!-- textarea อ่านอย่างเดียว (resize ได้) -->
            <textarea
                id="modalContent"
                readonly
                class="min-w-[300px] h-40 p-2 border border-gray-300 rounded resize"></textarea>

            <!-- ปุ่มปิด Modal -->
            <div class="mt-6 text-right">
                <button
                    id="closeModal"
                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg shadow transition">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('detailModal');
            const contentEl = document.getElementById('modalContent');
            const closeBtn = document.getElementById('closeModal');

            // เมื่อกดปุ่ม Close ให้ซ่อน Modal และเคลียร์ textarea
            closeBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
                contentEl.value = '';
            });

            // ผูกคลิกให้ทุก .view-detail เปิด Modal
            document.querySelectorAll('.view-detail').forEach(el => {
                el.addEventListener('click', () => {
                    // เอาค่าจาก data-detail ใส่ใน textarea
                    const fullText = el.getAttribute('data-detail') || '';
                    contentEl.value = fullText;

                    // แสดง Modal
                    modal.classList.remove('hidden');
                    // โฟกัส textarea เผื่อผู้ใช้อยาก scroll หรือ resize
                    contentEl.focus();
                });
            });

            // ถ้าคลิกนอกกล่อง (บน overlay) ให้ปิด Modal ด้วย (เลือกถ้าต้องการ)
            modal.addEventListener('click', e => {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                    contentEl.value = '';
                }
            });
        });
    </script>

</body>

</html>