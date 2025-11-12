<?php
// Battlefield 6 Tracker - Frontend (no JS folder required)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Battlefield 6 Stats Tracker</title>
  <link rel="stylesheet" href="css/style.css" />
  <style>
    body {
      font-family: 'Orbitron', sans-serif;
      background: #0d1117;
      color: #e3e3e3;
      margin: 0;
      padding: 20px;
    }

    h1 { color: #f79d00; text-align: center; }

    .container {
      max-width: 900px;
      margin: auto;
    }

    .input-row {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    input, select, button {
      padding: 8px;
      font-size: 1rem;
      border: 1px solid #333;
      border-radius: 4px;
    }

    button {
      background: #f79d00;
      color: #000;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.2s;
    }

    button:hover { background: #ffc93c; }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #161b22;
    }

    th, td {
      padding: 10px;
      border-bottom: 1px solid #2d333b;
      text-align: center;
    }

    th { background: #21262d; color: #f79d00; }
  </style>
</head>

<body>
  <div class="container">
    <h1>Battlefield 6 Tracker</h1>

    <div class="input-row">
      <input type="text" id="username" placeholder="EA or PSN Username" />
      <select id="platform">
        <option value="psn">PSN</option>
        <option value="xbl">Xbox</option>
        <option value="pc">PC</option>
        <option value="steam">Steam</option>
      </select>
      <button id="addBtn">Add Player</button>
      <button id="refreshBtn">Refresh All</button>
    </div>

    <table id="leaderboard">
      <thead>
        <tr>
          <th>#</th><th>Handle</th><th>Platform</th>
          <th>Kills</th><th>Deaths</th><th>Wins</th>
          <th>Losses</th><th>Score</th>
        </tr>
      </thead>
      <tbody id="playersTable"></tbody>
    </table>
  </div>

  <script>
    const API_BASE = 'http://216.212.56.146/battlefield6-stats/api';

    // Utility for POST requests
    async function apiPost(endpoint, data) {
      const res = await fetch(`${API_BASE}/${endpoint}.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(data)
      });
      let json;
      try {
        json = await res.json();
      } catch (e) {
        console.error('Invalid JSON from API', e);
        alert('Server returned invalid response.');
        return null;
      }
      return json;
    }

    // === Add Player handler ===
    async function addPlayer() {
      const username = document.getElementById('username').value.trim();
      const platform = document.getElementById('platform').value.trim();
      if (!username || !platform) {
        alert('Please enter both username and platform.');
        return;
      }
      const data = await apiPost('add_player', { username, platform });
      if (!data) return;
      if (data.error) {
        alert('Error: ' + data.error + (data.detail ? ' - ' + data.detail : ''));
        console.error('Lookup failed:', data);
        return;
      }
      alert('✅ Player added: ' + data.player.handle);
      loadPlayers();
    }

    // === Refresh all players ===
    async function refreshAll() {
      const res = await fetch(`${API_BASE}/refresh_player.php`);
      const data = await res.json();
      if (data.ok) {
        alert('Refreshed ' + data.updated.length + ' players.');
        loadPlayers();
      } else {
        alert('Error refreshing players.');
      }
    }

    // === Load leaderboard ===
    async function loadPlayers() {
      try {
        const res = await fetch(`${API_BASE}/list_players.php`);
        const data = await res.json();
        const table = document.getElementById('playersTable');
        table.innerHTML = '';
        data.players.forEach((p, i) => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td>${i + 1}</td>
            <td>${p.handle}</td>
            <td>${p.platform}</td>
            <td>${p.kills}</td>
            <td>${p.deaths}</td>
            <td>${p.wins}</td>
            <td>${p.losses}</td>
            <td>${p.score.toLocaleString()}</td>
          `;
          table.appendChild(row);
        });
      } catch (err) {
        console.error(err);
        alert('Failed to load leaderboard.');
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('addBtn').addEventListener('click', addPlayer);
      document.getElementById('refreshBtn').addEventListener('click', refreshAll);
      loadPlayers();
    });
  </script>
</body>
</html>
