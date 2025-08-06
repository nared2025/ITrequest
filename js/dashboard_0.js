     // Array of captions //ข้อความที่แสดงหน้าเว็บไซต์
          const captions = [
            '"If you encounter any problems, please contact IT."',
            '"หากคุณพบปัญหาใด ๆ กรุณาติดต่อ IT"'
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
    
         // Toggle Chat Visibility
         function toggleChat() {
            const chatContainer = document.getElementById("chat-container");
            if (chatContainer.classList.contains("hidden")) {
                chatContainer.classList.remove("hidden");
                setTimeout(() => {
                    chatContainer.classList.add("scale-100", "opacity-100");
                    chatContainer.classList.remove("scale-95", "opacity-0");
                }, 10); // Delay to trigger transition
            } else {
                chatContainer.classList.add("scale-95", "opacity-0");
                chatContainer.classList.remove("scale-100", "opacity-100");
                setTimeout(() => {
                    chatContainer.classList.add("hidden");
                }, 500); // Match the duration of the transition
            }
        }

        // Send Selected Question แชทบอท
        function sendSelectedQuestion() {
            const selectElement = document.getElementById("question-select");
            const selectedQuestion = selectElement.value;

            if (!selectedQuestion) {
                alert("โปรดเลือกคำถามก่อนส่งครับ");
                return;
            }

            const chatBox = document.getElementById("chat-box");

            // Add user message to chat
            chatBox.innerHTML += `
                <div class="flex justify-end items-center space-x-2">
                    <div class="bg-green-100 text-green-800 px-4 py-2 rounded-lg shadow-md max-w-xs">${selectedQuestion}</div>
                    <img src="../image/user.jpg" alt="User" class="w-8 h-8 rounded-full">
                </div>
            `;
            
            

            // Send message to chatbot.php
            fetch('chatbot.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'message=' + encodeURIComponent(selectedQuestion)
            })
            .then(res => res.json())
            .then(reply => {
                // Add bot reply to chat
                chatBox.innerHTML += `
                    <div class="flex justify-start items-center space-x-2">
                        <img src="../image/Agent.jpg" alt="Agent" class="w-8 h-8 rounded-full">
                        <div class="bg-gray-100 text-gray-800 px-4 py-2 rounded-lg shadow-md max-w-xs">${reply.message}</div>
                    </div>
                `;
                chatBox.scrollTop = chatBox.scrollHeight; // Scroll to bottom
            })
            .catch(error => {
                chatBox.innerHTML += `
                    <div class="flex justify-start items-center space-x-2">
                        <img src="bot-profile.jpg" alt="Agent" class="w-8 h-8 rounded-full">
                        <div class="bg-red-100 text-red-800 px-4 py-2 rounded-lg shadow-md max-w-xs">Sorry, something went wrong.</div>
                    </div>
                `;
            });
        }

        // Show Chat on Icon Click
        document.getElementById("chat-icon").addEventListener("click", toggleChat);

          // JavaScript for toggling theme with Local Storage
          const themeToggle = document.getElementById('theme-toggle');

          // Load saved theme from Local Storage ธีมที่บันทึกไว้
          if (localStorage.getItem('theme') === 'dark') {
              document.body.classList.add('dark');
              themeToggle.checked = true;
          }
  
          // Toggle theme and save to Local Storage
          themeToggle.addEventListener('change', () => {
              if (themeToggle.checked) {
                  document.body.classList.add('dark');
                  localStorage.setItem('theme', 'dark');
              } else {
                  document.body.classList.remove('dark');
                  localStorage.setItem('theme', 'light');
              }
          });
          
//   (function(){
//     var dpr = window.devicePixelRatio || 1;
//     // ถ้า DPR > 1 จะย่อหน้าเว็บให้เล็กลงตามอัตรา เพื่อ physical size ใกล้เคียง
//     if (dpr > 1) {
//       document.body.style.zoom = (1 / dpr);
//       // หรือถ้าไม่อยากใช้ zoom:
//       // document.body.style.transformOrigin = 'top left';
//       // document.body.style.transform = 'scale(' + (1 / dpr) + ')';
//       // document.body.style.width = (100 * dpr) + 'vw';
//     }
//   })();
