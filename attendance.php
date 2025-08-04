<?php
session_start();
date_default_timezone_set('Asia/Tokyo');
require 'db.php';

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$now = date('Y-m-d H:i:s');

// ボタンで送られてくるactionを確認
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'start') {
        // 出勤処理：まだその日の出勤記録がないときのみ記録
        $stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND work_date = ?");
        $stmt->execute([$user_id, $today]);
        $existing = $stmt->fetch();

        if (!$existing) {
            $stmt = $pdo->prepare("INSERT INTO attendance (user_id, work_date, start_time) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $today, $now]);
        }

    } elseif ($action === 'end') {
        // 退勤処理：その日の出勤記録があって、end_timeがまだ空なら記録
        $stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND work_date = ?");
        $stmt->execute([$user_id, $today]);
        $existing = $stmt->fetch();

        if ($existing && !$existing['end_time']) {
            $stmt = $pdo->prepare("UPDATE attendance SET end_time = ? WHERE id = ?");
            $stmt->execute([$now, $existing['id']]);
        }
    }
}

// 打刻後はホームに戻る
header("Location: index.php");
exit;
?>