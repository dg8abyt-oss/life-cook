<?php
/**
 * LifeCook - Master Pro Edition
 * Logic: Direct Service Worker + Locq API
 * Runtime: Vercel PHP 8.2+
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

    // 1. Dispatch via Locq-Personal (Emails/Texts)
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
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        echo json_encode(["status" => "error", "message" => $curlError]);
    } else {
        echo json_encode(["status" => "success", "locq" => json_decode($response)]);
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

    <style>
        :root {
            --primary: #0A84FF;
            --success: #30D158;
            --danger: #FF453A;
            --bg: #000000;
            --card: #1C1C1E;
            --input-bg: #2C2C2E;
            --text: #FFFFFF;
            --text-secondary: #8E8E93;
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", sans-serif;
        }

        body {
            margin: 0; padding: 0;
            background-color: var(--bg);
            color: var(--text);
            height: 100vh; width: 100vw;
            overflow: hidden;
            display: flex; flex-direction: column;
        }

        /* --- Animations --- */
        @keyframes orbBreathe {
            0%, 100% { transform: scale(1); box-shadow: 0 0 40px rgba(10, 132, 255, 0.2); }
            50% { transform: scale(1.1); box-shadow: 0 0 80px rgba(48, 209, 88, 0.5); }
        }
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* --- Structure --- */
        .view {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 40px 24px; opacity: 0; pointer-events: none;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            transform: scale(0.96);
        }

        .view.active {
            opacity: 1; pointer-events: all;
            transform: scale(1); z-index: 10;
        }

        /* --- Components --- */
        .app-icon {
            width: 120px; height: 120px;
            border-radius: 28px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            object-fit: cover;
        }

        h1 { font-size: 42px; font-weight: 900; margin: 0; letter-spacing: -1.5px; text-align: center; }
        p { color: var(--text-secondary); margin: 10px 0 40px 0; font-size: 19px; text-align: center; line-height: 1.4; }

        .form-container { width: 100%; max-width: 420px; animation: slideIn 0.6s ease; }
        .input-label { font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 10px; display: block; padding-left: 4px; }
        
        input {
            width: 100%; background: var(--card); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px; padding: 22px; font-size: 18px; color: #fff; outline: none; margin-bottom: 24px;
            transition: all 0.3s;
        }
        input:focus { border-color: var(--primary); background: #252528; }

        button {
            width: 100%; max-width: 420px; padding: 22px; border-radius: 22px;
            font-size: 19px; font-weight: 800; border: none; cursor: pointer;
            transition: 0.3s cubic-bezier(0.2, 0, 0.2, 1);
            display: flex; align-items: center; justify-content: center; gap: 12px;
        }
        button:active { transform: scale(0.96); opacity: 0.9; }

        .btn-primary { background: var(--primary); color: white; box-shadow: 0 15px 30px rgba(10, 132, 255, 0.3); }
        .btn-success { background: var(--success); color: white; box-shadow: 0 15px 30px rgba(48, 209, 88, 0.3); }
        .btn-ghost { background: var(--card); color: white; border: 1px solid rgba(255,255,255,0.1); margin-top: 20px; }

        .orb-outer {
            width: 200px; height: 200px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.03); margin-bottom: 40px;
        }

        .orb {
            width: 100px; height: 100px;
            background: linear-gradient(135deg, var(--primary), var(--success));
            border-radius: 50%; box-shadow: 0 0 60px rgba(48, 209, 88, 0.4);
            animation: orbBreathe 4s infinite ease-in-out;
        }

        .transcript-display {
            width: 100%; max-width: 420px; min-height: 80px;
            background: rgba(255,255,255,0.05); border-radius: 18px;
            padding: 20px; margin-bottom: 40px;
            font-family: "SF Mono", "Menlo", monospace; font-size: 14px;
            color: var(--primary); text-align: center; text-transform: uppercase;
            display: flex; align-items: center; justify-content: center;
        }

        #toast {
            position: fixed; bottom: 50px; left: 50%; transform: translateX(-50%) translateY(120px);
            background: var(--primary); color: white; padding: 18px 36px; border-radius: 50px;
            font-weight: 800; box-shadow: 0 20px 50px rgba(0,0,0,0.6); transition: 0.5s cubic-bezier(0.18, 0.89, 0.32, 1.28); z-index: 1000;
        }
        #toast.show { transform: translateX(-50%) translateY(0); }

        .status-badge {
            background: rgba(48, 209, 88, 0.15); color: var(--success);
            padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 700;
            margin-bottom: 20px; letter-spacing: 1px;
        }
    </style>
</head>
<body>

    <div class="app-container">

        <div id="view-onboarding" class="view">
            <img src="<?php echo $iconUrl; ?>" class="app-icon" alt="LifeCook">
            <h1>LifeCook</h1>
            <p>Welcome to the future of cooking.<br>Hands-free. Intelligent. Direct.</p>
            
            <div class="form-container">
                <span class="input-label">Identity</span>
                <input type="text" id="nameIn" placeholder="Enter Your Name" autocomplete="off">
                <button class="btn-primary" onclick="App.saveProfile()">Get Started</button>
            </div>
        </div>

        <div id="view-dashboard" class="view">
            <h1>Kitchen</h1>
            <p>Ready to start a new dish?</p>

            <div class="form-container">
                <span class="input-label">Active Dish</span>
                <input type="text" id="foodIn" placeholder="e.g. Ribeye Steak" autocomplete="off">
                
                <button class="btn-primary" onclick="App.startSession()">
                    Start Session
                </button>

                <button id="notif-btn" class="btn-ghost" onclick="App.enableNotifs()">
                    🔔 Enable System Alerts
                </button>
            </div>

            <div style="margin-top: 40px; font-size: 14px; color: var(--text-secondary);">
                Active Chef: <span id="user-display" style="color:#fff; font-weight:700;"></span>
            </div>
        </div>

        <div id="view-active" class="view">
            <div class="status-badge">● VOICE LISTENING</div>
            <div class="orb-outer"><div class="orb"></div></div>
            
            <h1 id="active-food-display">Dish Name</h1>
            <p>Say <b>"Done"</b> or <b>"Finished"</b></p>

            <div class="transcript-display" id="transcript">
                Listening for command...
            </div>

            <div class="form-container">
                <button class="btn-success" onclick="App.triggerCompletion()">
                    I'm Done
                </button>
                <button class="btn-ghost" style="border:none;" onclick="App.stopSession()">
                    Cancel Session
                </button>
            </div>
        </div>

    </div>

    <div id="toast">Dispatched!</div>

    <script>
        /**
         * LifeCook Application Core
         */
        const App = {
            recognition: null,
            wakeLock: null,
            isCooking: false,
            chefName: '',
            currentFood: '',

            init() {
                // Register Service Worker
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.register('/sw.js')
                        .then(reg => console.log('Service Worker Registered'));
                }

                // Load Profile
                this.chefName = localStorage.getItem('lc_chef');
                if (this.chefName) {
                    document.getElementById('user-display').innerText = this.chefName;
                    this.switchView('view-dashboard');
                } else {
                    this.switchView('view-onboarding');
                }

                // Check Notification Permission
                if (Notification.permission === "granted") {
                    const btn = document.getElementById('notif-btn');
                    if(btn) {
                        btn.innerText = "Alerts Active ✅";
                        btn.style.color = "#30D158";
                    }
                }
            },

            switchView(id) {
                document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
                document.getElementById(id).classList.add('active');
            },

            saveProfile() {
                const n = document.getElementById('nameIn').value.trim();
                if (!n) return alert("Please enter your name.");
                localStorage.setItem('lc_chef', n);
                this.chefName = n;
                document.getElementById('user-display').innerText = n;
                this.switchView('view-dashboard');
            },

            async enableNotifs() {
                const permission = await Notification.requestPermission();
                if (permission === "granted") {
                    document.getElementById('notif-btn').innerText = "Alerts Active ✅";
                    this.showToast("Alerts Enabled");
                }
            },

            async startSession() {
                const f = document.getElementById('foodIn').value.trim();
                if (!f) return alert("What are you cooking?");
                
                this.currentFood = f;
                this.isCooking = true;
                document.getElementById('active-food-display').innerText = f;
                this.switchView('view-active');

                // Acquire WakeLock (Keep screen on)
                if ('wakeLock' in navigator) {
                    try {
                        this.wakeLock = await navigator.wakeLock.request('screen');
                    } catch (err) { console.error("WakeLock failed:", err); }
                }

                this.initVoiceRecognition();
            },

            stopSession() {
                this.isCooking = false;
                if (this.recognition) this.recognition.stop();
                if (this.wakeLock) {
                    this.wakeLock.release();
                    this.wakeLock = null;
                }
                this.switchView('view-dashboard');
            },

            initVoiceRecognition() {
                const Speech = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (!Speech) {
                    document.getElementById('transcript').innerText = "VOICE NOT SUPPORTED";
                    return;
                }

                this.recognition = new Speech();
                this.recognition.continuous = true;
                this.recognition.interimResults = true;
                this.recognition.lang = 'en-US';

                this.recognition.onresult = (event) => {
                    const transcript = event.results[event.results.length - 1][0].transcript.toLowerCase();
                    document.getElementById('transcript').innerText = transcript;
                    
                    if (transcript.includes("done") || transcript.includes("finished") || transcript.includes("completed")) {
                        this.triggerCompletion();
                    }
                };

                this.recognition.onend = () => {
                    if (this.isCooking) {
                        try { this.recognition.start(); } catch(e) {}
                    }
                };

                this.recognition.start();
            },

            async triggerCompletion() {
                if (!this.isCooking) return;
                this.isCooking = false;
                
                if (this.recognition) this.recognition.stop();
                
                // Update UI state
                const status = document.getElementById('active-status');
                if(status) status.innerText = "NOTIFYING...";

                // 1. Dispatch Locq Backend (Emails/Texts)
                try {
                    await fetch('index.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            food: this.currentFood,
                            name: this.chefName
                        })
                    });
                } catch (e) { console.error("Backend failed"); }

                // 2. Direct Service Worker Notification
                // This triggers the notification on the device you are currently on.
                if (Notification.permission === "granted" && 'serviceWorker' in navigator) {
                    const reg = await navigator.serviceWorker.ready;
                    // We send a message to the SW to handle the notification correctly
                    if (reg.active) {
                        reg.active.postMessage({
                            type: 'NOTIFY',
                            title: 'LifeCook: Order Up!',
                            body: `${this.currentFood} is finished by ${this.chefName}`
                        });
                    }
                }

                this.showToast("Broadcast Dispatched!");
                
                setTimeout(() => {
                    this.stopSession();
                    if(status) status.innerText = "Listening...";
                }, 2000);
            },

            showToast(msg) {
                const t = document.getElementById('toast');
                if (msg) t.innerText = msg;
                t.classList.add('show');
                setTimeout(() => t.classList.remove('show'), 3000);
            }
        };

        // Initialize App
        window.addEventListener('load', () => App.init());

        // Handle visibility for WakeLock persistence
        document.addEventListener('visibilitychange', async () => {
            if (App.wakeLock !== null && document.visibilityState === 'visible' && App.isCooking) {
                try { App.wakeLock = await navigator.wakeLock.request('screen'); } catch(e) {}
            }
        });
    </script>
</body>
</html>
