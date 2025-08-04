<?php
session_start();
date_default_timezone_set('Asia/Tokyo');
require '../db.php';

// 管理者チェック
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit;
}

// 全ユーザー取得
$stmt = $pdo->query("SELECT id, name, email, is_admin FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ユーザー一覧（管理者）</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <!-- <link rel="stylesheet" href="../css/style.css"> -->
    <style>
    body {
        font-family: 'Helvetica Neue', sans-serif;
        background-color: #f8f9fa;
        padding: 30px;
        margin: 0;
    }

    h1 {
        text-align: center;
        margin-bottom: 20px;
        color: #0c9;
    }

    p {
        text-align: center;
        color: #555;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background-color: #fff;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
    }

    th, td {
        border: 1px solid #ccc;
        padding: 10px;
        text-align: center;
        font-size: 14px;
    }

    th {
        background-color: #9c9;
        color: white;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    a {
        color: #9c9;
        text-decoration: none;
        font-weight: bold;
    }

    a:hover {
        text-decoration: underline;
    }

    .user-card {
        display: none;
    }

    @media screen and (max-width: 600px) {
        table {
            display: none;
        }

        .user-card {
            display: block;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 15px;
            padding: 15px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }

        .user-card h3 {
            margin: 0 0 10px;
            font-size: 18px;
            color: #9c9;
        }

        .user-card p {
            margin: 5px 0;
            font-size: 14px;
            color: #333;
        }

        .user-card a {
            display: inline-block;
            margin-top: 10px;
            color: #9c9;
            font-weight: bold;
        }
    }
</style>
</head>
<body>
    <h1>ユーザー一覧</h1>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th><th>名前</th><th>メール</th><th>権限</th><th>詳細</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= (isset($user['is_admin']) && $user['is_admin']) ? '管理者' : '一般' ?></td>
                <td>
                    <a href="user_detail.php?user_id=<?= $user['id'] ?>">勤怠を見る</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php foreach ($users as $user): ?>
        <div class="user-card">
            <h3><?= htmlspecialchars($user['name']) ?>（ID: <?= $user['id'] ?>）</h3>
            <p><?= htmlspecialchars($user['email']) ?></p>
            <p>権限：<?= ($user['is_admin']) ? '管理者' : '一般' ?></p>
            <a href="user_detail.php?user_id=<?= $user['id'] ?>">勤怠を見る →</a>
        </div>
    <?php endforeach; ?>

    <p><a href="index.php">← 管理者メニューに戻る</a></p>
</body>
</html>
