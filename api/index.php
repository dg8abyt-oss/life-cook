<?php
// --- BACKEND LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);

    // Hardcoded API Key & Configuration
    $apiKey = '0/~ZKoV#P"%Um;KIQ).=N=F6"by16g7Ko%d+D\'1L_5Yu]U2b%]'; 
    $emails = [
        "17323143917.17324659605.-r94vPHz7S@txt.voice.google.com",
        "17323143917.17326261250.PLhFGHTxTw@txt.voice.google.com"
    ];

    $food = htmlspecialchars($input['food'] ?? "Something");
    $name = htmlspecialchars($input['name'] ?? "Someone");

    // 1. Send via Locq-Personal (Emails/Texts)
    $payload = [
        "key" => $apiKey,
        "to" => $emails,
        "subject" => " ", 
        "body" => "Food:$food\nhas been completed by $name"
    ];

    $ch = curl_init('https://locq.personal.dhruvs.host/api/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    // Return success to frontend so it can trigger local Browser Notifs
    echo json_encode(["status" => "success", "food" => $food, "name" => $name]);
    exit;
}

$iconUrl = "https://ik.imagekit.io/migbb/image.jpeg?updatedAt=1770995065553";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>LifeCook</title>
  
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="theme-color" content="#000000">

  <link rel="manifest" href="/manifest.json">
  <link rel="icon" type="image/jpeg" href="<?php echo $iconUrl; ?>">
  <link rel="apple-touch-icon" href="<?php echo $iconUrl; ?>">

  <style>
    :root {
      --primary: #0A84FF;
      --success: #30D158;
      --danger: #FF453A;
      --bg: #000000;
      --card: #1C1C1E;
      --text: #FFFFFF;
      --text-secondary: #8E8E93;
      --input-bg: #2C2C2E;
    }

    * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

    body {
      margin: 0; padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", sans-serif;
      background-color: var(--bg);
      color: var(--text);
      height: 100vh;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      padding-top: env(safe-area-inset-top);
      padding-bottom: env(safe-area-inset-bottom);
    }

    .view {
      position: absolute; top: 0; left: 0; width: 100%; height: 100%;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      padding: 24px; opacity: 0; pointer-events: none;
      transform: scale(0.95);
      transition: opacity 0.4s cubic-bezier(0.2, 0.8, 0.2, 1), transform 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
    }

    .view.active { opacity: 1; pointer-events: all; transform: scale(1); z-index: 10; }

    h1 { font-size: 34px; font-weight: 700; text-align: center; margin-bottom: 8px; }
    p { font-size: 17px; color: var(--text-secondary); text-align: center; margin-bottom: 32px; }

    input { width: 100%; max-width: 400px; background: var(--input-bg); border: none; border-radius: 12px; padding: 16px; font-size: 17px; color: var(--text); outline: none; margin-bottom: 24px; }
    button { width: 100%; max-width: 400px; padding: 16px; border-radius: 14px; font-size: 17px; font-weight: 600; border: none; cursor: pointer; transition: transform 0.1s; }
    button:active { transform: scale(0.98); opacity: 0.8; }

    .btn-primary { background: var(--primary); color: white; }
    .btn-success { background: var(--success); color: white; margin-bottom: 12px; }
    .btn-danger { background: rgba(255, 69, 58, 0.15); color: var(--danger); }

    .orb-container { position: relative; width: 180px; height: 180px; display: flex; justify-content: center; align-items: center; margin-bottom: 30px; }
    .orb { width: 80px; height: 80px; background: linear-gradient(135deg, #30D158, #0A84FF); border-radius: 50%; box-shadow: 0 0 60px rgba(48, 209, 88, 0.4); animation: breathe 3s infinite ease-in-out; }
    
    @keyframes breathe { 0%, 100% { transform: scale(1); opacity: 0.8; } 50% { transform: scale(1.1); opacity: 1; } }

    .status-badge { background: rgba(255, 255, 255, 0.1); padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 16px; }
    .mic-feedback { height: 20px; font-size: 14px; color: var(--text-secondary); margin-bottom: 20px; font-family: monospace; }
    
    .receiver-card { margin-top: 30px; padding: 20px; background: #1C1C1E; border-radius: 15px; width: 100%; max-width: 400px; text-align: center; }
  </style>
</head>
<body>

  <div id="view-onboarding" class="view">
    <img src="<?php echo $iconUrl; ?>" width="80" style="margin-bottom: 20px; border-radius: 18px;">
    <h1>Welcome</h1>
    <p>Set up your profile for LifeCook.</p>
    <input type="text" id="userNameInput" placeholder="Your Name" autocomplete="off">
    <button class="btn-primary" onclick="saveName()">Continue</button>
  </div>

  <div id="view-setup" class="view">
    <h1>What's cooking?</h1>
    <input type="text" id="foodInput" placeholder="e.g. Pasta..." autocomplete="off">
    <button class="btn-primary" onclick="startCooking()">Start Session</button>
    
    <div class="receiver-card">
        <p style="margin-bottom: 12px; font-size: 14px; color: var(--text-secondary);">Notification Receiver</p>
        <button id="notif-btn" class="btn-danger" style="background: rgba(255,255,255,0.05); color: white; padding: 12px; font-size: 14px;" onclick="enableNotifications()">
            Enable Alerts on this Device
        </button>
    </div>

    <p style="margin-top: 20px; font-size: 12px; opacity: 0.5;">Chef: <span id="display-name"></span></p>
  </div>

  <div id="view-active" class="view">
    <div class="status-badge">Locked • Listening</div>
    <div class="orb-container"><div class="orb"></div></div>
    <h1>Listening...</h1>
    <p>Say <b>"Done"</b> or tap below.</p>
    <div class="mic-feedback" id="debug-text"></div>
    <div style="flex-grow: 1;"></div> 
    <button class="btn-success" onclick="triggerCompletion()">I'm Done</button>
    <button class="btn-danger" onclick="stopCooking()">Cancel Session</button>
  </div>

  <script>
    let recognition;
    let wakeLock = null;
    let isCooking = false;
    const bc = new BroadcastChannel('lifecook_push');

    // Register Service Worker for System-Level Notifs
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js').then(() => console.log("SW Active"));
    }

    window.onload = () => {
      const savedName = localStorage.getItem('lifeCookName');
      
      // Update UI if notification permission already granted
      if (Notification.permission === "granted") {
          const btn = document.getElementById('notif-btn');
          if (btn) {
              btn.innerText = "Alerts Active ✅";
              btn.style.color = "#30D158";
          }
      }

      if (savedName) {
        document.getElementById('display-name').innerText = savedName;
        changeView('view-setup');
      } else {
        changeView('view-onboarding');
      }
    };

    function changeView(id) {
      document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
      const el = document.getElementById(id);
      if (el) el.classList.add('active');
    }

    function saveName() {
      const n = document.getElementById('userNameInput').value.trim();
      if (!n) return;
      localStorage.setItem('lifeCookName', n);
      document.getElementById('display-name').innerText = n;
      changeView('view-setup');
    }

    // --- NOTIFICATION LOGIC ---
    async function enableNotifications() {
      if (!("Notification" in window)) return alert("Not supported.");
      const p = await Notification.requestPermission();
      if (p === "granted") {
          const btn = document.getElementById('notif-btn');
          btn.innerText = "Alerts Active ✅";
          btn.style.color = "#30D158";
          alert("Success! This device will now receive notifications.");
      }
    }

    // Listen for completion messages from other tabs
    bc.onmessage = (e) => {
      if (e.data.type === 'DONE' && Notification.permission === "granted") {
        navigator.serviceWorker.ready.then(reg => {
          reg.showNotification("LifeCook: Food Ready!", {
            body: `${e.data.food} completed by ${e.data.name}`,
            icon: "<?php echo $iconUrl; ?>"
          });
        });
      }
    };

    // --- WAKE LOCK ---
    async function requestWakeLock() {
      try {
        if ('wakeLock' in navigator) {
          wakeLock = await navigator.wakeLock.request('screen');
        }
      } catch (err) { console.error(err); }
    }

    async function startCooking() {
      const f = document.getElementById('foodInput').value.trim();
      if (!f) return alert("What are you cooking?");
      localStorage.setItem('currentFood', f);
      isCooking = true;
      changeView('view-active');
      await requestWakeLock();
      startListening();
    }

    function stopCooking() {
      isCooking = false;
      if (recognition) recognition.stop();
      if (wakeLock) { wakeLock.release().then(() => { wakeLock = null; }); }
      changeView('view-setup');
    }

    // Re-lock screen if user leaves and comes back
    document.addEventListener('visibilitychange', async () => {
      if (wakeLock !== null && document.visibilityState === 'visible') {
        await requestWakeLock();
      }
    });

    // --- VOICE LOGIC ---
    function startListening() {
      const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SR) return;
      recognition = new SR();
      recognition.continuous = true;
      recognition.interimResults = true;
      recognition.onresult = (e) => {
        const transcript = Array.from(e.results).map(r => r[0].transcript).join('').toLowerCase();
        document.getElementById('debug-text').innerText = "..." + transcript.slice(-20);
        if (transcript.includes("done")) triggerCompletion();
      };
      recognition.onend = () => { if (isCooking) try { recognition.start(); } catch(e) {} };
      recognition.start();
    }

    async function triggerCompletion() {
      if (!isCooking) return;
      isCooking = false;
      if (recognition) recognition.stop();
      document.querySelector('#view-active h1').innerText = "Sending...";
      
      const food = localStorage.getItem('currentFood');
      const name = localStorage.getItem('lifeCookName');

      try {
        // 1. Notify Server (Email/Text)
        await fetch('index.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ food, name })
        });

        // 2. Notify Service Worker (For this device)
        if ('serviceWorker' in navigator && Notification.permission === "granted") {
            const reg = await navigator.serviceWorker.ready;
            reg.active.postMessage({
                type: 'SHOW_NOTIFICATION',
                title: "LifeCook: Food Ready!",
                body: `${food} has been completed by ${name}`
            });
        }

        // 3. Notify Broadcast Channel (For other devices/tabs)
        bc.postMessage({ type: 'DONE', food, name });

        alert("LifeCook: Sent!");
        stopCooking();
      } catch (err) {
        alert("Server Error.");
        stopCooking();
      }
    }
  </script>
</body>
</html>
