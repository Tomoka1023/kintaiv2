<?php
date_default_timezone_set('Asia/Tokyo');
require 'db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // トークンでユーザーを検索
    $stmt = $pdo->prepare("SELECT * FROM users WHERE token = ? AND is_verified = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // 認証完了処理
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, token = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        echo "✅ 本登録が完了しました！ログインできます。";
    } else {
        echo "⚠️ 無効なトークン、またはすでに認証済みです。";
    }
} else {
    echo "⚠️ トークンが見つかりません。";
}
?>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">