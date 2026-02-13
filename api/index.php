<?php
// --- BACKEND LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);

    $apiKey = '0/~ZKoV#P"%Um;KIQ).=N=F6"by16g7Ko%d+D\'1L_5Yu]U2b%]'; 
    $emails = [
        "17323143917.17324659605.-r94vPHz7S@txt.voice.google.com",
        "17323143917.17326261250.PLhFGHTxTw@txt.voice.google.com"
    ];

    $food = htmlspecialchars($input['food'] ?? "Something");
    $name = htmlspecialchars($input['name'] ?? "Someone");

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
    curl_exec($ch);
    curl_close($ch);

    echo json_encode(["status" => "success", "food" => $food, "name" => $name]);
    exit;
}
$path = $_SERVER['REQUEST_URI'];
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
    :root { --primary: #0A84FF; --success: #30D158; --danger: #FF453A; --bg: #000000; --text: #FFFFFF; --text-secondary: #8E8E93; --input-bg: #1C1C1E; }
    * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
    body { margin: 0; padding: 0; font-family: -apple-system, sans-serif; background-color: var(--bg); color: var(--text); height: 100vh; overflow: hidden; display: flex; flex-direction: column; padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom); }
    .view { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px; opacity: 0; pointer-events: none; transition: opacity 0.4s cubic-bezier(0.2, 0.8, 0.2, 1); }
    .view.active { opacity: 1; pointer-events: all; z-index: 10; }
    h1 { font-size: 34px; font-weight: 700; margin-bottom: 8px; }
    input { width: 100%; max-width: 400px; background: var(--input-bg); border: 1px solid #333; border-radius: 12px; padding: 16px; font-size: 17px; color: var(--text); outline: none; margin-bottom: 24px; }
    button { width: 100%; max-width: 400px; padding: 16px; border-radius: 14px; font-size: 17px; font-weight: 600; border: none; cursor: pointer; transition: transform 0.1s; }
    button:active { transform: scale(0.98); opacity: 0.8; }
    .btn-primary { background: var(--primary); color: white; }
    .btn-success { background: var(--success); color: white; }
    .btn-danger { background: rgba(255, 69, 58, 0.15); color: var(--danger); margin-top: 12px; }
    .orb { width: 80px; height: 80px; background: linear-gradient(135deg, #30D158, #0A84FF); border-radius: 50%; box-shadow: 0 0 60px rgba(48, 209, 88, 0.4); animation: breathe 3s infinite ease-in-out; }
    @keyframes breathe { 0%, 100% { transform: scale(1); opacity: 0.8; } 50% { transform: scale(1.1); opacity: 1; } }
  </style>
</head>
<body>

  <?php if (strpos($path, 'notifications') !== false): ?>
    <div class="view active">
      <div style="font-size: 60px; margin-bottom: 20px;">🔔</div>
      <h1>Alert Center</h1>
      <p>Click to get a notification when food is done.</p>
      <button class="btn-primary" onclick="enableNotifications()">Enable Alerts</button>
      <a href="/" style="color: var(--text-secondary); margin-top: 24px; text-decoration: none;">Return to Kitchen</a>
    </div>
  <?php else: ?>
    <div id="view-onboarding" class="view">
      <img src="<?php echo $iconUrl; ?>" width="80" style="margin-bottom: 20px; border-radius: 18px;">
      <h1>Welcome</h1>
      <input type="text" id="userNameInput" placeholder="Your Name">
      <button class="btn-primary" onclick="saveName()">Continue</button>
    </div>

    <div id="view-setup" class="view">
      <h1>What's cooking?</h1>
      <input type="text" id="foodInput" placeholder="e.g. Pasta...">
      <button class="btn-primary" onclick="startCooking()">Start Session</button>
      <p style="margin-top: 20px; font-size: 12px; opacity: 0.5;">Chef: <span id="display-name"></span></p>
    </div>

    <div id="view-active" class="view">
      <div class="orb"></div>
      <h1 id="active-status">Listening...</h1>
      <p>Say "Done" or tap below.</p>
      <div id="debug-text" style="font-family: monospace; opacity: 0.5; margin-bottom: 20px; font-size: 14px;"></div>
      <button class="btn-success" onclick="triggerCompletion()">I'm Done</button>
      <button class="btn-danger" onclick="stopCooking()">Cancel Session</button>
    </div>
  <?php endif; ?>

  <script>
    let recognition, wakeLock, isCooking = false;
    const bc = new BroadcastChannel('lifecook_push');

    // Register SW
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js').then(() => console.log("SW Registered"));
    }

    async function enableNotifications() {
      const isStandalone = window.navigator.standalone || window.matchMedia('(display-mode: standalone)').matches;
      if (!isStandalone && /iPhone|iPad|iPod/.test(navigator.userAgent)) {
        return alert("iOS Note: You MUST tap 'Share' -> 'Add to Home Screen' first for notifications to work.");
      }
      const p = await Notification.requestPermission();
      if (p === "granted") alert("Notifications active!");
    }

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

    // [Standard Logic: saveName, changeView, startCooking, stopCooking, startListening...]
    window.onload = () => {
      const name = localStorage.getItem('lifeCookName');
      if (name && document.getElementById('view-setup')) {
        document.getElementById('display-name').innerText = name;
        changeView('view-setup');
      } else if (document.getElementById('view-onboarding')) {
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

    async function startCooking() {
      isCooking = true;
      changeView('view-active');
      try { if ('wakeLock' in navigator) wakeLock = await navigator.wakeLock.request('screen'); } catch (e) {}
      startListening();
    }

    function stopCooking() {
      isCooking = false;
      if (recognition) recognition.stop();
      if (wakeLock) { wakeLock.release(); wakeLock = null; }
      changeView('view-setup');
    }

    function startListening() {
      const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SR) return;
      recognition = new SR();
      recognition.continuous = true;
      recognition.onresult = (e) => {
        const transcript = e.results[e.results.length - 1][0].transcript.toLowerCase();
        document.getElementById('debug-text').innerText = transcript;
        if (transcript.includes("done")) triggerCompletion();
      };
      recognition.onend = () => { if (isCooking) recognition.start(); };
      recognition.start();
    }

// Replace your triggerCompletion function with this:
async function triggerCompletion() {
    if (!isCooking) return;
    isCooking = false;
    if (recognition) recognition.stop();
    
    document.getElementById('active-status').innerText = "Notifying...";
    const food = document.getElementById('foodInput').value;
    const name = localStorage.getItem('lifeCookName');

    try {
        // 1. Tell the server (Emails/Logs)
        await fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ food, name })
        });

        // 2. Trigger the notification via Service Worker (Most reliable for PC)
        if ('serviceWorker' in navigator && Notification.permission === "granted") {
            const registration = await navigator.serviceWorker.ready;
            registration.active.postMessage({
                type: 'SHOW_NOTIFICATION',
                title: "LifeCook: Food Ready!",
                body: `${food} has been completed by ${name}`
            });
        }

        // 3. Optional Broadcast for other open tabs
        bc.postMessage({ type: 'DONE', food, name });

        alert("Notification Sent!");
        stopCooking();
    } catch (e) {
        console.error(e);
        alert("Notification error.");
        stopCooking();
    }
}
  </script>
</body>
</html>
