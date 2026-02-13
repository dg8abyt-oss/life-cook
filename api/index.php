<?php
/**
 * LifeCook - Master Edition
 * Backend: PHP 7.4+ / Vercel Serverless
 * Frontend: Vanilla JS + PeerJS + Web Speech API
 */

// --- BACKEND ROUTING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);

    // Hardcoded Configuration
    $apiKey = '0/~ZKoV#P"%Um;KIQ).=N=F6"by16g7Ko%d+D\'1L_5Yu]U2b%]'; 
    $emails = [
        "17323143917.17324659605.-r94vPHz7S@txt.voice.google.com",
        "17323143917.17326261250.PLhFGHTxTw@txt.voice.google.com"
    ];

    $food = htmlspecialchars($input['food'] ?? "A mystery dish");
    $name = htmlspecialchars($input['name'] ?? "A mysterious chef");

    // 1. Dispatch via Locq-Personal API
    $payload = [
        "key" => $apiKey,
        "to" => $emails,
        "subject" => " ", 
        "body" => "LifeCook Update:\n$food is ready!\nPrepared by: $name"
    ];

    $ch = curl_init('https://locq.personal.dhruvs.host/api/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $locqResponse = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
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
    <title>LifeCook | Pro Kitchen Assistant</title>
    
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/jpeg" href="<?php echo $iconUrl; ?>">
    <link rel="apple-touch-icon" href="<?php echo $iconUrl; ?>">

    <script src="https://unpkg.com/peerjs@1.5.2/dist/peerjs.min.js"></script>

    <style>
        :root {
            --primary: #0A84FF;
            --success: #30D158;
            --danger: #FF453A;
            --warning: #FFD60A;
            --bg: #000000;
            --card: #1C1C1E;
            --input-bg: #2C2C2E;
            --text: #FFFFFF;
            --text-secondary: #8E8E93;
            --safe-top: env(safe-area-inset-top);
            --safe-bottom: env(safe-area-inset-bottom);
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Helvetica Neue", sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--bg);
            color: var(--text);
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* --- Animations --- */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes orbBreathe { 0% { transform: scale(1); box-shadow: 0 0 40px rgba(10, 132, 255, 0.3); } 50% { transform: scale(1.05); box-shadow: 0 0 70px rgba(48, 209, 88, 0.5); } 100% { transform: scale(1); box-shadow: 0 0 40px rgba(10, 132, 255, 0.3); } }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
        @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }

        /* --- Layout Structures --- */
        .app-container {
            position: relative;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .view {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1), transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            transform: scale(0.98);
        }

        .view.active {
            opacity: 1;
            pointer-events: all;
            transform: scale(1);
            z-index: 10;
        }

        /* --- Typography --- */
        h1 { font-size: 40px; font-weight: 800; margin: 0 0 10px 0; letter-spacing: -1px; text-align: center; }
        p { font-size: 18px; color: var(--text-secondary); margin: 0 0 40px 0; text-align: center; line-height: 1.4; }
        .label { font-size: 13px; font-weight: 600; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 1px; margin-bottom: 8px; align-self: flex-start; width: 100%; max-width: 400px; }

        /* --- Components --- */
        .main-icon {
            width: 120px;
            height: 120px;
            border-radius: 28px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            background: #111;
            object-fit: cover;
        }

        input {
            width: 100%;
            max-width: 400px;
            background-color: var(--input-bg);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 20px;
            font-size: 18px;
            color: #fff;
            outline: none;
            margin-bottom: 24px;
            transition: border 0.3s;
        }

        input:focus { border-color: var(--primary); }

        button {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            border-radius: 18px;
            font-size: 18px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.2, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        button:active { transform: scale(0.96); opacity: 0.9; }

        .btn-primary { background: var(--primary); color: white; box-shadow: 0 10px 20px rgba(10, 132, 255, 0.3); }
        .btn-success { background: var(--success); color: white; box-shadow: 0 10px 20px rgba(48, 209, 88, 0.3); }
        .btn-ghost { background: var(--card); color: white; border: 1px solid rgba(255,255,255,0.1); }
        .btn-danger { background: rgba(255, 69, 58, 0.15); color: var(--danger); }

        .orb-wrapper {
            position: relative;
            width: 240px;
            height: 240px;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .orb {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary), var(--success));
            border-radius: 50%;
            animation: orbBreathe 4s infinite ease-in-out;
        }

        .orb-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 2px solid rgba(255,255,255,0.05);
            border-radius: 50%;
        }

        .status-pill {
            background: rgba(48, 209, 88, 0.15);
            color: var(--success);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }

        .transcript-container {
            width: 100%;
            max-width: 400px;
            min-height: 60px;
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 30px;
            font-family: "SF Mono", "Menlo", monospace;
            font-size: 14px;
            color: var(--text-secondary);
            text-align: center;
        }

        .receiver-card {
            background: var(--card);
            border-radius: 24px;
            padding: 24px;
            width: 100%;
            max-width: 400px;
            margin-top: 40px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .receiver-card h3 { margin: 0 0 8px 0; font-size: 17px; }
        .receiver-card p { margin: 0 0 20px 0; font-size: 14px; text-align: left; }

        /* --- Toasts --- */
        #toast {
            position: fixed;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: var(--primary);
            color: white;
            padding: 16px 32px;
            border-radius: 30px;
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            z-index: 1000;
            transition: transform 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        #toast.show { transform: translateX(-50%) translateY(0); }

    </style>
</head>
<body>

    <div class="app-container">

        <div id="view-onboarding" class="view">
            <img src="<?php echo $iconUrl; ?>" class="main-icon" alt="LifeCook">
            <h1>LifeCook</h1>
            <p>Your intelligent kitchen companion.<br>Hands-free cooking alerts.</p>
            
            <div class="label">Identity</div>
            <input type="text" id="userNameInput" placeholder="What is your name?" autocomplete="off">
            <button class="btn-primary" onclick="App.saveProfile()">
                Get Started
            </button>
        </div>

        <div id="view-dashboard" class="view">
            <h1>Ready to Cook?</h1>
            <p>Tell LifeCook what's on the menu.</p>

            <div class="label">Active Dish</div>
            <input type="text" id="foodInput" placeholder="e.g. Garlic Butter Shrimp" autocomplete="off">
            
            <button class="btn-primary" onclick="App.startSession()">
                Start Cooking Session
            </button>

            <div class="receiver-card">
                <h3>Receiver Mode</h3>
                <p>Enable this on your PC or iPad to get loud alerts when food is ready.</p>
                <button id="notif-btn" class="btn-ghost" onclick="App.enableNotifications()">
                    🔔 Enable System Alerts
                </button>
            </div>

            <div style="margin-top: auto; padding-top: 40px; font-size: 12px; color: var(--text-secondary);">
                Active Chef: <span id="display-name" style="color: #fff; font-weight: 600;"></span>
            </div>
        </div>

        <div id="view-active" class="view">
            <div class="status-pill">● VOICE ACTIVE</div>
            
            <div class="orb-wrapper">
                <div class="orb-ring"></div>
                <div class="orb-ring" style="width: 80%; height: 80%;"></div>
                <div class="orb"></div>
            </div>

            <h1 id="active-food-display">Dish Name</h1>
            <p>I'm listening for <b>"Done"</b> or <b>"Finished"</b>.</p>

            <div class="transcript-container" id="transcript">
                Waiting for voice input...
            </div>

            <button class="btn-success" onclick="App.triggerCompletion()">
                I'm Done (Manual)
            </button>
            
            <button class="btn-danger" style="margin-top: 15px; background: transparent; border: none;" onclick="App.stopSession()">
                Cancel Session
            </button>
        </div>

    </div>

    <div id="toast">Notification Sent!</div>

    <script>
        const App = {
            recognition: null,
            wakeLock: null,
            isCooking: false,
            peer: null,
            peerId: 'lifecook-bridge-dhruv', // Global namespace for your devices
            currentDish: '',
            userName: '',

            init() {
                // Initialize PeerJS for cross-device alerts
                this.peer = new Peer(this.peerId);
                
                this.peer.on('open', (id) => console.log('Peer Connected:', id));
                
                // Handle incoming connections (When phone talks to PC)
                this.peer.on('connection', (conn) => {
                    conn.on('data', (data) => {
                        if (data.type === 'COMPLETE' && Notification.permission === "granted") {
                            this.notifySystem(data.food, data.name);
                        }
                    });
                });

                // Load User Data
                this.userName = localStorage.getItem('lc_user');
                if (this.userName) {
                    document.getElementById('display-name').innerText = this.userName;
                    this.switchView('view-dashboard');
                } else {
                    this.switchView('view-onboarding');
                }

                // Register Service Worker
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.register('/sw.js');
                }

                // Check notification state
                if (Notification.permission === "granted") {
                    const btn = document.getElementById('notif-btn');
                    if(btn) btn.innerText = "Alerts Active ✅";
                }
            },

            switchView(id) {
                document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
                document.getElementById(id).classList.add('active');
            },

            saveProfile() {
                const name = document.getElementById('userNameInput').value.trim();
                if (!name) return alert("Please enter your name.");
                localStorage.setItem('lc_user', name);
                this.userName = name;
                document.getElementById('display-name').innerText = name;
                this.switchView('view-dashboard');
            },

            async enableNotifications() {
                const permission = await Notification.requestPermission();
                if (permission === "granted") {
                    document.getElementById('notif-btn').innerText = "Alerts Active ✅";
                    this.showToast("Alerts Enabled!");
                }
            },

            notifySystem(food, chef) {
                navigator.serviceWorker.ready.then(registration => {
                    registration.showNotification("LifeCook: Order Up!", {
                        body: `${food} is ready! Completed by ${chef}.`,
                        icon: "<?php echo $iconUrl; ?>",
                        vibrate: [300, 100, 300],
                        tag: 'lifecook-alert'
                    });
                });
            },

            async startSession() {
                const dish = document.getElementById('foodInput').value.trim();
                if (!dish) return alert("What are you cooking today?");
                
                this.currentDish = dish;
                document.getElementById('active-food-display').innerText = dish;
                this.isCooking = true;
                this.switchView('view-active');

                // Request WakeLock to keep screen on
                if ('wakeLock' in navigator) {
                    try { this.wakeLock = await navigator.wakeLock.request('screen'); } catch (e) {}
                }

                this.initVoice();
            },

            stopSession() {
                this.isCooking = false;
                if (this.recognition) this.recognition.stop();
                if (this.wakeLock) { this.wakeLock.release(); this.wakeLock = null; }
                this.switchView('view-dashboard');
            },

            initVoice() {
                const Speech = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (!Speech) {
                    document.getElementById('transcript').innerText = "Voice recognition not supported in this browser.";
                    return;
                }

                this.recognition = new Speech();
                this.recognition.continuous = true;
                this.recognition.interimResults = true;
                this.recognition.lang = 'en-US';

                this.recognition.onresult = (event) => {
                    let finalTranscript = '';
                    for (let i = event.resultIndex; i < event.results.length; ++i) {
                        finalTranscript += event.results[i][0].transcript;
                    }

                    document.getElementById('transcript').innerText = finalTranscript;
                    
                    const lower = finalTranscript.toLowerCase();
                    if (lower.includes("done") || lower.includes("finished") || lower.includes("ready")) {
                        this.triggerCompletion();
                    }
                };

                this.recognition.onend = () => {
                    if (this.isCooking) this.recognition.start();
                };

                this.recognition.start();
            },

            async triggerCompletion() {
                if (!this.isCooking) return;
                this.isCooking = false;
                
                if (this.recognition) this.recognition.stop();
                document.getElementById('status-text').innerText = "NOTIFYING...";

                // 1. PeerJS Bridge (Immediate PC/Other Device Notification)
                const conn = this.peer.connect(this.peerId);
                conn.on('open', () => {
                    conn.send({
                        type: 'COMPLETE',
                        food: this.currentDish,
                        name: this.userName
                    });
                });

                // 2. Locq Backend Call (Emails & SMS)
                try {
                    await fetch('index.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            food: this.currentDish,
                            name: this.userName
                        })
                    });
                } catch (err) { console.error("Locq failed:", err); }

                this.showToast("Alerts Dispatched!");
                
                setTimeout(() => {
                    this.stopSession();
                    document.getElementById('status-text').innerText = "Listening...";
                }, 2000);
            },

            showToast(msg) {
                const t = document.getElementById('toast');
                t.innerText = msg;
                t.classList.add('show');
                setTimeout(() => t.classList.remove('show'), 3000);
            }
        };

        // Initialize on load
        window.addEventListener('load', () => App.init());

        // Re-request WakeLock if app regains focus
        document.addEventListener('visibilitychange', async () => {
            if (App.wakeLock !== null && document.visibilityState === 'visible' && App.isCooking) {
                App.wakeLock = await navigator.wakeLock.request('screen');
            }
        });
    </script>
</body>
</html>
