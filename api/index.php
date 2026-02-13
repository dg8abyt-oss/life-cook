<?php
/**
 * LifeCook - Global Bridge Edition
 * Backend: PHP 7.4+ / Vercel Serverless
 * Sync: PubNub Real-time Network
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);

    $apiKey = '0/~ZKoV#P"%Um;KIQ).=N=F6"by16g7Ko%d+D\'1L_5Yu]U2b%]'; 
    $emails = [
        "17323143917.17324659605.-r94vPHz7S@txt.voice.google.com",
        "17323143917.17326261250.PLhFGHTxTw@txt.voice.google.com"
    ];

    $food = htmlspecialchars($input['food'] ?? "A mystery dish");
    $name = htmlspecialchars($input['name'] ?? "A mysterious chef");

    // 1. Locq Dispatch (Google Voice Emails)
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
    <title>LifeCook Pro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/jpeg" href="<?php echo $iconUrl; ?>">
    <link rel="apple-touch-icon" href="<?php echo $iconUrl; ?>">

    <script src="https://cdn.pubnub.com/sdk/javascript/pubnub.7.2.2.min.js"></script>

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

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; font-family: -apple-system, system-ui, sans-serif; }
        body { margin: 0; background: var(--bg); color: var(--text); height: 100vh; overflow: hidden; display: flex; flex-direction: column; }

        .view {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 40px 24px; opacity: 0; pointer-events: none;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(20px);
        }

        .view.active { opacity: 1; pointer-events: all; transform: translateY(0); z-index: 10; }

        .orb {
            width: 120px; height: 120px;
            background: linear-gradient(135deg, var(--primary), var(--success));
            border-radius: 50%; margin-bottom: 40px;
            box-shadow: 0 0 60px rgba(48, 209, 88, 0.3);
            animation: breathe 3s infinite ease-in-out;
        }

        @keyframes breathe { 0%, 100% { transform: scale(1); filter: brightness(1); } 50% { transform: scale(1.1); filter: brightness(1.3); } }

        h1 { font-size: 42px; font-weight: 900; margin: 0; letter-spacing: -2px; }
        p { color: var(--text-secondary); margin: 10px 0 40px 0; font-size: 18px; }

        .input-group { width: 100%; max-width: 400px; margin-bottom: 25px; }
        .input-label { font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 8px; display: block; }
        
        input {
            width: 100%; background: var(--card); border: 1px solid #333;
            border-radius: 18px; padding: 20px; font-size: 18px; color: #fff; outline: none;
            transition: border-color 0.3s;
        }
        input:focus { border-color: var(--primary); }

        button {
            width: 100%; max-width: 400px; padding: 20px; border-radius: 20px;
            font-size: 18px; font-weight: 700; border: none; cursor: pointer;
            transition: 0.2s cubic-bezier(0.2, 0, 0.2, 1);
        }
        button:active { transform: scale(0.97); opacity: 0.8; }

        .btn-primary { background: var(--primary); color: white; box-shadow: 0 10px 25px rgba(10, 132, 255, 0.3); }
        .btn-success { background: var(--success); color: white; margin-top: 20px; box-shadow: 0 10px 25px rgba(48, 209, 88, 0.3); }
        .btn-ghost { background: var(--card); color: white; border: 1px solid #333; margin-top: 20px; }

        .transcript {
            margin-top: 30px; font-family: "SF Mono", monospace; font-size: 14px;
            color: var(--primary); min-height: 24px; text-transform: uppercase; letter-spacing: 1px;
        }

        #toast {
            position: fixed; bottom: 40px; left: 50%; transform: translateX(-50%) translateY(100px);
            background: var(--success); color: white; padding: 16px 32px; border-radius: 40px;
            font-weight: 700; box-shadow: 0 10px 30px rgba(0,0,0,0.5); transition: 0.4s; z-index: 1000;
        }
        #toast.show { transform: translateX(-50%) translateY(0); }
    </style>
</head>
<body>

    <div id="view-onboarding" class="view">
        <img src="<?php echo $iconUrl; ?>" width="120" style="border-radius: 30px; margin-bottom: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.4);">
        <h1>LifeCook</h1>
        <p>Your AI Kitchen Assistant</p>
        <div class="input-group">
            <span class="input-label">Identity</span>
            <input type="text" id="nameIn" placeholder="Who is cooking?" autocomplete="off">
        </div>
        <button class="btn-primary" onclick="App.saveProfile()">Initialize App</button>
    </div>

    <div id="view-dashboard" class="view">
        <h1>Dashboard</h1>
        <p>Welcome, <span id="user-display" style="color:#fff; font-weight:700;"></span></p>
        <div class="input-group">
            <span class="input-label">Active Meal</span>
            <input type="text" id="foodIn" placeholder="e.g. Pasta Carbonara" autocomplete="off">
        </div>
        <button class="btn-primary" onclick="App.startSession()">Start Voice Session</button>
        <button id="notif-btn" class="btn-ghost" onclick="App.enableNotifs()">🔔 Sync This Device</button>
    </div>

    <div id="view-active" class="view">
        <div class="orb"></div>
        <h1 id="status-title">Listening...</h1>
        <p id="food-display" style="margin-bottom:10px;"></p>
        <div class="transcript" id="transcript-box">---</div>
        <button class="btn-success" onclick="App.triggerCompletion()">Manual Finish</button>
        <button class="btn-ghost" style="border:none;" onclick="App.stopSession()">Cancel Session</button>
    </div>

    <div id="toast">Broadcast Dispatched!</div>

    <script>
        const App = {
            recognition: null,
            wakeLock: null,
            isCooking: false,
            pubnub: null,
            channel: 'lifecook_global_dhruv', // Private sync channel

            init() {
                // Initialize PubNub for Real-time Global Sync
                this.pubnub = new PubNub({
                    publishKey: 'pub-c-46a482d8-639a-4161-9c17-45e3f4124996', // Demo Key
                    subscribeKey: 'sub-c-57270f20-8e1d-11e8-a72a-9e7379d750d5', // Demo Key
                    userId: "user-" + Math.random().toString(36).substring(7)
                });

                // Listen for messages from ANY device
                this.pubnub.subscribe({ channels: [this.channel] });
                this.pubnub.addListener({
                    message: (s) => {
                        const data = s.message;
                        if (data.type === 'COMPLETE' && Notification.permission === "granted") {
                            this.notifySystem(data.food, data.name);
                        }
                    }
                });

                const name = localStorage.getItem('lc_name');
                if (name) {
                    document.getElementById('user-display').innerText = name;
                    this.switchView('view-dashboard');
                } else {
                    this.switchView('view-onboarding');
                }

                if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');
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
                document.getElementById('user-display').innerText = n;
                this.switchView('view-dashboard');
            },

            async enableNotifs() {
                const p = await Notification.requestPermission();
                if (p === "granted") {
                    document.getElementById('notif-btn').innerText = "Sync Active ✅";
                    this.showToast("Receiver Online");
                }
            },

            notifySystem(food, chef) {
                navigator.serviceWorker.ready.then(reg => {
                    reg.showNotification("LifeCook: Order Up!", {
                        body: `${food} is ready! Chef: ${chef}`,
                        icon: "<?php echo $iconUrl; ?>",
                        vibrate: [300, 100, 300]
                    });
                });
            },

            async startSession() {
                const f = document.getElementById('foodIn').value.trim();
                if (!f) return alert("What are you making?");
                this.isCooking = true;
                document.getElementById('food-display').innerText = "PREPARING: " + f;
                this.switchView('view-active');
                if ('wakeLock' in navigator) {
                    try { this.wakeLock = await navigator.wakeLock.request('screen'); } catch(e){}
                }
                this.startVoice();
            },

            stopSession() {
                this.isCooking = false;
                if (this.recognition) this.recognition.stop();
                if (this.wakeLock) { this.wakeLock.release(); this.wakeLock = null; }
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
                
                document.getElementById('status-title').innerText = "BROADCASTING...";

                // 1. Global PubNub Sync (Wakes up PC)
                this.pubnub.publish({
                    channel: this.channel,
                    message: { type: 'COMPLETE', food, name }
                });

                // 2. Locq API (Emails)
                try {
                    await fetch('index.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ food, name })
                    });
                } catch (err) {}

                this.showToast("Dispatched Everywhere");
                setTimeout(() => {
                    this.stopSession();
                    document.getElementById('status-title').innerText = "Listening...";
                }, 2000);
            },

            showToast(msg) {
                const t = document.getElementById('toast');
                if(msg) t.innerText = msg;
                t.classList.add('show');
                setTimeout(() => t.classList.remove('show'), 3000);
            }
        };

        window.onload = () => App.init();
    </script>
</body>
</html>
