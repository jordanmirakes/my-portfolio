<?php
session_start();





if (!isset($_SESSION['user'])) {
    echo "❌ Please log in.";
    exit;
}

$user = $_SESSION['user'];
$account_number = $user['account_number'];

try {
    $db = new PDO('sqlite:/home/jordan/haha/bank.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("SELECT balance FROM users WHERE account_number = :acct");
    $stmt->execute([':acct' => $account_number]);
    $balance = $stmt->fetchColumn();
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
    exit;
}

if (!isset($_SESSION['user'])) {
    die("⛔ You must be logged in.");
}

$user = $_SESSION['user'];
$user_id = $user['id'];

try {
    $pdo = new PDO("sqlite:/home/jordan/haha/bank.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch alerts
    $stmt = $pdo->prepare("SELECT message, created_at FROM alerts WHERE user_id = ? AND type = 'credit' ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html><html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #1e1e1e;
      color: #fff;
    }
    .container {
      padding: 20px;
    }
    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .profile {
      display: flex;
      align-items: center;
    }
    .profile img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 10px;
    }
    .premium {
      background-color: #444;
      padding: 2px 8px;
      border-radius: 8px;
      font-size: 12px;
      margin-top: 4px;
    }
    .balance-box {
      background: #2b2b2b;
      border: 1px solid #f2c94c;
      border-radius: 12px;
      padding: 20px;
      margin-top: 20px;
      position: relative;
    }
    .balance-box h1 {
      font-size: 36px;
      color: #f2c94c;
      margin: 0;
    }
    .balance-actions {
      margin-top: 20px;
    }
    .balance-actions button {
      padding: 10px 20px;
      border: none;
      border-radius: 20px;
      margin-right: 10px;
      font-weight: bold;
    }
    .top-up {
      background-color: #f2c94c;
      color: black;
    }
    .send-money {
      background-color: #555;
      color: white;
    }
    .expenses, .spending-box, .cashback-box {
      background: #2b2b2b;
      border-radius: 12px;
      padding: 20px;
      margin-top: 20px;
    }
    .expenses h3 {
      margin-top: 0;
    }
    .expense-item {
      display: flex;
      justify-content: space-between;
      margin: 8px 0;
    }
    .income {
      color: #f2c94c;
    }
    .outcome {
      color: #aaa;
    }
    .bottom-section {
      display: flex;
      gap: 20px;
    }
    .cashback-box {
      background: #f2c94c;
      color: black;
      flex: 1;
    }
    .spending-box {
      flex: 1;
    }
    .nav {
      position: fixed;
      bottom: 0;
      width: 100%;
      background-color: white;
      display: flex;
      justify-content: space-around;
      padding: 10px 0;
    }
    .nav-icon {
      width: 24px;
      height: 24px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
</div>
<div class="profile">
  <img src="uploads/<?= htmlspecialchars($user['profile_pic']) ?>" alt="user">
  <div>
    <div>Morning, <?= htmlspecialchars($user['name']) ?>!</div>
    <div class="premium">Premium</div>
  </div>
</div>

<!-- Alert Popup HTML -->
<div id="alertPopup" style="
  display: none;
  position: fixed;
  bottom: 20px;
  right: 20px;
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
  padding: 15px 20px;
  font-weight: bold;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  z-index: 9999;
">
  <span id="alertMessage"></span>
</div>

<!-- Alert Sound -->
<audio id="alertSound" src="uploads/CATACLYSM PR FUNK.mp3" preload="auto"></audio>

<!-- Alert Script -->
<script>
let lastAlertId = null;

function showAlert(message) {
  const popup = document.getElementById('alertPopup');
  const msg = document.getElementById('alertMessage');
  const sound = document.getElementById('alertSound');

  msg.textContent = message;
  popup.style.display = 'block';
  sound.play();

  setTimeout(() => {
    popup.style.display = 'none';
  }, 10000); // hide after 10 seconds
}

// Initial fetch to skip old alerts
fetch('alerts.php')
  .then(res => res.json())
  .then(data => {
    if (data && data.id) {
      lastAlertId = data.id;
    }
  });

// Poll for new alerts every 5 seconds
setInterval(() => {
  fetch('alerts.php')
    .then(res => res.json())
    .then(data => {
      if (data && data.id && data.id !== lastAlertId) {
        lastAlertId = data.id;
        showAlert(data.message);
      }
    })
    .catch(err => console.error('Alert fetch error:', err));
}, 5000);
</script>

<div class="balance-box">
  <div>Active Balance</div>




<h1>$<?= number_format($balance, 2) ?></h1>


  <div class="balance-actions">
    <button class="top-up">Top Up</button>
    <!-- HTML Button -->
<button class="send-money" onclick="loadAndRedirect()">
  <img src="loader.gif" id="loader" style="display: none; width: 16px; height: 16px; margin-right: 8px;">
  <span id="btn-text">Send Money</span>
</button>

<!-- JavaScript -->
<script>
  function loadAndRedirect() {
    document.getElementById("loader").style.display = "inline-block";
    document.getElementById("btn-text").textContent = "Sending...";

    // ✅ Replace 'send-money.html' with your desired web page
    setTimeout(function () {
      window.location.href = 'transferz.php';
    }, 3000); // Wait 3 seconds before redirecting
  }
</script>


  </div>
</div>

<div class="expenses">
  <h3>Expenses</h3>
  <div class="expense-item"><span>Salary <span class="income">Income</span></span><span>$4,000.00</span></div>
  <div class="expense-item"><span>Stock Dividends <span class="income">Income</span></span><span>$1,000.00</span></div>
  <div class="expense-item"><span>App Subscriptions <span class="outcome">Outcome</span></span><span>$300.00</span></div>
  <div class="expense-item"><span>Food & Dining <span class="outcome">Outcome</span></span><span>$1,500.00</span></div>
</div>

<div class="bottom-section">
  <div class="spending-box">
    <div>Spendings</div>
    <h2>$2,890.00</h2>
    <small>Spent this month</small>
  </div>
  <div class="cashback-box">
    <div>Cashback</div>
    <h2>$1,067.00</h2>
    <small>Get this month</small>
  </div>
</div>

  </div>
<div class="nav">
  <a href="dashboard.php"><img src="https://img.icons8.com/ios-filled/50/home.png" class="nav-icon" alt="Home"></a>
  <a href="history.php"><img src="https://img.icons8.com/ios-filled/50/wallet.png" class="nav-icon" alt="Wallet"></a>
  <a href="transferz.php"><img src="https://img.icons8.com/ios-filled/50/swap.png" class="nav-icon" alt="Transfer"></a>
  <a href="dashboard.php"><img src="https://img.icons8.com/ios-filled/50/user.png" class="nav-icon" alt="Home"></a>
</div>
  <iframe src="notification_popup.php" style="display:none;" id="notifyFrame"></iframe>
</body>
</html>
