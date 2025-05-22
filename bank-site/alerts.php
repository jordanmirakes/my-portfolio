<?php
session_start();
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "alert" => null]);
    exit;
}

$user_id = $_SESSION['user_id'] ?? $_SESSION['user']['id'];

// Connect to SQLite
try {
    $pdo = new PDO("sqlite:/home/jordan/haha/bank.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
    exit;
}

// Get user's name
$nameStmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$nameStmt->execute([$user_id]);
$name = $nameStmt->fetchColumn();

// Get latest unread alert
$stmt = $pdo->prepare("SELECT id, message FROM alerts WHERE user_id = ? AND is_read = 0 ORDER BY id DESC LIMIT 1");
$stmt->execute([$user_id]);
$alert = $stmt->fetch(PDO::FETCH_ASSOC);

// Mark as read and include name
if ($alert) {
    $mark = $pdo->prepare("UPDATE alerts SET is_read = 1 WHERE id = ?");
    $mark->execute([$alert['id']]);

    $alert['message'] = "$name, " . $alert['message'];
    echo json_encode($alert);
    exit;
}

echo json_encode(null);
