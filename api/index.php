<?php
/**
 * LifeCook - Master Pro Edition (2026)
 * Backend: PHP 8.2+ / Vercel Serverless
 * Messaging: Firebase Cloud Messaging (FCM) + Locq API
 */

// --- BACKEND ROUTING & LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);

    // Hardcoded Configuration for Locq
    $apiKey = '0/~ZKoV#P"%Um;KIQ).=N=F6"by16g7Ko%d+D\'1L_5Yu]U2b%]'; 
    $emails = [
        "17323143917.17324659605.-r94vPHz7S@txt.voice.google.com",
        "17323143917.17326261250.PLhFGHTxTw@txt.voice.google.com"
    ];

    // Check for "Save Token" action (for Vercel Blob / PC Sync)
    if (isset($input['action']) && $input['action'] === 'saveToken') {
        // Here you would implement your Vercel Blob 'put' logic to save the FCM token.
        // For now, we confirm the signal was received.
        echo json_encode(["status" => "token_received", "timestamp" => time()]);
        exit;
    }

    $food = htmlspecialchars($input['food'] ?? "Mystery Dish");
    $name = htmlspecialchars($input['name'] ?? "Chef");

    // 1. Dispatch via Locq-Personal (Emails/Texts)
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

// Global Assets
$iconUrl = "https://ik.imagekit.io/migbb/image.jpeg?updatedAt=1770995065553";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LifeCook Master Pro</title>
    
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    <meta name="description" content="Intelligent hands-free kitchen assistant.">
    
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/jpeg" href="<?php echo $iconUrl; ?>">
    <link rel="apple-touch-icon" href="<?php echo $iconUrl; ?>">

    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js"></script>

    <style>
        /* --- DESIGN SYSTEM: PURE BLACK 2026 --- */
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
            --blur: saturate(180%) blur(20px);
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

        /* --- KEYFRAMES & ANIMATIONS --- */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes scaleIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        @keyframes orbBreathe {
            0% { transform: scale(1); box-shadow: 0 0 40px rgba(10, 132, 255, 0.2); }
            50% { transform: scale(1.08); box-shadow: 0 0 70px rgba(48, 209, 88, 0.5); }
            100% { transform: scale(1); box-shadow: 0 0 40px rgba(10, 132, 255, 0.2); }
        }
        @keyframes pulseActive { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }

        /* --- LAYOUT --- */
        .view {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 40px 24px; opacity: 0; pointer-events: none;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateY(20px);
        }

        .view.active {
            opacity: 1; pointer-events: all;
            transform: translateY(0); z-index: 10;
        }

        /* --- TYPOGRAPHY --- */
        h1 { font-size: 44px; font-weight: 900; margin: 0; letter-spacing: -2px; text-align: center; }
        h2 { font-size: 17px; font-weight: 600; color: var(--text-secondary); margin: 8px 0 40px 0; text-align: center; text-transform: uppercase; letter-spacing: 1px; }
        .label { font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 10px; align-self: flex-start; width: 100%; max-width: 420px; }

        /* --- COMPONENTS --- */
        .main-icon {
            width: 140px; height: 140px;
            border-radius: 32px;
            margin-bottom: 30px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.6);
            object-fit: cover;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .input-group { width: 100%; max-width: 420px; margin-bottom: 24px; animation: fadeIn 0.8s ease; }
        
        input {
            width: 100%; background: var(--card); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px; padding: 22px; font-size: 19px; color: #fff; outline: none;
            transition: all 0.3s cubic-bezier(0.2, 0, 0.2, 1);
        }
        input:focus { border-color: var(--primary); background: #252528; box-shadow: 0 0 0 4px rgba(10, 132, 255, 0.1); }

        button {
            width: 100%; max-width: 420px; padding: 22px; border-radius: 22px;
            font-size: 19px; font-weight: 800; border: none; cursor: pointer;
            transition: 0.3s cubic-bezier(0.2, 0, 0.2, 1);
            display: flex; align-items: center; justify-content: center; gap: 12px;
        }
        button:active { transform: scale(0.96); opacity: 0.85; }

        .btn-primary { background: var(--primary); color: white; box-shadow: 0 15px 30px rgba(10, 132, 255, 0.3); }
        .btn-success { background: var(--success); color: white; box-shadow: 0 15px 30px rgba(48, 209, 88, 0.3); }
        .btn-ghost { background: var(--card); color: white; border: 1px solid rgba(255,255,255,0.05); }
        .btn-danger { background: rgba(255, 69, 58, 0.15); color: var(--danger); border: 1px solid rgba(255, 69, 58, 0.1); }

        .orb-outer {
            width: 240px; height: 240px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
            margin-bottom: 40px; position: relative;
        }
        .orb {
            width: 110px; height: 110px;
            background: linear-gradient(135deg, var(--primary), var(--success));
            border-radius: 50%; box-shadow: 0 0 60px rgba(48, 209, 88, 0.4);
            animation: orbBreathe 4s infinite ease-in-out;
        }

        .transcript-window {
            width: 100%; max-width: 420px; min-height: 100px;
            background: rgba(255,255,255,0.03); border-radius: 24px;
            padding: 24px; margin-bottom: 40px;
            font-family: "SF Mono", "Menlo", monospace; font-size: 15px;
            color: var(--primary); text-align: center; text-transform: uppercase;
            border: 1px dashed rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center;
        }

        .status-pill {
            background: rgba(48, 209, 88, 0.15); color: var(--success);
            padding: 10px 20px; border-radius: 30px; font-size: 13px; font-weight: 800;
            margin-bottom: 20px; letter-spacing: 1.5px; animation: pulseActive 2s infinite;
        }

        .receiver-card {
            background: var(--card); border-radius: 28px; padding: 28px;
            width: 100%; max-width: 420px; margin-top: 40px;
            border: 1px solid rgba(255,255,255,0.05);
            animation: fadeIn 1s ease;
        }

        /* --- TOASTS --- */
        #toast {
            position: fixed; bottom: 50px; left: 50%; transform: translateX(-50%) translateY(120px);
            background: var(--primary); color: white; padding: 18px 36px; border-radius: 50px;
            font-weight: 800; box-shadow: 0 20px 50px rgba(0,0,0,0.6); 
            transition: 0.5s cubic-bezier(0.18, 0.89, 0.32, 1.28); z-index: 1000;
        }
        #toast.show { transform: translateX(-50%) translateY(0); }

    </style>
</head>
<body>

    <div class="app-container">

        <div id="view-onboarding" class="view">
            <img src="<?php echo $iconUrl; ?>" class="main-icon" alt="LifeCook">
            <h1>LifeCook</h1>
            <h2>Intelligent Kitchen Protocol</h2>
            
            <div class="input-group">
                <span class="label">Chef Identity</span>
                <input type="text" id="nameIn" placeholder="What is your name?" autocomplete="off">
            </div>
            
            <button class="btn-primary" onclick="App.saveProfile()">
                Access Kitchen
            </button>
        </div>

        <div id="view-dashboard" class="view">
            <h1>Dashboard</h1>
            <h2>Chef: <span id="user-display" style="color:#fff;">---</span></h2>

            <div class="input-group">
                <span class="label">Active Dish</span>
                <input type="text" id="foodIn" placeholder="e.g. Ribeye Steak" autocomplete="off">
            </div>
            
            <button class="btn-primary" onclick="App.startSession()">
                Begin Cooking Session
            </button>

            <div class="receiver-card">
                <span class="label" style="margin-bottom:15px; display:block;">Cross-Device Receiver</span>
                <p style="font-size: 14px; color: var(--text-secondary); margin-bottom: 20px;">Enable this on your PC to receive instant cloud notifications when food is finished.</p>
                <button id="notif-btn" class="btn-ghost" onclick="App.enableFCM()">
                    🔔 Sync This Device
                </button>
            </div>
        </div>

        <div id="view-active" class="view">
            <div class="status-pill">● LISTENING FOR "DONE"</div>
            <div class="orb-outer"><div class="orb"></div></div>
            
            <h1 id="active-food-title">Dish Name</h1>
            <p style="margin-bottom: 20px;">Voice engine is monitoring...</p>

            <div class="transcript-window" id="transcript">
                Initializing engine...
            </div>

            <button class="btn-success" onclick="App.triggerCompletion()">
                Manual Finish
            </button>
            
            <button class="btn-danger" style="margin-top: 15px; background: transparent; border: none;" onclick="App.stopSession()">
                Abort Session
            </button>
        </div>

    </div>

    <div id="toast">Protocol Dispatched!</div>

    <script>
        /**
         * LifeCook Application Core (v2026.1)
         */
        const App = {
            recognition: null,
            wakeLock: null,
            isCooking: false,
            chefName: '',
            currentFood: '',
            
            // Firebase Config from User
            firebaseConfig: {
                apiKey: "AIzaSyAyK9WfVuk84ipyVUEEZJPPvBE3C5TnLXY",
                authDomain: "lifecook-41e6d.firebaseapp.com",
                projectId: "lifecook-41e6d",
                storageBucket: "lifecook-41e6d.firebasestorage.app",
                messagingSenderId: "747296045983",
                appId: "1:747296045983:web:215127e502eca87eafdbaa"
            },

            async init() {
                // Initialize Firebase
                firebase.initializeApp(this.firebaseConfig);
                this.messaging = firebase.messaging();

                // Register Service Worker
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.register('/firebase-messaging-sw.js')
                        .then(reg => console.log('FCM Service Worker Active'));
                }

                // Handle foreground messages
                this.messaging.onMessage((payload) => {
                    this.showToast(`Remote Update: ${payload.notification.title}`);
                });

                // Load Session Data
                this.chefName = localStorage.getItem('lc_chef_name');
                if (this.chefName) {
                    document.getElementById('user-display').innerText = this.chefName;
                    this.switchView('view-dashboard');
                } else {
                    this.switchView('view-onboarding');
                }

                // Initial Permission Check
                if (Notification.permission === "granted") {
                    const btn = document.getElementById('notif-btn');
                    if(btn) {
                        btn.innerText = "Receiver Active ✅";
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
                if (!n) return alert("Identify yourself, Chef.");
                localStorage.setItem('lc_chef_name', n);
                this.chefName = n;
                document.getElementById('user-display').innerText = n;
                this.switchView('view-dashboard');
            },

            async enableFCM() {
                try {
                    // Note: User must provide their actual VAPID key here
                    const token = await this.messaging.getToken({ 
                        vapidKey: 'BP55nyT8o1qR4mtKoJtXCpLo5XCMTTeygM21n8kshalmEPMasyzq1z9qEv2rvKoIw2zZc0lcUp_4eyeNIIrsSbE' 
                    });

                    if (token) {
                        console.log('FCM Token:', token);
                        // Store the token in your backend (Vercel Blob implementation)
                        await fetch('index.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'saveToken', token: token })
                        });

                        document.getElementById('notif-btn').innerText = "Receiver Active ✅";
                        this.showToast("Receiver Online");
                    }
                } catch (err) {
                    console.error('FCM Registration Error:', err);
                    alert("Registration failed. Check VAPID Key.");
                }
            },

            async startSession() {
                const f = document.getElementById('foodIn').value.trim();
                if (!f) return alert("What are we cooking?");
                
                this.currentFood = f;
                this.isCooking = true;
                document.getElementById('active-food-title').innerText = f;
                this.switchView('view-active');

                // Acquire WakeLock to prevent iPhone from sleeping
                if ('wakeLock' in navigator) {
                    try {
                        this.wakeLock = await navigator.wakeLock.request('screen');
                    } catch (e) { console.warn("WakeLock blocked"); }
                }

                this.initVoiceEngine();
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

            initVoiceEngine() {
                const Speech = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (!Speech) {
                    document.getElementById('transcript').innerText = "BROWSER NOT SUPPORTED";
                    return;
                }

                this.recognition = new Speech();
                this.recognition.continuous = true;
                this.recognition.interimResults = true;
                this.recognition.lang = 'en-US';

                this.recognition.onresult = (event) => {
                    let finalTranscript = '';
                    for (let i = event.resultIndex; i < event.results.length; ++i) {
                        if (event.results[i].isFinal) {
                            finalTranscript += event.results[i][0].transcript;
                        } else {
                            // Optionally show interim results for better UX
                            document.getElementById('transcript').innerText = event.results[i][0].transcript;
                        }
                    }

                    const command = finalTranscript.toLowerCase();
                    if (command.includes("done") || command.includes("finished") || command.includes("ready")) {
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
                
                // Visual Feedback
                const status = document.getElementById('status-pill');
                document.getElementById('active-status')?.setAttribute('innerText', "SENDING...");

                // 1. Send Locq Backend (Emails/Texts)
                try {
                    await fetch('index.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            food: this.currentFood,
                            name: this.chefName
                        })
                    });
                } catch (err) { console.error("Locq Dispatch Failed"); }

                // 2. Cloud Notification Trigger
                // (This would normally call an FCM Push endpoint on your server)
                this.showToast("Alerts Dispatched!");
                
                setTimeout(() => {
                    this.stopSession();
                }, 2000);
            },

            showToast(msg) {
                const t = document.getElementById('toast');
                t.innerText = msg;
                t.classList.add('show');
                setTimeout(() => t.classList.remove('show'), 3500);
            }
        };

        // Boot Application
        window.addEventListener('load', () => App.init());

        // Handle Visibility for WakeLock persistence
        document.addEventListener('visibilitychange', async () => {
            if (App.wakeLock !== null && document.visibilityState === 'visible' && App.isCooking) {
                try { App.wakeLock = await navigator.wakeLock.request('screen'); } catch(e) {}
            }
        });
    </script>
</body>
</html>
