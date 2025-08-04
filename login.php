<?php
session_start();
date_default_timezone_set('Asia/Tokyo');
require 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // 認証ユーザー検索（認証済み限定）
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_verified = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // ユーザー情報取得（ログイン成功時）
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['is_admin'] = $user['is_admin'];

    // 管理者なら管理画面へ、それ以外は一般画面へ
    if ($user['is_admin']) {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit;

}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
</head>
<body>
    <h2>ログイン</h2>

    <?php if ($message): ?>
        <p style="color:red;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label>メールアドレス <input type="email" name="email" required></label><br>
        <label>パスワード <input type="password" name="password" required></label><br>
        <button type="submit">ログイン</button>
    </form>

    <p><a href="register.php">新規登録はこちら</a></p>
</body>
</html>
