<?php
if (!isset($_GET['id'])) {
    die("❌ No transaction ID provided.");
}

$transaction_id = intval($_GET['id']);

try {
    $pdo = new PDO("sqlite:/home/jordan/haha/bank.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the transaction
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE transaction_id = ?");
    $stmt->execute([$transaction_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        die("❌ Transaction not found.");
    }

    // Get sender name
    $stmt = $pdo->prepare("SELECT name FROM users WHERE account_number = ?");
    $stmt->execute([$transaction['sender_account']]);
    $sender = $stmt->fetchColumn() ?: 'Unknown';

    // Get receiver name
    $stmt = $pdo->prepare("SELECT name FROM users WHERE account_number = ?");
    $stmt->execute([$transaction['receiver_account']]);
    $receiver = $stmt->fetchColumn() ?: 'Unknown';

} catch (Exception $e) {
    die("❌ Database error: " . $e->getMessage());
}
?>

<?php
echo '<div style="
    background-color: #d4edda;
    color: #155724;
    padding: 15px;
    border: 1px solid #c3e6cb;
    border-radius: 5px;
    margin: 20px auto;
    width: 90%;
    text-align: center;
    font-weight: bold;
">
    ✅ Transaction Successful!
</div>';
?>
<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">
    <title>Transaction Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 30px;
        }
        .receipt-box {
            background: #fff;
            padding: 25px;
            margin: auto;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #28a745;
        }
        .icon {
            text-align: center;
            margin-bottom: 20px;
        }
        .icon img {
            width: 60px;
        }
        .detail {
            line-height: 1.8;
            margin-top: 10px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 160px;
        }
    </style>
</head>
<body>
<script>
       windows.onload = funtion() {
           alert("✔ Transaction Successful");
                                  };
    </script>
<div class="receipt-box">
    <div class="icon">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/50/Yes_Check_Circle.svg/1024px-Yes_Check_Circle.svg.png" alt="Success">
    </div>
    <h2>Transaction Successful</h2>
    <div class="detail">
        <div><span class="label">Transaction ID:</span> <?= htmlspecialchars($transaction['transaction_id']) ?></div>
        <div><span class="label">Sender Account:</span> <?= htmlspecialchars($transaction['sender_account']) ?> (<?= htmlspecialchars($sender) ?>)</div>
        <div><span class="label">Receiver Account:</span> <?= htmlspecialchars($transaction['receiver_account']) ?> (<?= htmlspecialchars($receiver) ?>)</div>
        <div><span class="label">Amount:</span> $<?= number_format($transaction['amount'], 2) ?></div>
        <div><span class="label">Date:</span> <?= htmlspecialchars($transaction['transaction_date']) ?></div>
    </div>
</div>
<div style="text-align:center; margin-top: 30px;">
    <a href="dashboard.php" style="
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        display: inline-block;
    ">Back to Dashboard</a>
</div>
</body>
</html>
