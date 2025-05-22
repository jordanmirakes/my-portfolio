<?php
session_start();
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
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

    .alert {
      background: #f8d7da;
      color: #721c24;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #f5c6cb;
      border-radius: 6px;
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

    #accountNameDisplay {
      color: green;
      font-weight: bold;
      margin-top: -10px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

  <?php if (!empty($error)): ?>
    <div class="alert">
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <form class="transfer-form" id="transferForm" action="transfer.php" method="post">
    <h2>Transfer Funds</h2>

    <label for="receiver_account">Receiver Account:</label>
    <input type="text" name="receiver_account" id="receiver_account" required onblur="getAccountName()">

    <p id="accountNameDisplay"></p>

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

    function getAccountName() {
      const acc = document.getElementById("receiver_account").value.trim();
      const display = document.getElementById("accountNameDisplay");

      if (acc.length < 6) {
        display.innerText = "";
        return;
      }

      fetch("get_account_name.php?account=" + encodeURIComponent(acc))
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            display.innerText = "Account Name: " + data.name;
          } else {
            display.innerText = "Account not found";
          }
        })
        .catch(err => {
          display.innerText = "Error fetching account name";
        });
    }
  </script>

  <div class="nav">
    <a href="dashboard.php"><img src="https://img.icons8.com/ios-filled/50/home.png" class="nav-icon" alt="Home"></a>
    <a href="history.php"><img src="https://img.icons8.com/ios-filled/50/wallet.png" class="nav-icon" alt="Wallet"></a>
    <a href="transfer.php"><img src="https://img.icons8.com/ios-filled/50/swap.png" class="nav-icon" alt="Transfer"></a>
    <a href="dashboard.php"><img src="https://img.icons8.com/ios-filled/50/user.png" class="nav-icon" alt="Profile"></a>
  </div>

</body>
</html>
