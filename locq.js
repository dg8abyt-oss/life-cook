(function(window) {
    // Configured for your specific domain
    const ENDPOINT = 'https://locq-personal.dhruvs.host/api/send';

    function sendEmail(config) {
        // 1. Basic Validation
        if (!config.key) {
            console.error("Locq Error: Missing 'key' in configuration.");
            if (config.onError) config.onError("Missing API Key");
            return;
        }
        if (!config.to) {
            console.error("Locq Error: Missing 'to' (recipient) in configuration.");
            if (config.onError) config.onError("Missing Recipient");
            return;
        }

        // 2. Send Request
        fetch(ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                key: config.key,
                to: config.to,
                subject: config.subject || "New Message",
                body: config.body || ""
            })
        })
        .then(response => response.json())
        .then(data => {
            // 3. Handle Response
            if (data.status === 'success') {
                if (config.onSuccess) config.onSuccess();
            } else {
                const errorMsg = data.error || "Unknown server error";
                console.error("Locq Error:", errorMsg);
                if (config.onError) config.onError(errorMsg);
            }
        })
        .catch(error => {
            console.error("Locq Network Error:", error);
            if (config.onError) config.onError(error);
        });
    }

    // Expose the library to the window
    window.Locq = {
        send: sendEmail
    };

})(window);
