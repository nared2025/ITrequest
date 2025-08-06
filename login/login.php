<?php
session_start();
// สร้าง CSRF token ถ้ายังไม่มี
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// รับ error จาก process_login.php
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Login</title>
</head>
<body class="bg-green-50 flex items-center justify-center min-h-screen px-4">
  <div class="bg-white shadow-lg rounded-lg overflow-hidden flex flex-col md:flex-row max-w-5xl w-full">
    <!-- Left Section: แสดงเมื่อหน้าจอ ≥ md -->
    <div class="hidden md:flex md:w-1/2 flex-col justify-center items-center bg-green-100 p-8">
      <img
        src="../image/login.png"
        alt="Illustration"
        class="w-full h-auto mb-6 object-contain"
      >
      <h1 class="text-2xl font-bold text-gray-800 mb-2 text-center">IT REQUEST ONLINE</h1>
      <p id="caption" class="text-gray-600 text-center transition-opacity duration-500">
        "A job isn’t a job—it’s who you are."
      </p>
    </div>

    <!-- Right Section: ฟอร์ม login -->
    <div class="w-full md:w-1/2 p-8 flex flex-col justify-center items-center">
      <div class="w-full max-w-sm">
        <!-- Title -->
        <h2 class="text-3xl font-bold text-gray-800 text-center mb-6">Login</h2>

        <!-- Error Message -->
        <?php if (!empty($error)): ?>
          <div class="text-red-600 mb-4 text-center">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="login_process.php" method="POST" class="space-y-4">
          <!-- Emp ID -->
          <div>
            <label for="emp_id" class="block text-sm font-medium text-gray-600">Username (Emp ID)</label>
            <input
              id="emp_id"
              name="emp_id"
              type="text"
              required
              autocomplete="username"
              placeholder="A0000"
              class="w-full px-4 py-2 mt-1 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400"
            >
          </div>

          <!-- Password -->
          <div>
            <label for="emp_password" class="block text-sm font-medium text-gray-600">Password</label>
            <input
              id="emp_password"
              name="emp_password"
              type="password"
              required
              autocomplete="current-password"
              placeholder="********"
              class="w-full px-4 py-2 mt-1 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400"
            >
          </div>

          <!-- CSRF Token -->
          <input
            type="hidden"
            name="csrf_token"
            value="<?= $_SESSION['csrf_token'] ?>"
          >

          <!-- Submit Button -->
          <button
            type="submit"
            class="w-full py-2 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition duration-300"
          >
            Let’s go
          </button>
        </form>
      </div>
    </div>
  </div>

  <script src="../js/login.js"></script>
</body>
</html>
