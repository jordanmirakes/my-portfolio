<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_account = $_SESSION['user']['account_number'];
    $sender_id = $_SESSION['user']['id'];
    $receiver_account = trim($_POST['receiver_account']);
    $amount = floatval($_POST['amount']);
    $pin = trim($_POST['pin']);

    try {
        $pdo = new PDO("sqlite:/home/jordan/haha/bank.db");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check sender's account status
        $stmt = $pdo->prepare("SELECT status, pin_hash, balance FROM users WHERE account_number = ?");
        $stmt->execute([$sender_account]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = "❌ Sender not found.";
            header("Location: transfer.html");
            exit();
        }

        if (in_array($user['status'], ['suspended', 'blocked'])) {
            $_SESSION['error'] = "❌ Your account is {$user['status']}. Transfers are not allowed.";
            header("Location: transferz.php");
            exit();
        }

        // Verify PIN
        if (!password_verify($pin, $user['pin_hash'])) {
            $_SESSION['error'] = "❌ Invalid PIN. Please try again.";
            header("Location: transfer.html");
            exit();
        }

        if ($user['balance'] < $amount) {
            $_SESSION['error'] = "❌ Insufficient balance.";
            header("Location: transfer.php");
            exit();
        }

        // Get receiver details
        $stmt = $pdo->prepare("SELECT id FROM users WHERE account_number = ?");
        $stmt->execute([$receiver_account]);
        $receiver = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$receiver) {
            $_SESSION['error'] = "❌ Receiver account not found.";
            header("Location: transfer.php");
            exit();
        }

        $receiver_id = $receiver['id'];

        // Begin transaction
        $pdo->beginTransaction();

        // Deduct from sender
        $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE account_number = ?");
        $stmt->execute([$amount, $sender_account]);

        // Credit receiver
        $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE account_number = ?");
        $stmt->execute([$amount, $receiver_account]);

        // Log transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (sender_account, receiver_account, amount) VALUES (?, ?, ?)");
        $stmt->execute([$sender_account, $receiver_account, $amount]);
        $transaction_id = $pdo->lastInsertId();

        // Alert receiver
        $message = "You received $$amount from account $sender_account.";
        $stmt = $pdo->prepare("INSERT INTO alerts (user_id, message, is_read) VALUES (?, ?, 0)");
        $stmt->execute([$receiver_id, $message]);

        $pdo->commit();

        header("Location: receipt.php?id=" . $transaction_id);
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = "❌ Transaction failed: " . $e->getMessage();
        header("Location: transfer.html");
        exit();
    }
} else {
    $_SESSION['error'] = "❌ Invalid request method.";
    header("Location: transfer.php");
    exit();
}
?>
<?php
session_start();
$status = $_SESSION['status'] ?? '';
$status_type = $_SESSION['status_type'] ?? ''; // success or error
unset($_SESSION['status'], $_SESSION['status_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Transfer Funds</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f1f1f1;
      padding: 30px;
    }

    .transfer-form {
      max-width: 400px;
      margin: auto;
      background: white;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .transfer-form h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    input, button {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
    }

    button {
      background-color: #28a745;
      color: white;
      font-size: 16px;
      cursor: pointer;
    }

    button:disabled {
      background-color: #aaa;
      cursor: not-allowed;
    }

    .loader {
      display: none;
      text-align: center;
      margin-top: 10px;
    }

    .spinner {
      border: 4px solid #f3f3f3;
      border-top: 4px solid #28a745;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin: auto;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .nav {
      position: fixed;
      bottom: 0;
      width: 100%;
      background-color: #121212;
      display: flex;
      justify-content: space-around;
      padding: 10px 0;
    }

    .nav-icon {
      width: 24px;
      height: 24px;
    }

    .status-message {
      max-width: 400px;
      margin: 10px auto;
      padding: 10px;
      border-radius: 6px;
      text-align: center;
      font-weight: bold;
    }

    .status-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .status-error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
  </style>
</head>
<body>

  <?php if (!empty($status)): ?>
    <div class="status-message <?php echo $status_type === 'success' ? 'status-success' : 'status-error'; ?>">
      <?php echo htmlspecialchars($status); ?>
    </div>
  <?php endif; ?>

  <form class="transfer-form" id="transferForm" action="transfer.php" method="post">
    <h2>Transfer Funds</h2>

    <label for="receiver_account">Receiver Account:</label>
    <input type="text" name="receiver_account" id="receiver_account" required>

    <label for="amount">Amount ($):</label>
    <input type="number" step="0.01" name="amount" id="amount" required>

    <label for="pin">Transaction PIN:</label>
    <input type="password" name="pin" id="pin" required>

    <button type="submit" id="submitBtn">Transfer</button>

    <div class="loader" id="loader">
      <div class="spinner"></div>
      <p>Processing transaction...</p>
    </div>
  </form>

  <script>
    const form = document.getElementById("transferForm");
    const loader = document.getElementById("loader");
    const submitBtn = document.getElementById("submitBtn");

    form.addEventListener("submit", function(e) {
      e.preventDefault();
      loader.style.display = "block";
      submitBtn.disabled = true;
      setTimeout(() => {
        form.submit();
      }, 3000);
    });
  </script>

  <div class="nav">
    <a href="dashboard.php"><img src="https://img.icons8.com/ios-filled/50/home.png" class="nav-icon" alt="Home"></a>
    <a href="history.php"><img src="https://img.icons8.com/ios-filled/50/wallet.png" class="nav-icon" alt="Wallet"></a>
    <a href="transfer.php"><img src="https://img.icons8.com/ios-filled/50/swap.png" class="nav-icon" alt="Transfer"></a>
    <a href="dashboard.php"><img src="https://img.icons8.com/ios-filled/50/user.png" class="nav-icon" alt="Profile"></a>
  </div>

</body>
</html>
