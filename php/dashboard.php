<?php
//ถ้ามีปัญหาเรื่อง headers หรือ session มันจะเด้งเตือนขึ้นมา
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['emp_id'])) {
    header('Location: login.php');  // หรือ path ที่ถูกต้องของคุณ
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Request Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body class="bg-gray-50 uppercase transition-colors duration-300 flex flex-col min-h-screen">

    <!-- Navbar -->
   <header class="bg-white shadow-md">
  <div class="container mx-auto px-2 md:px-4 py-2
              flex flex-col sm:flex-row justify-between items-center">
    <!-- Logo / Title -->
    <h1 onclick="window.location.href='dashboard.php'"
        class="text-2xl font-bold text-green-500 hover:text-green-600
               transition-transform transform hover:scale-105">
      IT REQUEST <span class="text-gray-800 hover:text-gray-800">ONLINE</span>
    </h1>

    <!-- Navigation -->
    <nav class="w-full sm:w-auto mt-4 sm:mt-0">
      <ul class="flex flex-wrap justify-center sm:justify-start gap-2 sm:gap-6">
        <li class="flex-1 sm:flex-none">
          <a href="./dashboard.php"
             class="relative text-gray-600 hover:text-green-500
                    transition-all duration-300 group transform hover:scale-105">
            HOME
            <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500
                         transition-all duration-300 group-hover:w-full"></span>
          </a>
        </li>
        <?php if (isset($_SESSION['dep_id']) && $_SESSION['dep_id']==17 
                  && isset($_SESSION['emp_id']) && $_SESSION['emp_id']!=='A1263'): ?>
        <li class="flex-1 sm:flex-none">
          <a href="../IT_Support/Admin.php"
             class="relative text-gray-600 hover:text-green-500
                    transition-all duration-300 group transform hover:scale-105">
            job ASSIGN
            <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500
                         transition-all duration-300 group-hover:w-full"></span>
          </a>
        </li>
        <?php endif; ?>
        <?php if (isset($_SESSION['role']) && $_SESSION['role']>=2 
                  && $_SESSION['emp_id']!=='A1263'): ?>
        <li class="flex-1 sm:flex-none">
          <a href="../manager/Admin.php"
             class="relative text-gray-600 hover:text-green-500
                    transition-all duration-300 group transform hover:scale-105">
            APPROVED
            <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500
                         transition-all duration-300 group-hover:w-full"></span>
          </a>
        </li>
        <?php endif; ?>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] 
                  && isset($_SESSION['emp_id']) && $_SESSION['emp_id']=='A1263'): ?>
        <li class="flex-1 sm:flex-none">
          <a href="../manager_IT/Admin_IT.php"
             class="relative text-gray-600 hover:text-green-500
                    transition-all duration-300 group transform hover:scale-105">
            APPROVED
            <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500
                         transition-all duration-300 group-hover:w-full"></span>
          </a>
        </li>
        <?php endif; ?>
        <?php if (isset($_SESSION['role']) && $_SESSION['role']): ?>
        <li class="flex-1 sm:flex-none">
          <a href="./checkrequest.php"
             class="relative text-gray-600 hover:text-green-500
                    transition-all duration-300 group transform hover:scale-105">
            ACCEPTED
            <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500
                         transition-all duration-300 group-hover:w-full"></span>
          </a>
        </li>
        <?php endif; ?>
          <li class="flex-1 sm:flex-none">
          <a href="./jobstatus.php"
             class="relative text-gray-600 hover:text-green-500
                    transition-all duration-300 group transform hover:scale-105">
            JOB STATUS
            <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500
                         transition-all duration-300 group-hover:w-full"></span>
          </a>
        </li>
        <li class="flex-1 sm:flex-none">
          <a href="./history.php"
             class="relative text-gray-600 hover:text-green-500
                    transition-all duration-300 group transform hover:scale-105">
            JOB HISTORY
            <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500
                         transition-all duration-300 group-hover:w-full"></span>
          </a>
        </li>
        <li class="flex-1 sm:flex-none">
          <a href="./status.php"
             class="relative text-gray-600 hover:text-green-500
                    transition-all duration-300 group transform hover:scale-105">
            QUEUE
            <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500
                         transition-all duration-300 group-hover:w-full"></span>
          </a>
        </li>
        <li class="flex-1 sm:flex-none">
          <a href="../login/logout.php"
             class="relative text-gray-600 hover:text-green-500
                    transition-all duration-300 group transform hover:scale-105">
            LOGOUT
            <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-green-500
                         transition-all duration-300 group-hover:w-full"></span>
          </a>
        </li>
      </ul>
    </nav>

    <!-- Create Button -->
    <a href="../documents/tranform.php"
       class="create mt-3 sm:mt-0 px-4 py-2">
      <span>Create a request →</span>
      <div class="liquid"></div>
    </a>
  </div>
</header>

<main class="flex-grow flex flex-col justify-start">
  <!-- Hero Section -->
<section class="bg-gray-100 py-6 sm:py-10 md:py-2">
  <div class="container mx-auto px-4 sm:px-3 md:px-6 lg:px-8 text-center">
    <h2 class="text-2xl sm:text-3xl md:text-3xl font-bold text-gray-800 mb-4">
      Welcome to <span class="text-green-500"> IT</span>
    </h2>
    <p id="caption" class="text-base sm:text-lg md:text-xl text-gray-600 mb-6 transition-opacity">
      If you encounter any problems, please contact IT.
    </p>
  </div>
</section>

    <!-- Clients Section -->
    <!-- HTML -->
    <section class="hero-section relative w-full h-[20rem] bg-no-repeat bg-cover bg-center">
        <div class="container mx-auto h-full flex flex-col justify-center items-center text-white px-4">
            <h3 class="text-3xl md:text-2xl font-bold mb-2">Step into the Paperless Era – All Forms Online</h3>
            <p class="text-lg md:text-xl">"ก้าวสู่ยุคไร้กระดาษ ทุกแบบฟอร์มอยู่บนเว็บ"</p>
        </div>
        <div class="ghost">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </section>

    <!-- <div class="container mx-auto px-4 md:px-6 text-center">
            <h3 class="text-2xl font-semibold text-gray-800 mb-6">Our Clients</h3>
            <p class="text-gray-600 mb-12">We have been working with some Fortune 500+ clients</p>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-6">
                <img src="https://via.placeholder.com/100" alt="Client Logo" class="mx-auto">
                <img src="https://via.placeholder.com/100" alt="Client Logo" class="mx-auto">
                <img src="https://via.placeholder.com/100" alt="Client Logo" class="mx-auto">
                <img src="https://via.placeholder.com/100" alt="Client Logo" class="mx-auto">
                <img src="https://via.placeholder.com/100" alt="Client Logo" class="mx-auto">
                <img src="https://via.placeholder.com/100" alt="Client Logo" class="mx-auto">
            </div>
        </div>
    </section> -->

    <!-- Features Section -->
    <section class="bg-gray-100 py-2 md:py-4">
        <div class="container mx-auto px-4 md:px-6 pb-6 text-center">
            <h3 class="text-2xl font-semibold text-gray-800 mb-3">select a form</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <a href="../documents/tranform.php" class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-transform transform hover:scale-105 border border-green-500 group">
                    <h4 class="text-lg font-semibold text-green-600 mb-2 group-hover:text-green-700 transition-colors">IT Request Form</h4>
                    <p class="text-gray-600 group-hover:text-green-500 transition-colors">Click here to create an IT request</p>
                </a>
                <a href="#" class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-transform transform hover:scale-105 border border-green-500 group">
                    <h4 class="text-lg font-semibold text-green-600 mb-2 group-hover:text-green-700 transition-colors">Borrowing form</h4>
                    <p class="text-gray-600 group-hover:text-green-500 transition-colors">Coming soon...</p>
                </a>
                <a href="#" class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-transform transform hover:scale-105 border border-green-500 group">
                    <h4 class="text-lg font-semibold text-green-600 mb-2 group-hover:text-green-700 transition-colors">it equipment Transfer form</h4>
                    <p class="text-gray-600 group-hover:text-green-500 transition-colors">Coming soon...</p>
                </a>
            </div>
        </div>
    </section>
</main>
    <!-- Chatbot Icon -->
    <div id="chat-icon" class="fixed bottom-20 right-4 w-16 h-16 bg-green-100 rounded-full shadow-lg flex items-center justify-center cursor-pointer transition-transform transform hover:scale-110">
        <img src="../image/chat.png" alt="Chat Icon" class="w-10 h-10">
    </div>

    <!-- Chatbot Section -->
    <div id="chat-container" class="hidden fixed bottom-28 right-4 w-80 bg-white shadow-lg rounded-lg overflow-hidden border border-gray-300 transition-all duration-500 transform scale-95 opacity-0 origin-bottom-right">
        <div class="bg-green-500 text-white px-4 py-2 flex justify-between items-center">
            <h3 class="font-bold">Chat with AI</h3>
            <button onclick="toggleChat()" class="text-white font-bold">✖</button>
        </div>
        <div id="chat-box" class="p-4 h-64 overflow-y-auto text-sm text-gray-800 space-y-4">
            <div class="text-gray-500 text-center">Select a question below to start...</div>
        </div>
        <div class="p-4 border-t border-gray-200">
            <select id="question-select" class="w-full bg-gray-200 text-green-800 px-3 py-2 rounded-lg text-sm">
                <option value="" disabled selected>Select a question...</option>
                <option value="กรณียืมอุปกรณ์ IT ต้องทำอย่างไรบ้าง?">กรณียืมอุปกรณ์ IT ต้องทำอย่างไรบ้าง?</option>
                <option value="เข้า AMT system ไม่ได้แก้ไขอย่างไรได้บ้าง?">เข้า AMT system ไม่ได้แก้ไขอย่างไรได้บ้าง?</option>
                <option value="คอมพิวเตอร์เปิดไม่ติดแก้ปัญหาอย่างไร?">คอมพิวเตอร์เปิดไม่ติดแก้ปัญหาอย่างไร?</option>
                <option value="มีปัญหาเกี่ยวกับการใช้งานโปรแกรมแก้ไขอย่างไร?">มีปัญหาเกี่ยวกับการใช้งานโปรแกรมแก้ไขอย่างไร?</option>
                <option value="Website learning เข้าไม่ได้?">Website learning เข้าไม่ได้?</option>
            </select>
            <button onclick="sendSelectedQuestion()" class="mt-2 w-full bg-green-500 text-white px-4 py-2 rounded-lg font-bold transition-transform transform hover:scale-105">Send</button>
        </div>
    </div>

    <!-- Footer -->
   <footer class="bg-gray-800 text-white py-6 mt-auto">
        <div class="text-center">
            <p>© 2025 IT Request System.</p>
        </div>
    </footer>

    <!-- Theme Toggle Button -->
    <div class="fixed bottom-4 right-4 flex items-center space-x-2">
        <span class="text-gray-600 dark:text-gray-300">Light</span>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" id="theme-toggle" class="sr-only peer">
            <div class="w-11 h-6 bg-gray-200 rounded-full dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 transition"></div>
            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full border border-gray-300 dark:border-gray-600 peer-checked:translate-x-5 transition-transform"></div>
        </label>
        <span class="text-gray-600 dark:text-gray-300">Dark</span>
    </div>
    
    <script src="../js/dashboard_0.js"></script>
</body>

</html>