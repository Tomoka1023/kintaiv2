<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ユーザー仮登録</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
</head>
<body>
<h2>ユーザー登録</h2>
    <form action="send_mail.php" method="post">
        
        <label for="name">名前</label>
        <input type="text" name="name" id="name" required>

        <label for="email">メールアドレス</label>
        <input type="email" name="email" id="email" required>

        <label for="password">パスワード</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">仮登録</button>

    </form>
    <p><a href="login.php">ログインはこちら</a></p>
</body>
</html>
