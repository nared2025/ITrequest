//แบบฟอร์มความคิดเห็น
document.getElementById('feedbackForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const rating = document.querySelector('input[name="rating"]:checked');
    const comments = document.getElementById('comments').value;

    if (!rating) {
        alert('กรุณาเลือกคะแนนความพึงพอใจ!');
        return;
    }

    // แสดงข้อความขอบคุณ
    const thankYouMessage = document.getElementById('thankYouMessage');
    thankYouMessage.classList.remove('hidden');

    // ล้างฟอร์มหลังส่งข้อมูล
    document.getElementById('feedbackForm').reset();

    // ซ่อนข้อความขอบคุณและเปลี่ยนหน้าไปยัง firstpage.php หลังจาก 3 วินาที
    setTimeout(() => {
        thankYouMessage.classList.add('hidden');
        window.location.href = 'dashboard.php'; // เปลี่ยนเส้นทางไปยัง firstpage.php
    }, 500);
});