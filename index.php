<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Prompt Injection Lab</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
<style>
  body { margin:0; font-family:Arial,sans-serif; display:flex; flex-direction:column; height:100vh; background:#1e1e1e; color:#ddd; }
  #topbar { padding:10px 20px; border-bottom:1px solid #444; display:flex; gap:20px; align-items:center; }
  #topbar select, #topbar button { background:#333; color:#fff; border:1px solid #444; padding:4px 8px; border-radius:4px; cursor:pointer; }
  #messages { flex:1; padding:20px; overflow-y:auto; }
  .msg { margin-bottom:15px; padding:10px 14px; border-radius:8px; max-width:70%; white-space:pre-wrap; }
  .msg.user { background:#0e639c; margin-left:auto; }
  .msg.assistant { background:#333; }
  .msg.leak { border:2px solid #22c55e; }
  #banner { display:none; background:#16a34a; color:#fff; padding:10px 20px; font-weight:bold; text-align:center; }
  #inputArea { display:flex; padding:15px; border-top:1px solid #444; }
  #inputArea input { flex:1; padding:10px; border-radius:4px; border:none; }
  #inputArea button { margin-left:10px; padding:10px 20px; background:#0e639c; color:#fff; border:none; border-radius:4px; cursor:pointer; }
  pre { background:#111; padding:10px; border-radius:6px; overflow-x:auto; }
</style>
</head>
<body>
<div id="topbar">
  <strong>🎯 Prompt Injection Lab</strong>
  <select id="labLevelSelect" onchange="resetChat()">
    <option value="1">⚙️ Level 1: Guarded Vault (Easy)</option>
    <option value="2">🛡️ Level 2: Keyword Filter (Medium)</option>
    <option value="3">🔒 Level 3: Adversarial Sandbox (Hard)</option>
  </select>
  <button onclick="resetChat()">Reset chat</button>
</div>
<div id="banner">🚩 Secret leaked! Prompt injection succeeded.</div>
<div id="messages"></div>
<div id="inputArea">
  <input type="text" id="messageInput" placeholder="Try to extract the secret..." onkeydown="if(event.key==='Enter') sendMessage()">
  <button onclick="sendMessage()">Send</button>
</div>

<script>
const csrfToken = <?php echo json_encode($_SESSION['csrf_token']); ?>;
const level = () => document.getElementById('labLevelSelect').value;

function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

function renderMessage(role, content, leaked = false) {
  const div = document.createElement('div');
  div.className = 'msg ' + role + (leaked ? ' leak' : '');
  div.innerHTML = escapeHtml(content).replace(/```(\w*)\n([\s\S]*?)```/g,
    '<pre><code class="language-$1">$2</code></pre>');
  const box = document.getElementById('messages');
  box.appendChild(div);
  box.scrollTop = box.scrollHeight;
  div.querySelectorAll('pre code').forEach(el => hljs.highlightElement(el));
}

async function post(body) {
  const res = await fetch('chat_api.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-CSRF-Token':csrfToken},
    body: JSON.stringify(body)
  });
  const data = await res.json();
  if (!res.ok || data.error) throw new Error(data.error || `Request failed (${res.status})`);
  return data;
}

async function resetChat() {
  document.getElementById('messages').innerHTML = '';
  document.getElementById('banner').style.display = 'none';
  try { await post({reset: true, lab_level: level()}); } catch (e) { console.error(e); }
}

async function sendMessage() {
  const input = document.getElementById('messageInput');
  const message = input.value.trim();
  if (!message) return;
  renderMessage('user', message);
  input.value = '';
  try {
    const data = await post({message, lab_level: level()});
    renderMessage('assistant', data.reply, data.leaked);
    if (data.leaked) document.getElementById('banner').style.display = 'block';
  } catch (e) {
    renderMessage('assistant', '⚠️ Error: ' + e.message);
  }
}
</script>
</body>
</html>