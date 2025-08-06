<?php
session_start();
// ตรวจสอบให้มี job_id ใน URL ถ้าไม่มีให้กลับไปยังแบบฟอร์ม
if (empty($_GET['job_id'])) {
    header('Location: ../documents/tranform.php');
    exit();
}
$jobId = htmlspecialchars($_GET['job_id']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ส่งคำร้องสำเร็จ</title>
    <!-- ปรับพาธ CSS ตามโครงสร้างโปรเจค -->
     <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/tranform.css">
</head>

<body>
 <!-- Overlay Background -->
<div id="success-modal" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm flex items-center justify-center z-50">
  <!-- Modal Container -->
  <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
    <!-- Close Button -->
    <!-- <button
      type="button"
      class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors"
      aria-label="Close"
      onclick="document.getElementById('success-modal').classList.add('hidden')"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button> -->

    <!-- Content -->
    <div class="flex flex-col items-center p-6 space-y-4">
      <!-- Animated Check Icon -->
      <div class="flex items-center justify-center w-20 h-20 bg-green-100 rounded-full animate-pulse">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <!-- Title -->
      <h2 class="text-2xl font-semibold text-gray-800">ส่งคำร้องสำเร็จ!</h2>
      <!-- Job ID Message -->
      <p class="text-center text-gray-600">
        เลขคำร้องของคุณคือ:
        <span class="font-semibold text-green-600"><?= htmlspecialchars($jobId); ?></span>
      </p>
      <!-- Instruction Message -->
      <p class="text-center text-gray-500 text-sm">
        โปรดเก็บหมายเลขนี้ไว้ เพื่อติดตามสถานะในภายหลัง
      </p>
    </div>

    <!-- Divider -->
    <div class="border-t border-gray-200"></div>

    <!-- Action Buttons -->
    <div class="p-6 flex flex-col sm:flex-row gap-4">
      <a
        href="../documents/tranform.php"
        class="flex-1 py-2 px-4 border-2 border-green-500 text-green-500 font-medium rounded-lg text-center
               bg-white hover:bg-green-50 transition-colors duration-300 ease-in-out shadow-sm hover:shadow-md"
      >
        กลับไปยังแบบฟอร์ม
      </a>
      <a
        href="dashboard.php"
        class="flex-1 py-2 px-4 bg-gradient-to-r from-green-500 to-green-600 text-white font-medium rounded-lg text-center
               hover:from-green-600 hover:to-green-700 transition-colors duration-300 ease-in-out shadow-sm hover:shadow-md"
      >
        ไปที่ Dashboard
      </a>
    </div>
  </div>
</div>



</body>

</html>