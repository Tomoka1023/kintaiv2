<?php
session_start();
require '../db.php';
date_default_timezone_set('Asia/Tokyo');

// 管理者チェック
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_GET['user_id'];
$date = $_GET['date'];

// ユーザー名を取得
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_name = $stmt->fetchColumn();

// 勤怠データ取得
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND work_date = ?");
$stmt->execute([$user_id, $date]);
$data = $stmt->fetch();

if (isset($_POST['delete'])) {
    $delete = $pdo->prepare("DELETE FROM attendance WHERE user_id = ? AND work_date = ?");
    $delete->execute([$user_id, $date]);

    header("Location: user_detail.php?user_id=$user_id");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $update = $pdo->prepare("UPDATE attendance SET start_time = ?, end_time = ? WHERE user_id = ? AND work_date = ?");
    $update->execute([$start_time, $end_time, $user_id, $date]);

    header("Location: user_detail.php?user_id=$user_id");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>勤怠編集</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2><?= htmlspecialchars($user_name) ?>さんの <?= $date ?> の勤怠を編集</h2>
    <form method="post">
        <label>出勤時間:</label>
        <input type="time" name="start_time" value="<?= substr($data['start_time'], 0, 5) ?>"><br><br>
        <label>退勤時間:</label>
        <input type="time" name="end_time" value="<?= substr($data['end_time'], 0, 5) ?>"><br><br>
        <button type="submit">保存</button>
        <button type="submit" name="delete" onclick="return confirm('この勤怠記録を削除しますか？')">削除</button>
        <a href="user_detail.php?user_id=<?= $user_id ?>">キャンセル</a>
    </form>
</body>
</html>
