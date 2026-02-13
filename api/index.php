<?php
/**
 * LifeCook - Master Edition (Storage & Logic Fixed)
 * Backend: PHP 7.4+ / Vercel Serverless
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
    <title>LifeCook | Pro</title>
    
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
            --text: #FFFFFF;
            --text-secondary: #8E8E93;
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; font-family: -apple-system, system-ui, sans-serif; }
        body { margin: 0; background: var(--bg); color: var(--text); height: 100vh; overflow: hidden; display: flex; flex-direction: column; }

        .view {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 40px 24px; opacity: 0; pointer-events: none;
            transition: all 0.4s ease; transform: scale(0.95);
        }

        .view.active { opacity: 1; pointer-events: all; transform: scale(1); }

        .orb {
            width: 100px; height: 100px;
            background: linear-gradient(135deg, var(--primary), var(--success));
            border-radius: 50%; margin-bottom: 40px;
            box-shadow: 0 0 50px rgba(10, 132, 255, 0.4);
            animation: breathe 3s infinite ease-in-out;
        }

        @keyframes breathe { 0%, 100% { transform: scale(1); opacity: 0.8; } 50% { transform: scale(1.1); opacity: 1; } }

        h1 { font-size: 38px; font-weight: 800; margin: 0 0 10px 0; text-align: center; }
        p { color: var(--text-secondary); margin-bottom: 40px; text-align: center; }

        input {
            width: 100%; max-width: 400px; background: var(--card); border: 1px solid #333;
            border-radius: 16px; padding: 20px; font-size: 18px; color: #fff; margin-bottom: 20px; outline: none;
        }

        button {
            width: 100%; max-width: 400px; padding: 20px; border-radius: 18px;
            font-size: 18px; font-weight: 700; border: none; cursor: pointer;
            transition: 0.2s;
        }

        .btn-primary { background: var(--primary); color: white; }
        .btn-success { background: var(--success); color: white; margin-top: 20px; }
        .btn-ghost { background: var(--card); color: white; margin-top: 20px; border: 1px solid #333; }
        
        .transcript {
            margin-top: 20px; font-family: monospace; font-size: 14px;
            color: var(--text-secondary); text-align: center;
            min-height: 20px; width: 100%;
        }

        #toast {
            position: fixed; bottom: 50px; left: 50%; transform: translateX(-50%) translateY(100px);
            background: var(--primary); color: white; padding: 12px 24px; border-radius: 30px;
            font-weight: 600; transition: 0.4s; z-index: 999;
        }
        #toast.show { transform: translateX(-50%) translateY(0); }
    </style>
</head>
<body>

    <div id="view-onboarding" class="view">
        <img src="<?php echo $iconUrl; ?>" width="100" style="border-radius: 22px; margin-bottom: 20px;">
        <h1>LifeCook</h1>
        <p>Your Voice-Activated Kitchen.</p>
        <input type="text" id="nameIn" placeholder="Enter Your Name">
        <button class="btn-primary" onclick="App.saveProfile()">Continue</button>
    </div>

    <div id="view-dashboard" class="view">
        <h1>Dashboard</h1>
        <p>Welcome back, <span id="user-display"></span></p>
        <input type="text" id="foodIn" placeholder="What's cooking?">
        <button class="btn-primary" onclick="App.startSession()">Start Kitchen Session</button>
        <button id="notif-btn" class="btn-ghost" onclick="App.enableNotifs()">Enable System Alerts</button>
    </div>

    <div id="view-active" class="view">
        <div class="orb"></div>
        <h1 id="status-title">Listening...</h1>
        <p id="food-display"></p>
        <div class="transcript" id="transcript-box">Waiting for voice...</div>
        <button class="btn-success" onclick="App.triggerCompletion()">Manual Done</button>
        <button class="btn-ghost" style="border:none;" onclick="App.stopSession()">Cancel</button>
    </div>

    <div id="toast">Message Sent!</div>

    <script>
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

                if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');
                
                // Permission Check
                if (Notification.permission === "granted") {
                    const b = document.getElementById('notif-btn');
                    if(b) b.innerText = "Alerts Active ✅";
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
                if (p === "granted") document.getElementById('notif-btn').innerText = "Alerts Active ✅";
            },

            async startSession() {
                const f = document.getElementById('foodIn').value.trim();
                if (!f) return alert("Enter a dish name!");
                
                this.isCooking = true;
                document.getElementById('food-display').innerText = f;
                this.switchView('view-active');

                // WakeLock
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
                this.recognition.interimResults = true;

                this.recognition.onresult = (e) => {
                    const t = e.results[e.results.length - 1][0].transcript.toLowerCase();
                    document.getElementById('transcript-box').innerText = t;
                    if (t.includes("done") || t.includes("finished")) {
                        this.triggerCompletion();
                    }
                };

                this.recognition.onend = () => { if(this.isCooking) this.recognition.start(); };
                this.recognition.start();
            },

            async triggerCompletion() {
                if (!this.isCooking) return;
                this.isCooking = false;
                
                const statusTitle = document.getElementById('status-title');
                if (statusTitle) statusTitle.innerText = "SENDING...";

                const food = document.getElementById('foodIn').value;
                const name = localStorage.getItem('lc_name');

                try {
                    await fetch('index.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ food, name })
                    });
                    
                    this.showToast();
                    
                    // Local Browser Notification
                    if (Notification.permission === "granted") {
                        new Notification("LifeCook", { body: `${food} is ready!`, icon: "<?php echo $iconUrl; ?>" });
                    }

                } catch (err) {}

                setTimeout(() => {
                    this.stopSession();
                    if (statusTitle) statusTitle.innerText = "Listening...";
                }, 2000);
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
