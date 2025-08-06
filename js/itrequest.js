//ไฟล์นี้ใช้สำหรับการจัดการการแสดง popup 
const popup = document.getElementById('popup');
const popupClose = document.getElementById('popup-close');
const popupOk = document.getElementById('popup-ok');
const formContainer = document.getElementById('form-container');

// ถ้ากด "รับทราบ" แล้วจะปิด popup และแสดง form
popupOk.addEventListener('click', () => {
    popup.style.display = 'none';
    formContainer.style.display = 'block';
});

// ถ้ากด "X" ปิด popupจะไปหน้า dashboard
popupClose.addEventListener('click', () => {
    window.location.href = '../php/dashboard.php';
});

