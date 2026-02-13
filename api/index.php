<?php
/**
 * LifeCook - Master Pro Edition (v2.6)
 * Author: Dhruv Gowda
 * Logic: FCM + Locq API + Voice Engine
 */

// --- BACKEND DISPATCH ENGINE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);

    // Hardcoded Locq Configuration
    $apiKey = '0/~ZKoV#P"%Um;KIQ).=N=F6"by16g7Ko%d+D\'1L_5Yu]U2b%]'; 
    $emails = [
        "17323143917.17324659605.-r94vPHz7S@txt.voice.google.com",
        "17323143917.17326261250.PLhFGHTxTw@txt.voice.google.com"
    ];

    $food = htmlspecialchars($input['food'] ?? "Unknown Dish");
    $name = htmlspecialchars($input['name'] ?? "Chef");

    // 1. DISPATCH TO LOCQ (Emails/SMS)
    $payload = [
        "key" => $apiKey,
        "to" => $emails,
        "subject" => " ", 
        "body" => "LifeCook Pro Update:\n$food is ready!\nPrepared by: $name"
    ];

    $ch = curl_init('https://locq.personal.dhruvs.host/api/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Prevent Vercel Timeout
    $locqResponse = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    // 2. TOKEN STORAGE MOCK (For PC Receiver)
    if (isset($input['action']) && $input['action'] === 'saveToken') {
        echo json_encode(["status" => "token_synced", "time" => time()]);
        exit;
    }

    if ($curlError) {
        error_log("Locq Error: " . $curlError);
        echo json_encode(["status" => "error", "message" => $curlError]);
    } else {
        echo json_encode(["status" => "success", "locq" => json_decode($locqResponse)]);
    }
    exit;
}

$iconUrl = "https://ik.imagekit.io/migbb/image.jpeg?updatedAt=1770995065553";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LifeCook Master</title>
    
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
            --primary: #0A84FF;
            --success: #30D158;
            --danger: #FF453A;
            --bg: #000000;
            --card: #1C1C1E;
            --text: #FFFFFF;
            --text-secondary: #8E8E93;
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; font-family: -apple-system, sans-serif; }
        body { margin: 0; background: var(--bg); color: var(--text); height: 100vh; overflow: hidden; display: flex; flex-direction: column; }

        .app-container { position: relative; flex: 1; display: flex; flex-direction: column; }

        .view {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 40px 24px; opacity: 0; pointer-events: none;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            transform: scale(0.96);
        }

        .view.active { opacity: 1; pointer-events: all; transform: scale(1); z-index: 10; }

        /* --- UI ELEMENTS --- */
        .main-icon {
            width: 140px; height: 140px; border-radius: 32px;
            margin-bottom: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.6);
            border: 1px solid rgba(255,255,255,0.1);
        }

        h1 { font-size: 42px; font-weight: 900; margin: 0; letter-spacing: -1.5px; text-align: center; }
        p { color: var(--text-secondary); margin: 10px 0 40px 0; font-size: 19px; text-align: center; }

        .input-group { width: 100%; max-width: 420px; margin-bottom: 24px; }
        .label { font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 10px; display: block; }
        
        input {
            width: 100%; background: var(--card); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px; padding: 22px; font-size: 18px; color: #fff; outline: none;
            transition: 0.3s;
        }
        input:focus { border-color: var(--primary); }

        button {
            width: 100%; max-width: 420px; padding: 22px; border-radius: 22px;
            font-size: 19px; font-weight: 800; border: none; cursor: pointer;
            transition: 0.3s cubic-bezier(0.2, 0, 0.2, 1);
        }
        button:active { transform: scale(0.96); }

        .btn-primary { background: var(--primary); color: white; box-shadow: 0 15px 30px rgba(10, 132, 255, 0.3); }
        .btn-success { background: var(--success); color: white; box-shadow: 0 15px 30px rgba(48, 209, 88, 0.3); }
        .btn-ghost { background: var(--card); color: white; border: 1px solid #333; margin-top: 20px; }

        .orb {
            width: 120px; height: 120px;
            background: linear-gradient(135deg, var(--primary), var(--success));
            border-radius: 50%; box-shadow: 0 0 60px rgba(48, 209, 88, 0.4);
            animation: breathe 4s infinite ease-in-out; margin-bottom: 40px;
        }

        @keyframes breathe { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }

        .transcript {
            margin-top: 25px; font-family: monospace; font-size: 14px;
            color: var(--primary); text-transform: uppercase; letter-spacing: 1px;
            min-height: 24px; text-align: center;
        }

        #toast {
            position: fixed; bottom: 50px; left: 50%; transform: translateX(-50%) translateY(120px);
            background: var(--primary); color: white; padding: 18px 36px; border-radius: 50px;
            font-weight: 800; transition: 0.5s; z-index: 1000;
        }
        #toast.show { transform: translateX(-50%) translateY(0); }
    </style>
</head>
<body>

    <div class="app-container">
        <div id="view-onboarding" class="view">
            <img src="<?php echo $iconUrl; ?>" class="main-icon">
            <h1>LifeCook</h1>
            <p>Voice-Activated Kitchen Pro</p>
            <div class="input-group">
                <span class="label">Chef Identity</span>
                <input type="text" id="nameIn" placeholder="Your Name" autocomplete="off">
            </div>
            <button class="btn-primary" onclick="App.saveProfile()">Get Started</button>
        </div>

        <div id="view-dashboard" class="view">
            <h1>Dashboard</h1>
            <p>Welcome, <span id="user-display" style="color:#fff; font-weight:700;"></span></p>
            <div class="input-group">
                <span class="label">Dish Name</span>
                <input type="text" id="foodIn" placeholder="e.g. Ribeye Steak" autocomplete="off">
            </div>
            <button class="btn-primary" onclick="App.startSession()">Start Kitchen Session</button>
            <button id="notif-btn" class="btn-ghost" onclick="App.enableFCM()">🔔 Sync PC Receiver</button>
        </div>

        <div id="view-active" class="view">
            <div class="orb"></div>
            <h1 id="status-title">Listening...</h1>
            <p id="food-display"></p>
            <div class="transcript" id="transcript-box">---</div>
            <button class="btn-success" onclick="App.triggerCompletion()">Manual Done</button>
            <button class="btn-ghost" style="border:none;" onclick="App.stopSession()">Cancel</button>
        </div>
    </div>

    <div id="toast">Dispatched!</div>

    <script>
        const App = {
            recognition: null,
            wakeLock: null,
            isCooking: false,
            
            firebaseConfig: {
                apiKey: "AIzaSyAyK9WfVuk84ipyVUEEZJPPvBE3C5TnLXY",
                authDomain: "lifecook-41e6d.firebaseapp.com",
                projectId: "lifecook-41e6d",
                storageBucket: "lifecook-41e6d.firebasestorage.app",
                messagingSenderId: "747296045983",
                appId: "1:747296045983:web:215127e502eca87eafdbaa"
            },

            init() {
                firebase.initializeApp(this.firebaseConfig);
                this.messaging = firebase.messaging();

                const name = localStorage.getItem('lc_name');
                if (name) {
                    document.getElementById('user-display').innerText = name;
                    this.switchView('view-dashboard');
                } else {
                    this.switchView('view-onboarding');
                }

                if ('serviceWorker' in navigator) navigator.serviceWorker.register('/firebase-messaging-sw.js');
                if (Notification.permission === "granted") {
                    const b = document.getElementById('notif-btn');
                    if(b) b.innerText = "Sync Active ✅";
                }
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
                    // YOU MUST PASTE YOUR PUBLIC VAPID KEY HERE
                    const token = await this.messaging.getToken({ vapidKey: 'YOUR_VAPID_KEY' });
                    if (token) {
                        await fetch('index.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'saveToken', token: token })
                        });
                        document.getElementById('notif-btn').innerText = "Sync Active ✅";
                    }
                } catch (e) { alert("VAPID Error. Check console."); }
            },

            async startSession() {
                const f = document.getElementById('foodIn').value.trim();
                if (!f) return alert("What are you making?");
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

                try {
                    const res = await fetch('index.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ food, name })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        this.showToast();
                        setTimeout(() => this.stopSession(), 2000);
                    } else {
                        alert("Locq Error: " + data.message);
                    }
                } catch (err) { alert("Network error."); }
            },

            showToast() {
                const t = document.getElementById('toast');
                t.classList.add('show');
                setTimeout(() => t.classList.remove('show'), 3000);
            }
        };

        window.onload = () => App.init();
    </script>
</body>
</html>
