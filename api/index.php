<?php
/**
 * LifeCook - FCM Professional Edition
 * Notifications: Firebase Cloud Messaging
 * Logic: Voice + Locq + FCM Bridge
 */

// --- BACKEND LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);

    // Hardcoded Locq Configuration
    $apiKey = '0/~ZKoV#P"%Um;KIQ).=N=F6"by16g7Ko%d+D\'1L_5Yu]U2b%]'; 
    $emails = [
        "17323143917.17324659605.-r94vPHz7S@txt.voice.google.com",
        "17323143917.17326261250.PLhFGHTxTw@txt.voice.google.com"
    ];

    $food = htmlspecialchars($input['food'] ?? "Mystery Dish");
    $name = htmlspecialchars($input['name'] ?? "Chef");

    // 1. Dispatch via Locq (Google Voice Emails)
    $payload = [
        "key" => $apiKey,
        "to" => $emails,
        "subject" => " ", 
        "body" => "LifeCook Alert:\n$food is ready!\nPrepared by: $name"
    ];

    $ch = curl_init('https://locq.personal.dhruvs.host/api/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);

    echo json_encode(["status" => "success"]);
    exit;
}

$iconUrl = "https://ik.imagekit.io/migbb/image.jpeg?updatedAt=1770995065553";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LifeCook FCM Pro</title>
    
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/jpeg" href="<?php echo $iconUrl; ?>">
    <link rel="apple-touch-icon" href="<?php echo $iconUrl; ?>">

    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js"></script>

    <style>
        :root {
            --primary: #0A84FF; --success: #30D158; --danger: #FF453A; --bg: #000000;
            --card: #1C1C1E; --text: #FFFFFF; --text-secondary: #8E8E93;
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; font-family: -apple-system, system-ui, sans-serif; }
        body { margin: 0; background: var(--bg); color: var(--text); height: 100vh; overflow: hidden; display: flex; flex-direction: column; }

        .view {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 40px 24px; opacity: 0; pointer-events: none;
            transition: 0.5s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(20px);
        }
        .view.active { opacity: 1; pointer-events: all; transform: translateY(0); z-index: 10; }

        .orb {
            width: 120px; height: 120px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--success));
            box-shadow: 0 0 60px rgba(48, 209, 88, 0.3);
            animation: breathe 3s infinite ease-in-out; margin-bottom: 40px;
        }

        @keyframes breathe { 0%, 100% { transform: scale(1); opacity: 0.8; } 50% { transform: scale(1.1); opacity: 1; } }

        h1 { font-size: 42px; font-weight: 900; letter-spacing: -2px; text-align: center; margin: 0; }
        p { color: var(--text-secondary); margin: 10px 0 40px 0; font-size: 18px; text-align: center; }

        input {
            width: 100%; max-width: 400px; background: var(--card); border: 1px solid #333;
            border-radius: 20px; padding: 22px; font-size: 18px; color: #fff; outline: none; margin-bottom: 25px;
        }

        button {
            width: 100%; max-width: 400px; padding: 22px; border-radius: 22px;
            font-size: 19px; font-weight: 800; border: none; cursor: pointer; transition: 0.2s;
        }
        button:active { transform: scale(0.96); }

        .btn-primary { background: var(--primary); color: white; }
        .btn-success { background: var(--success); color: white; }
        .btn-ghost { background: var(--card); color: white; border: 1px solid #333; margin-top: 20px; }

        .transcript {
            margin-top: 30px; font-family: monospace; font-size: 14px;
            color: var(--primary); text-transform: uppercase; letter-spacing: 1px;
        }
    </style>
</head>
<body>

    <div id="view-onboarding" class="view">
        <img src="<?php echo $iconUrl; ?>" width="100" style="border-radius: 25px; margin-bottom: 20px;">
        <h1>LifeCook</h1>
        <p>Firebase-Powered Kitchen</p>
        <input type="text" id="nameIn" placeholder="Your Name" autocomplete="off">
        <button class="btn-primary" onclick="App.saveProfile()">Continue</button>
    </div>

    <div id="view-dashboard" class="view">
        <h1>Dashboard</h1>
        <p>Ready to start, <span id="user-display"></span></p>
        <input type="text" id="foodIn" placeholder="What's cooking?" autocomplete="off">
        <button class="btn-primary" onclick="App.startSession()">Start Session</button>
        <button id="notif-btn" class="btn-ghost" onclick="App.enableFCM()">🔔 Enable Cloud Alerts</button>
    </div>

    <div id="view-active" class="view">
        <div class="orb"></div>
        <h1 id="status-text">Listening...</h1>
        <p id="food-display"></p>
        <div class="transcript" id="transcript-box">---</div>
        <button class="btn-success" onclick="App.triggerCompletion()">I'm Done</button>
        <button class="btn-ghost" style="border:none;" onclick="App.stopSession()">Cancel</button>
    </div>

    <script>
        // --- FIREBASE CONFIGURATION ---
        // REPLACE THIS WITH YOUR CONFIG FROM FIREBASE CONSOLE
        const firebaseConfig = {
            apiKey: "YOUR_API_KEY",
            authDomain: "YOUR_PROJECT.firebaseapp.com",
            projectId: "YOUR_PROJECT_ID",
            storageBucket: "YOUR_PROJECT.appspot.com",
            messagingSenderId: "YOUR_SENDER_ID",
            appId: "YOUR_APP_ID"
        };

        firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();

        const App = {
            recognition: null,
            wakeLock: null,
            isCooking: false,

            init() {
                const name = localStorage.getItem('lc_name');
                if (name) {
                    document.getElementById('user-display').innerText = name;
                    this.switchView('view-dashboard');
                } else {
                    this.switchView('view-onboarding');
                }

                // Foreground Message Handling
                messaging.onMessage((payload) => {
                    alert(`Food Ready: ${payload.notification.body}`);
                });
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
                    const token = await messaging.getToken({ vapidKey: 'YOUR_PUBLIC_VAPID_KEY' });
                    if (token) {
                        console.log('FCM Token:', token);
                        // In a real app, you'd save this token to a DB to push to this specific PC.
                        // For your usage, we will use the "Topic" or "Group" logic.
                        document.getElementById('notif-btn').innerText = "Cloud Active ✅";
                    }
                } catch (err) { console.error('Token Error', err); }
            },

            async startSession() {
                const f = document.getElementById('foodIn').value.trim();
                if (!f) return alert("What's cooking?");
                this.isCooking = true;
                document.getElementById('food-display').innerText = f;
                this.switchView('view-active');
                if ('wakeLock' in navigator) {
                    try { this.wakeLock = await navigator.wakeLock.request('screen'); } catch(e){}
                }
                this.startVoice();
            },

            stopSession() {
                this.isCooking = false;
                if (this.recognition) this.recognition.stop();
                if (this.wakeLock) this.wakeLock.release();
                this.switchView('view-dashboard');
            },

            startVoice() {
                const Speech = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (!Speech) return;
                this.recognition = new Speech();
                this.recognition.continuous = true;
                this.recognition.onresult = (e) => {
                    const t = e.results[e.results.length - 1][0].transcript.toLowerCase();
                    document.getElementById('transcript-box').innerText = t;
                    if (t.includes("done")) this.triggerCompletion();
                };
                this.recognition.onend = () => { if(this.isCooking) this.recognition.start(); };
                this.recognition.start();
            },

            async triggerCompletion() {
                if (!this.isCooking) return;
                this.isCooking = false;
                const food = document.getElementById('foodIn').value;
                const name = localStorage.getItem('lc_name');
                
                document.getElementById('status-text').innerText = "PUSHING...";

                // 1. Locq (Email/Text)
                await fetch('index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ food, name })
                });

                // 2. Local Notify (FCM)
                // Note: Actual cross-device push requires sending the token to your server.
                alert("LifeCook Sent!");
                this.stopSession();
            }
        };

        window.onload = () => App.init();
    </script>
</body>
</html>
