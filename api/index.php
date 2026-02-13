<?php
/**
 * LifeCook - Master Pro "Black Edition" (v2.9)
 * Integration: Discord Webhook + whapi.cloud + FCM + Locq
 * WhatsApp Token: yD2KdpjbQ61IXqz2rXrqOoH139QrkgOO
 * Runtime: Vercel Serverless (PHP 8.2)
 */

// --- BACKEND DISPATCH ENGINE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);

    $apiKey = '0/~ZKoV#P"%Um;KIQ).=N=F6"by16g7Ko%d+D\'1L_5Yu]U2b%]'; 
    $emails = [
        "17323143917.17324659605.-r94vPHz7S@txt.voice.google.com",
        "17323143917.17326261250.PLhFGHTxTw@txt.voice.google.com"
    ];

    $food = htmlspecialchars($input['food'] ?? "Unknown Dish");
    $name = htmlspecialchars($input['name'] ?? "Chef");

    // --- CHANNEL 1: Discord Webhook ---
    // Replace with your actual Discord Webhook URL
    $discordUrl = "YOUR_DISCORD_WEBHOOK_URL_HERE"; 
    $discordPayload = [
        "content" => "🔔 **LifeCook Alert**",
        "embeds" => [[
            "title" => "Cooking Session Complete",
            "description" => "**$food** is ready to serve!",
            "color" => 3066993, // Success Green
            "fields" => [
                ["name" => "Chef", "value" => $name, "inline" => true]
            ],
            "footer" => ["text" => "LifeCook Master Pro v2.9"]
        ]]
    ];

    $disCh = curl_init($discordUrl);
    curl_setopt($disCh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($disCh, CURLOPT_POST, true);
    curl_setopt($disCh, CURLOPT_POSTFIELDS, json_encode($discordPayload));
    curl_setopt($disCh, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($disCh);
    curl_close($disCh);

    // --- CHANNEL 2: whapi.cloud (WhatsApp) ---
    $waToken = "yD2KdpjbQ61IXqz2rXrqOoH139QrkgOO";
    $waPayload = [
        "to" => "17326261250@s.whatsapp.net",
        "body" => "🥘 *LifeCook Update*\n\nYour dish *($food)* is ready!\nPrepared by: *$name*"
    ];

    $waCh = curl_init('https://gate.whapi.cloud/messages/text');
    curl_setopt($waCh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($waCh, CURLOPT_POST, true);
    curl_setopt($waCh, CURLOPT_POSTFIELDS, json_encode($waPayload));
    curl_setopt($waCh, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $waToken,
        'Content-Type: application/json'
    ]);
    curl_exec($waCh);
    curl_close($waCh);

    // --- CHANNEL 3: Locq API ---
    $locqPayload = ["key" => $apiKey, "to" => $emails, "body" => "LifeCook: $food ready by $name"];
    $locqCh = curl_init('https://locq.personal.dhruvs.host/api/send');
    curl_setopt($locqCh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($locqCh, CURLOPT_POST, true);
    curl_setopt($locqCh, CURLOPT_POSTFIELDS, json_encode($locqPayload));
    curl_setopt($locqCh, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($locqCh);
    curl_close($locqCh);

    echo json_encode(["status" => "success", "dispatched" => ["discord", "whatsapp", "locq"]]);
    exit;
}

$iconUrl = "https://ik.imagekit.io/migbb/image.jpeg?updatedAt=1770995065553";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LifeCook Master | Discord Edition</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="<?php echo $iconUrl; ?>">
    <link rel="apple-touch-icon" href="<?php echo $iconUrl; ?>">

    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js"></script>

    <style>
        :root { --primary: #0A84FF; --success: #30D158; --bg: #000000; --card: #1C1C1E; --text: #FFFFFF; --text-sec: #8E8E93; --safe-top: env(safe-area-inset-top); }
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; font-family: -apple-system, sans-serif; }
        body { margin: 0; background: var(--bg); color: var(--text); height: 100vh; overflow: hidden; display: flex; flex-direction: column; padding-top: var(--safe-top); }
        
        .view { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 24px; opacity: 0; pointer-events: none; transition: 0.7s cubic-bezier(0.2, 0.8, 0.2, 1); }
        .view.active { opacity: 1; pointer-events: all; transform: translateY(0); z-index: 10; }
        
        .app-icon { width: 160px; height: 160px; border-radius: 40px; margin-bottom: 30px; box-shadow: 0 30px 60px rgba(0,0,0,0.9); border: 1px solid rgba(255,255,255,0.1); }
        h1 { font-size: 54px; font-weight: 900; letter-spacing: -3px; margin: 0; text-align: center; }
        p { color: var(--text-sec); font-size: 20px; text-align: center; margin: 10px 0 40px; }
        
        input { width: 100%; max-width: 480px; background: var(--card); border: 1px solid rgba(255,255,255,0.05); border-radius: 24px; padding: 26px; font-size: 19px; color: #fff; outline: none; margin-bottom: 24px; transition: 0.4s; }
        input:focus { border-color: var(--primary); }
        
        button { width: 100%; max-width: 480px; padding: 24px; border-radius: 24px; font-size: 21px; font-weight: 800; border: none; cursor: pointer; transition: 0.3s; }
        button:active { transform: scale(0.95); }
        
        .btn-primary { background: var(--primary); color: white; box-shadow: 0 20px 40px rgba(10, 132, 255, 0.3); }
        .btn-success { background: var(--success); color: white; margin-top: 20px; }
        .btn-ghost { background: var(--card); color: white; border: 1px solid #333; margin-top: 20px; }

        .orb { width: 130px; height: 130px; background: linear-gradient(135deg, var(--primary), var(--success)); border-radius: 50%; animation: pulse 4s infinite ease-in-out; margin-bottom: 40px; }
        @keyframes pulse { 0%, 100% { transform: scale(1); filter: brightness(1); } 50% { transform: scale(1.15); filter: brightness(1.25); } }
        
        .transcript { width: 100%; max-width: 480px; min-height: 120px; background: rgba(255,255,255,0.02); border-radius: 30px; padding: 30px; margin-bottom: 40px; color: var(--primary); text-align: center; font-family: monospace; border: 1px dashed rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; }
        #toast { position: fixed; bottom: 60px; left: 50%; transform: translateX(-50%) translateY(150px); background: var(--primary); color: white; padding: 20px 45px; border-radius: 50px; font-weight: 800; transition: 0.6s cubic-bezier(0.18, 0.89, 0.32, 1.28); z-index: 1000; }
        #toast.show { transform: translateX(-50%) translateY(0); }
    </style>
</head>
<body>

    <div id="view-onboarding" class="view active">
        <img src="<?php echo $iconUrl; ?>" class="app-icon">
        <h1>LifeCook</h1>
        <p>Advanced kitchen intelligence.</p>
        <input type="text" id="nameIn" placeholder="Chef Name" autocomplete="off">
        <button class="btn-primary" onclick="App.saveProfile()">Initialize</button>
    </div>

    <div id="view-dashboard" class="view">
        <h1 id="greeting">Welcome</h1>
        <p>Deploy session.</p>
        <input type="text" id="foodIn" placeholder="Dish Name" autocomplete="off">
        <button class="btn-primary" onclick="App.startSession()">Enter Kitchen</button>
        <button id="notif-btn" class="btn-ghost" onclick="App.enableFCM()">🔔 Sync PC Receiver</button>
    </div>

    <div id="view-active" class="view">
        <div class="orb"></div>
        <h1 id="status-title">Listening...</h1>
        <p id="food-display" style="color:#fff; font-weight:700;"></p>
        <div class="transcript" id="transcript-box">Awaiting command...</div>
        <button class="btn-success" onclick="App.triggerCompletion()">Manual Done</button>
        <button class="btn-ghost" style="border:none;" onclick="App.stopSession()">Abort</button>
    </div>

    <div id="toast">Discord Broadcast Sent!</div>

    <script>
        const App = {
            recognition: null,
            isCooking: false,
            firebaseConfig: {
                apiKey: "AIzaSyAyK9WfVuk84ipyVUEEZJPPvBE3C5TnLXY",
                authDomain: "lifecook-41e6d.firebaseapp.com",
                projectId: "lifecook-41e6d",
                storageBucket: "lifecook-41e6d.firebasestorage.app",
                messagingSenderId: "747296045983",
                appId: "1:747296045983:web:215127e502eca87eafdbaa"
            },
            async init() {
                firebase.initializeApp(this.firebaseConfig);
                this.messaging = firebase.messaging();
                const name = localStorage.getItem('lc_name');
                if (name) {
                    document.getElementById('greeting').innerText = "Hi, " + name;
                    this.switchView('view-dashboard');
                }
                if ('serviceWorker' in navigator) navigator.serviceWorker.register('/firebase-messaging-sw.js');
            },
            switchView(id) {
                document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
                document.getElementById(id).classList.add('active');
            },
            saveProfile() {
                const n = document.getElementById('nameIn').value.trim();
                if (!n) return;
                localStorage.setItem('lc_name', n);
                location.reload();
            },
            async enableFCM() {
                try {
                    const vapidKey = 'BP55nyT8o1qR4mtKoJtXCpLo5XCMTTeygM21n8kshalmEPMasyzq1z9qEv2rvKoIw2zZc0lcUp_4eyeNIIrsSbE';
                    const token = await this.messaging.getToken({ vapidKey });
                    if (token) {
                        await fetch('index.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'syncToken', token }) });
                        document.getElementById('notif-btn').innerText = "Sync Active ✅";
                        this.showToast("Cloud Bridge Active");
                    }
                } catch (e) { alert("FCM Error."); }
            },
            async startSession() {
                const f = document.getElementById('foodIn').value.trim();
                if (!f) return;
                this.isCooking = true;
                document.getElementById('food-display').innerText = f;
                this.switchView('view-active');
                if ('wakeLock' in navigator) await navigator.wakeLock.request('screen');
                this.startVoice();
            },
            stopSession() {
                this.isCooking = false;
                if (this.recognition) this.recognition.stop();
                this.switchView('view-dashboard');
            },
            startVoice() {
                const Speech = window.SpeechRecognition || window.webkitSpeechRecognition;
                this.recognition = new Speech();
                this.recognition.continuous = true;
                this.recognition.onresult = (e) => {
                    const t = e.results[e.results.length - 1][0].transcript.toLowerCase();
                    document.getElementById('transcript-box').innerText = t;
                    if (t.includes("done") || t.includes("finished")) this.triggerCompletion();
                };
                this.recognition.onend = () => { if(this.isCooking) this.recognition.start(); };
                this.recognition.start();
            },
            async triggerCompletion() {
                if (!this.isCooking) return;
                this.isCooking = false;
                const food = document.getElementById('foodIn').value;
                const name = localStorage.getItem('lc_name');
                document.getElementById('status-title').innerText = "DISPATCHING...";
                await fetch('index.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ food, name }) });
                this.showToast();
                setTimeout(() => this.stopSession(), 3000);
            },
            showToast() {
                const t = document.getElementById('toast');
                t.classList.add('show');
                setTimeout(() => t.classList.remove('show'), 4000);
            }
        };
        window.onload = () => App.init();
    </script>
</body>
</html>e
