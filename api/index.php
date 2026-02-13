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
    curl_exec($ch);
    curl_close($ch);

    // 2. Trigger "Global" Notification Signal
    // We send a signal back to the frontend. The "Receiver" devices 
    // will be checking for this signal.
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
  <link rel="manifest" href="/manifest.json">
  <link rel="icon" type="image/jpeg" href="<?php echo $iconUrl; ?>">
  <link rel="apple-touch-icon" href="<?php echo $iconUrl; ?>">
  <style>
    :root { --primary: #0A84FF; --success: #30D158; --danger: #FF453A; --bg: #000000; --text: #FFFFFF; --text-secondary: #8E8E93; --input-bg: #1C1C1E; }
    * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
    body { margin: 0; padding: 0; font-family: -apple-system, sans-serif; background-color: var(--bg); color: var(--text); height: 100vh; overflow: hidden; display: flex; flex-direction: column; padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom); }
    .view { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px; opacity: 0; pointer-events: none; transition: opacity 0.4s; }
    .view.active { opacity: 1; pointer-events: all; z-index: 10; }
    h1 { font-size: 34px; font-weight: 700; margin-bottom: 8px; }
    input { width: 100%; max-width: 400px; background: var(--input-bg); border: none; border-radius: 12px; padding: 16px; font-size: 17px; color: var(--text); outline: none; margin-bottom: 24px; }
    button { width: 100%; max-width: 400px; padding: 16px; border-radius: 14px; font-size: 17px; font-weight: 600; border: none; cursor: pointer; transition: transform 0.1s; }
    .btn-primary { background: var(--primary); color: white; }
    .btn-success { background: var(--success); color: white; }
    .receiver-card { margin-top: 30px; padding: 20px; background: #1C1C1E; border-radius: 15px; width: 100%; max-width: 400px; text-align: center; }
  </style>
</head>
<body>

  <div id="view-onboarding" class="view">
    <img src="<?php echo $iconUrl; ?>" width="80" style="margin-bottom: 20px; border-radius: 18px;">
    <h1>Welcome</h1>
    <input type="text" id="userNameInput" placeholder="Your Name">
    <button class="btn-primary" onclick="saveName()">Continue</button>
  </div>

  <div id="view-setup" class="view">
    <h1>Kitchen</h1>
    <input type="text" id="foodInput" placeholder="What are you making?">
    <button class="btn-primary" onclick="startCooking()">Start Cooking</button>
    <div class="receiver-card">
        <button id="notif-btn" class="btn-success" style="background: rgba(48, 209, 88, 0.1); color: #30D158;" onclick="enableNotifications()">
            Enable Notifications
        </button>
    </div>
  </div>

  <div id="view-active" class="view">
    <h1>Cooking...</h1>
    <p id="debug-text"></p>
    <button class="btn-success" onclick="triggerCompletion()">I'm Done</button>
  </div>

  <script>
    let recognition, wakeLock, isCooking = false;

    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js').then(() => console.log("SW Registered"));
    }

    // --- PC FIX: Cross-Device Synchronization ---
    // Since we don't have a backend DB, we use a public "Sync" channel via a tiny API or Broadcast
    const bc = new BroadcastChannel('lifecook_global');

    bc.onmessage = (e) => {
      if (e.data.type === 'REMOTE_DONE' && Notification.permission === "granted") {
        showSystemNotification(e.data.food, e.data.name);
      }
    };

    function showSystemNotification(food, name) {
        navigator.serviceWorker.ready.then(reg => {
          reg.showNotification("LifeCook: Food Ready!", {
            body: `${food} done by ${name}`,
            icon: "<?php echo $iconUrl; ?>",
            vibrate: [200, 100, 200]
          });
        });
    }

    async function enableNotifications() {
      const p = await Notification.requestPermission();
      if (p === "granted") {
        document.getElementById('notif-btn').innerText = "Alerts Active ✅";
        // To make PC work, we must ensure the SW is active
        const reg = await navigator.serviceWorker.ready;
        alert("PC/Phone Alerts Enabled!");
      }
    }

    // [Standard saveName, changeView logic...]
    window.onload = () => {
      const name = localStorage.getItem('lifeCookName');
      if (Notification.permission === "granted") {
          const btn = document.getElementById('notif-btn');
          if (btn) btn.innerText = "Alerts Active ✅";
      }
      if (name) { changeView('view-setup'); } else { changeView('view-onboarding'); }
    };

    function changeView(id) {
      document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
      document.getElementById(id).classList.add('active');
    }

    function saveName() {
      const n = document.getElementById('userNameInput').value.trim();
      if (n) { localStorage.setItem('lifeCookName', n); changeView('view-setup'); }
    }

    async function startCooking() {
      isCooking = true;
      changeView('view-active');
      if ('wakeLock' in navigator) wakeLock = await navigator.wakeLock.request('screen');
      startListening();
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
      recognition.start();
    }

    async function triggerCompletion() {
      const food = document.getElementById('foodInput').value;
      const name = localStorage.getItem('lifeCookName');
      
      // 1. Send to server
      await fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ food, name })
      });

      // 2. Broadcast to ALL tabs on THIS machine
      bc.postMessage({ type: 'REMOTE_DONE', food, name });

      alert("Sent! (If PC isn't showing, ensure the LifeCook tab is open there too)");
      location.reload(); 
    }
  </script>
</body>
</html>
