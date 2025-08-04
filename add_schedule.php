<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit;
}

$stmt = $pdo->query("SELECT id, name FROM users ORDER BY id");
$users = $stmt->fetchAll();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $date = $_POST['work_date'];
    $start = $_POST['planned_start'];
    $end = $_POST['planned_end'];

    $stmt = $pdo->prepare("REPLACE INTO work_schedule (user_id, work_date, planned_start, planned_end) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $date, $start, $end])) {
        $message = "✅ 勤務予定を登録しました！";
    } else {
        $message = "⚠️ 登録に失敗しました。";
    }
}
?>

<h2>勤務予定の登録</h2>
<link rel="stylesheet" href="../css/style.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<form method="post">
    <label>ユーザー：
        <select name="user_id">
            <?php foreach ($users as $user): ?>
                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <label>日付：<input type="date" name="work_date" required></label><br><br>
    <label>予定出勤：<input type="time" name="planned_start" required></label><br><br>
    <label>予定退勤：<input type="time" name="planned_end" required></label><br><br>

    <button type="submit">登録する</button>
</form>

<p><?= $message ?></p>
<p><a href="index.php">← 管理メニューへ戻る</a></p>
