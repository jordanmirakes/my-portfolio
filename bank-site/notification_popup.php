<?php
session_start();
if (!isset($_SESSION['user'])) exit;

$user = $_SESSION['user'];
$account_number = $user['account_number'];

try {
    $pdo = new PDO("sqlite:/home/jordan/haha/bank.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get most recent transaction
    $stmt = $pdo->prepare("SELECT * FROM transactions 
                           WHERE sender_account = :acct OR receiver_account = :acct 
                           ORDER BY transaction_date DESC LIMIT 1");
    $stmt->execute([':acct' => $account_number]);
    $tx = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tx) exit;

    $type = $tx['sender_account'] === $account_number ? 'debit' : 'credit';
    $other_party = $type === 'debit' ? $tx['receiver_account'] : $tx['sender_account'];
    $amount = number_format($tx['amount'], 2);
    $date = $tx['transaction_date'];

    // Prevent duplicate alerts in same session
    $last_id = $_SESSION['last_txn_id'] ?? null;
    if ($last_id === $tx['transaction_id']) exit;
    $_SESSION['last_txn_id'] = $tx['transaction_id'];

} catch (PDOException $e) {
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            margin: 0;
            background: transparent;
        }
        #popup {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: <?= $type === 'credit' ? '#d9fff2' : '#fff0d9' ?>;
            border-left: 6px solid <?= $type === 'credit' ? '#00cc88' : '#ffa500' ?>;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
            font-family: sans-serif;
            z-index: 9999;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(20px);}
            to {opacity: 1; transform: translateY(0);}
        }
    </style>
</head>
<body>
    <div id="popup">
        <strong>🔔 <?= ucfirst($type) ?> Alert</strong><br>
        ₦<?= $amount ?> <?= $type === 'credit' ? 'received from' : 'sent to' ?> <?= htmlspecialchars($other_party) ?><br>
        <small><?= $date ?></small>
    </div>
    <audio autoplay>
        <source src="<?= $type === 'credit' ? 'credit.mp3' : 'debit.mp3' ?>" type="audio/mpeg">
    </audio>
</body>
</html>

