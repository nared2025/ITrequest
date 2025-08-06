<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบบประเมินความพึงพอใจ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/customer.css">
</head>
<body class="bg-green-50 font-sans min-h-screen flex items-center justify-center p-6">

<div class="feedback-container bg-white max-w-lg w-full p-6 rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold text-gray-800 mb-4 text-center">แบบประเมินความพึงพอใจ</h2>
    <p class="text-gray-600 text-center mb-6">โปรดให้คะแนนความพึงพอใจในการใช้งานเว็บไซต์ของเรา</p>

    <form id="feedbackForm" class="space-y-4 text-center">
        <!-- Star Rating -->
        <div class="star-rating inline-flex">
            <input id="star5" type="radio" name="rating" value="5">
            <label for="star5">★</label>
            <input id="star4" type="radio" name="rating" value="4">
            <label for="star4">★</label>
            <input id="star3" type="radio" name="rating" value="3">
            <label for="star3">★</label>
            <input id="star2" type="radio" name="rating" value="2">
            <label for="star2">★</label>
            <input id="star1" type="radio" name="rating" value="1">
            <label for="star1">★</label>
        </div>

        <!-- Comments -->
        <textarea id="comments" name="comments" placeholder="เขียนความคิดเห็นที่นี่..." 
            class="w-full h-24 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400"></textarea>

        <!-- Submit Button -->
        <button type="submit" 
            class="w-full bg-green-500 text-white py-2 rounded-lg font-semibold hover:bg-green-600 transition-all">
            ส่งแบบประเมิน
        </button>

        <!-- Thank You Message -->
        <div id="thankYouMessage" class="hidden text-green-600 text-center font-medium mt-4">
            ขอบคุณสำหรับความคิดเห็นของคุณ!
        </div>
    </form>
</div>
<script src="../js/customer.js"></script>
</body>
</html>
