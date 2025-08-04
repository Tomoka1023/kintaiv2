<?php
session_start();
require '../db.php';
date_default_timezone_set('Asia/Tokyo');

// 管理者チェック
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit;
}

// 対象ユーザー取得
if (!isset($_GET['user_id'])) {
    echo "ユーザーが指定されていません。";
    exit;
}
$user_id = $_GET['user_id'];

// ユーザー情報取得
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) {
    echo "指定されたユーザーは存在しません。";
    exit;
}

// 月切り替え
$ym = isset($_GET['ym']) ? $_GET['ym'] : date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $ym)) {
    $ym = date('Y-m');
}
$firstDay = "$ym-01";
$lastDay = date("Y-m-t", strtotime($firstDay));

// 勤怠データ取得
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND work_date BETWEEN ? AND ? ORDER BY work_date");
$stmt->execute([$user_id, $firstDay, $lastDay]);
$data = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT work_date, planned_start, planned_end 
    FROM work_schedule 
    WHERE user_id = ? AND work_date BETWEEN ? AND ?
");
$stmt->execute([$user_id, $firstDay, $lastDay]);
$schedules = [];
foreach ($stmt->fetchAll() as $row) {
    $schedules[$row['work_date']] = [
        'start' => $row['planned_start'],
        'end' => $row['planned_end']
    ];
}

// 日別の集計
$attendance = [];
$totalSeconds = 0;

foreach ($data as $row) {
    $workDate = $row['work_date'];
    $start = $row['start_time'] ? date('H:i', strtotime($row['start_time'])) : '-';
    $end = $row['end_time'] ? date('H:i', strtotime($row['end_time'])) : '-';

    $work = '-';
    if ($row['start_time'] && $row['end_time']) {
        $start_dt = new DateTime($row['start_time']);
        $end_dt = new DateTime($row['end_time']);
        $interval = $start_dt->diff($end_dt);
        $hours = $interval->h;
        $minutes = $interval->i;
        $work = sprintf('%d:%02d', $hours, $minutes);
        $totalSeconds += $hours * 3600 + $minutes * 60;
    }

    $attendance[$workDate] = [
        'start' => $start,
        'end' => $end,
        'work' => $work
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user['name']) ?>さんの勤怠</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <style>
    body {
        font-family: 'Helvetica Neue', sans-serif;
        background-color: #f8f9fa;
        padding: 30px;
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #0c9;
    }

    p {
        text-align: center;
        color: #555;
    }

    form {
        text-align: center;
        margin-bottom: 20px;
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

    tr:hover {
        background-color: #e6f7ff;
    }

    a {
        color: #9c9;
        text-decoration: none;
        font-weight: bold;
    }

    a:hover {
        text-decoration: underline;
    }

    select {
        padding: 5px;
        font-size: 14px;
    }

    .summary {
        text-align: center;
        margin-top: 20px;
        font-weight: bold;
        color: #333;
    }
    .day-card {
        display: none;
    }
    @media screen and (max-width: 600px) {
        table {
            display: none;
        }

        .day-card {
            display: block;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 15px;
            padding: 15px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            font-size: 14px;
        }

        .day-card strong {
            display: block;
            margin-bottom: 5px;
            font-size: 16px;
            color: #9c9;
        }

        .day-card p {
            margin: 4px 0;
            color: #333;
            text-align: left;
        }

        .day-card a {
            display: inline-block;
            margin-top: 8px;
            color: #9c9;
            font-weight: bold;
            text-decoration: none;
        }

        .day-card a:hover {
            text-decoration: underline;
        }
    }
</style>

</head>
<body>
    <h2><?= htmlspecialchars($user['name']) ?>さんの勤怠（<?= date('Y年n月', strtotime($firstDay)) ?>）</h2>
    <p>メール：<?= htmlspecialchars($user['email']) ?></p>

    <form method="get" style="margin-bottom: 20px;">
        <input type="hidden" name="user_id" value="<?= $user_id ?>">
        <label>表示月：
            <select name="ym" onchange="this.form.submit()">
                <?php
                for ($i = 0; $i < 12; $i++) {
                    $option = date('Y-m', strtotime("-{$i} month"));
                    $selected = ($option === $ym) ? 'selected' : '';
                    echo "<option value='$option' $selected>" . date('Y年n月', strtotime($option)) . "</option>";
                }
                ?>
            </select>
        </label>
    </form>

    <table border="1" cellpadding="8">
        <tr>
            <th>日付</th><th>予定</th><th>出勤</th><th>退勤</th><th>勤務時間</th><th>操作</th>
        </tr>
        <?php
        $daysInMonth = date('t', strtotime($firstDay));
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = sprintf('%s-%02d', $ym, $d);
            $planned = isset($schedules[$date])
            ? "{$schedules[$date]['start']}〜{$schedules[$date]['end']}"
            : '-';
            $att = $attendance[$date] ?? ['start' => '-', 'end' => '-', 'work' => '-'];
            echo "<tr>
                <td>{$date}</td>
                <td>{$planned}</td>
                <td>{$att['start']}</td>
                <td>{$att['end']}</td>
                <td>{$att['work']}</td>
                <td><a href='edit_attendance.php?user_id={$user_id}&date={$date}'>編集</a></td>
            </tr>";
        }
        ?>
    </table>

    <!-- モバイル用表示 -->
<?php if ($daysInMonth): ?>
    <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
        <?php
        $date = sprintf('%s-%02d', $ym, $d);
        $planned = isset($schedules[$date])
            ? "{$schedules[$date]['start']}〜{$schedules[$date]['end']}"
            : '-';
        $att = $attendance[$date] ?? ['start' => '-', 'end' => '-', 'work' => '-'];
        ?>
        <div class="day-card">
            <strong><?= $date ?></strong>
            <p>予定：<?= $planned ?></p>
            <p>出勤：<?= $att['start'] ?></p>
            <p>退勤：<?= $att['end'] ?></p>
            <p>勤務時間：<?= $att['work'] ?></p>
            <a href='edit_attendance.php?user_id=<?= $user_id ?>&date=<?= $date ?>'>編集する →</a>
        </div>
    <?php endfor; ?>
<?php endif; ?>

    <p>✅ 総勤務時間：<?= floor($totalSeconds / 3600) ?>時間 <?= floor(($totalSeconds % 3600) / 60) ?>分</p>
    <p><a href="user_list.php">← ユーザー一覧に戻る</a></p>
</body>
</html>
