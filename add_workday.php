<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit;
}

// 全ユーザー取得（プルダウン用）
$stmt = $pdo->query("SELECT id, name FROM users ORDER BY id");
$users = $stmt->fetchAll();

// 登録処理
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $date = $_POST['work_date'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];

    $stmt = $pdo->prepare("REPLACE INTO attendance (user_id, work_date, start_time, end_time) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $date, $start, $end])) {
        $message = "✅ 出勤データを登録しました！";
    } else {
        $message = "⚠️ 登録に失敗しました。";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>出勤日手動登録</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>出勤日の手動登録</h2>
    <form method="post">
        <label>ユーザー：
            <select name="user_id" required>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <label>日付：<input type="date" name="work_date" required></label><br><br>
        <label>出勤時間：<input type="time" name="start_time" required></label><br><br>
        <label>退勤時間：<input type="time" name="end_time" required></label><br><br>

        <button type="submit">登録する</button>
    </form>

    <p><?= $message ?></p>
    <p><a href="index.php">← 管理メニューに戻る</a></p>
</body>
</html>
