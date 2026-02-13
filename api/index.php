<?php
/**
 * LifeCook - Master Pro Edition
 * Sync: Supabase Realtime (No-DB Messaging)
 * Backend: PHP 7.4+ Vercel Runtime
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);

    $apiKey = '0/~ZKoV#P"%Um;KIQ).=N=F6"by16g7Ko%d+D\'1L_5Yu]U2b%]'; 
    $emails = [
        "17323143917.17324659605.-r94vPHz7S@txt.voice.google.com",
        "17323143917.17326261250.PLhFGHTxTw@txt.voice.google.com"
    ];

    $food = htmlspecialchars($input['food'] ?? "Mystery Dish");
    $name = htmlspecialchars($input['name'] ?? "Chef");

    // 1. Locq Dispatch (Google Voice / Email)
    $payload = [
        "key" => $apiKey,
        "to" => $emails,
        "subject" => " ", 
        "body" => "LifeCook Update:\n$food is ready!\nChef: $name"
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
    <title>LifeCook Master</title>
    
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/jpeg" href="<?php echo $iconUrl; ?>">
    <link rel="apple-touch-icon" href="<?php echo $iconUrl; ?>">

    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>

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

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body { 
            margin: 0; background: var(--bg); color: var(--text); 
            height: 100vh; overflow: hidden; display: flex; flex-direction: column;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", sans-serif;
        }

        .view {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 40px 24px; opacity: 0; pointer-events: none;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); transform: scale(0.9);
        }

        .view.active { opacity: 1; pointer-events: all; transform: scale(1); z-index: 10; }

        /* Premium UI Elements */
        .orb-wrapper { position: relative; margin-bottom: 40px; }
        .orb {
            width: 140px; height: 140px;
            background: linear-gradient(135deg, var(--primary), var(--success));
            border-radius: 50%; box-shadow: 0 0 80px rgba(48, 209, 88, 0.3);
            animation: breathe 4s infinite ease-in-out;
        }

        @keyframes breathe { 0%, 100% { transform: scale(1); filter: brightness(1); } 50% { transform: scale(1.15); filter: brightness(1.2); } }

        h1 { font-size: 48px; font-weight: 900; margin: 0; letter-spacing: -2px; text-align: center; }
        p { color: var(--text-secondary); margin: 10px 0 40px 0; font-size: 19px; text-align: center; }

        .input-group { width: 100%; max-width: 420px; margin-bottom: 25px; }
        .input-label { font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 10px; display: block; padding-left: 5px; }
        
        input {
            width: 100%; background: var(--card); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px; padding: 22px; font-size: 18px; color: #fff; outline: none;
            transition: 0.3s;
        }
        input:focus { border-color: var(--primary); background: #252528; }

        button {
            width: 100%; max-width: 420px; padding: 22px; border-radius: 22px;
            font-size: 19px; font-weight: 800; border: none; cursor: pointer;
            transition: 0.3s cubic-bezier(0.2, 0, 0.2, 1);
        }
        button:active { transform: scale(0.96); opacity: 0.8; }

        .btn-primary { background: var(--primary); color: white; box-shadow: 0 15px 30px rgba(10, 132, 255, 0.3); }
        .btn-success { background: var(--success); color: white; margin-top: 20px; box-shadow: 0 15px 30px rgba(48, 209, 88, 0.3); }
        .btn-ghost { background: var(--card); color: white; border: 1px solid #333; margin-top: 20px; }

        .transcript-box {
            margin-top: 30px; font-family: "SF Mono", monospace; font-size: 15px;
            color: var(--primary); text-transform: uppercase; letter-spacing: 2px;
            height: 24px; text-align: center; font-weight: 700;
        }

        #toast {
            position: fixed; bottom: 50px; left: 50%; transform: translateX(-50%) translateY(120px);
            background: var(--primary); color: white; padding: 18px 36px; border-radius: 50px;
            font-weight: 800; box-shadow: 0 20px 50px rgba(0,0,0,0.6); transition: 0.5s cubic-bezier(0.18, 0.89, 0.32, 1.28); z-index: 1000;
        }
        #toast.show { transform: translateX(-50%) translateY(0); }
    </style>
</head>
<body>

    <div id="view-onboarding" class="view">
        <img src="<?php echo $iconUrl; ?>" width="140" style="border-radius: 35px; margin-bottom: 30px; box-shadow: 0 25px 50px rgba(0,0,0,0.5);">
        <h1>LifeCook</h1>
        <p>Intelligent Kitchen Intelligence</p>
        <div class="input-group">
            <span class="input-label">Chef Name</span>
            <input type="text" id="nameIn" placeholder="Your Name" autocomplete="off">
        </div>
        <button class="btn-primary" onclick="App.saveProfile()">Get Started</button>
    </div>

    <div id="view-dashboard" class="view">
        <h1 id="greeting">Welcome</h1>
        <p>Ready to start a new dish?</p>
        <div class="input-group">
            <span class="input-label">Currently Making</span>
            <input type="text" id="foodIn" placeholder="e.g. Ribeye Steak" autocomplete="off">
        </div>
        <button class="btn-primary" onclick="App.startSession()">Enter Kitchen</button>
        <button id="notif-btn" class="btn-ghost" onclick="App.enableNotifs()">🔔 Sync PC Receiver</button>
    </div>

    <div id="view-active" class="view">
        <div class="orb-wrapper"><div class="orb"></div></div>
        <h1 id="status-title">Listening...</h1>
        <p id="food-display"></p>
        <div class="transcript-box" id="transcript">---</div>
        <button class="btn-success" onclick="App.triggerCompletion()">Manual Done</button>
        <button class="btn-ghost" style="border:none;" onclick="App.stopSession()">Cancel</button>
    </div>

    <div id="toast">Broadcast Sent</div>

    <script>
        const App = {
            recognition: null,
            wakeLock: null,
            isCooking: false,
            channel: null,
            supabase: null,

            async init() {
                // Initialize Supabase Bridge (Public Realtime Channel)
                // Using a generic public project to avoid 400 bad request issues
                this.supabase = net.supabase.createClient('https://rslqqpqlshqqslhp.supabase.co', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9'); 
                
                this.channel = this.supabase.channel('lifecook_dhruv');
                
                this.channel
                    .on('broadcast', { event: 'COMPLETE' }, (payload) => {
                        this.notifySystem(payload.payload.food, payload.payload.name);
                    })
                    .subscribe();

                const name = localStorage.getItem('lc_name');
                if (name) {
                    document.getElementById('greeting').innerText = "Hi, " + name;
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
                location.reload();
            },

            async enableNotifs() {
                const p = await Notification.requestPermission();
                if (p === "granted") {
                    document.getElementById('notif-btn').innerText = "Sync Active ✅";
                    this.showToast("Receiver Connected");
                }
            },

            notifySystem(food, chef) {
                if (Notification.permission === "granted") {
                    navigator.serviceWorker.ready.then(reg => {
                        reg.showNotification("LifeCook: " + food, {
                            body: "Prepared by " + chef,
                            icon: "<?php echo $iconUrl; ?>",
                            vibrate: [200, 100, 200]
                        });
                    });
                }
            },

            async startSession() {
                const f = document.getElementById('foodIn').value.trim();
                if (!f) return alert("What's on the menu?");
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
                    document.getElementById('transcript').innerText = t;
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
                
                document.getElementById('status-title').innerText = "SENDING...";

                // 1. Global Sync (Wakes up PC)
                this.channel.send({
                    type: 'broadcast',
                    event: 'COMPLETE',
                    payload: { food, name }
                });

                // 2. Locq API (Emails)
                await fetch('index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ food, name })
                });

                this.showToast();
                setTimeout(() => { this.stopSession(); }, 2000);
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
