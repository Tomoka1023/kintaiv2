<?php
session_start();
date_default_timezone_set('Asia/Tokyo');
require '../db.php';

// 管理者チェック
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>管理者ダッシュボード</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>管理者メニュー</h1>
        <a href="user_list.php">ユーザー一覧</a>
        <a href="add_schedule.php">勤務予定の登録</a>
        <a href="add_workday.php">出勤日手動登録</a>
        <a href="../logout.php">ログアウト</a>
</body>
</html>
