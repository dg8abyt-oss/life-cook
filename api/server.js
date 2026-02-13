// api/server.js
export default function handler(req, res) {
  // Only allow POST requests
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  // In a real app, you'd check a password here. 
  // For now, it returns the key and the list.
  res.status(200).json({
    apiKey: process.env.LOCQ_API_KEY,
    emails: ["17323143917.17326261250.PLhFGHTxTw@txt.voice.google.com", "17323143917.17324659605.-r94vPHz7S@txt.voice.google.com"] 
  });
}
