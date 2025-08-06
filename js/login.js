    // Array of captions
    const captions = [
        '"A job isn’t a job it’s who you are."',
        '"การทำงานไม่ใช่แค่การทำๆไป แต่มันคือสิ่งที่คุณเป็น."',
        '"Being busy and being productive are two different things."',
        '"การทำงานยุ่ง กับการทำงานให้เกิดคุณค่า คือสองสิ่งที่แตกต่างกัน."',
    ];

    let currentIndex = 0;
    const captionElement = document.getElementById('caption');

    // Function to update the caption
    function updateCaption() {
        // Fade out
        captionElement.classList.add('opacity-0');
        setTimeout(() => {
            // Change text
            currentIndex = (currentIndex + 1) % captions.length;
            captionElement.textContent = captions[currentIndex];
            // Fade in
            captionElement.classList.remove('opacity-0');
        }, 500); // Match the duration of the fade-out
    }

    // Change caption every 3 seconds
    setInterval(updateCaption, 3000);