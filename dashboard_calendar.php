<?php
session_start();
require 'db.php';
date_default_timezone_set('Asia/Tokyo');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$ym = isset($_GET['ym']) ? $_GET['ym'] : date('Y-m');

// 不正な形式を防ぐ
if (!preg_match('/^\d{4}-\d{2}$/', $ym)) {
    $ym = date('Y-m');
}

$firstDay = "$ym-01";
$lastDay = date("Y-m-t", strtotime($firstDay));

// 出勤予定を取得
$schedule_stmt = $pdo->prepare("
    SELECT work_date, planned_start, planned_end 
    FROM work_schedule 
    WHERE user_id = ? AND work_date BETWEEN ? AND ?
");
$schedule_stmt->execute([$user_id, $firstDay, $lastDay]);

$schedules = [];
foreach ($schedule_stmt->fetchAll() as $row) {
    $schedules[$row['work_date']] = [
        'start' => $row['planned_start'],
        'end' => $row['planned_end']
    ];
}

// $user_id = $_SESSION['user_id'];
// $yearMonth = date('Y-m');
// $firstDay = "$yearMonth-01";
// $lastDay = date("Y-m-t", strtotime($firstDay));

// 勤怠データ取得
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND work_date BETWEEN ? AND ? ORDER BY work_date");
$stmt->execute([$user_id, $firstDay, $lastDay]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 日付ごとに整理
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
    <title>勤怠カレンダー</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <!-- <link rel="stylesheet" href="css/style.css"> -->
    <style>
        body {
            font-family: sans-serif;
        }
        h2 {
            color: #0c9;
        }
        p, a {
            color: #9c9;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #888;
            width: 14.2%;
            height: 100px;
            vertical-align: top;
            padding: 5px;
            font-size: 14px;
        }
        th {
            background-color:rgb(220, 234, 224);
        }
        .today {
            background-color: #fffcdd;
        }
        .work-info {
            margin-top: 5px;
            font-size: 16px;
            color: #333;
        }
        .planned {
            background-color: #e6f2ff;
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
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            font-size: 14px;
        }

        .day-card strong {
            display: block;
            margin-bottom: 6px;
            font-size: 16px;
            color: #9c9;
        }

        .day-card .work-info {
            margin-top: 4px;
            line-height: 1.4;
            color: #333;
        }

        .day-card.planned {
            background-color: #e6f2ff;
        }

        .day-card.today {
            border-left: 5px solid #ffcc00;
        }
    }

    </style>
</head>
<body>
    <h2><?= $_SESSION['user_name'] ?>さんの勤怠カレンダー（<?= date('Y年n月', strtotime($firstDay)) ?>）</h2>

    <form method="get" style="margin-bottom: 20px;">
        <label for="ym">表示する月：</label>
        <select name="ym" id="ym" onchange="this.form.submit()">
            <?php
            $thisMonth = date('Y-m');
            for ($i = 0; $i < 12; $i++) {
                $option = date('Y-m', strtotime("-{$i} month"));
                $selected = ($option === $ym) ? 'selected' : '';
                echo "<option value='$option' $selected>" . date('Y年n月', strtotime($option)) . "</option>";
            }
            ?>
        </select>
    </form>

    <table>
        <tr>
            <th>日</th><th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th>土</th>
        </tr>

        <?php
        $startWeekday = date('w', strtotime($firstDay));
        $daysInMonth = date('t', strtotime($firstDay));
        $day = 1;
        $cell = 0;

        echo '<tr>';
        for ($i = 0; $i < $startWeekday; $i++, $cell++) {
            echo '<td></td>';
        }

        while ($day <= $daysInMonth) {
            $currentDate = sprintf('%s-%02d', $ym, $day);
            $class = [];
        
            if ($currentDate === date('Y-m-d')) {
                $class[] = 'today';
            }
            if (isset($schedules[$currentDate])) {
                $class[] = 'planned';
            }
        
            $class_attr = empty($class) ? '' : 'class="' . implode(' ', $class) . '"';
        
            echo "<td $class_attr><strong>$day</strong><br>";
        
            if (isset($attendance[$currentDate])) {
                echo "<div class='work-info'>出：{$attendance[$currentDate]['start']}<br>退：{$attendance[$currentDate]['end']}<br>{$attendance[$currentDate]['work']}</div>";
            }
        
            echo "</td>";
            $day++;
            $cell++;
        
            if ($cell % 7 === 0 && $day <= $daysInMonth) {
                echo '</tr><tr>';
            }
        }
        
        echo '</tr>';

        ?>
    </table>

    <!-- スマホ用の縦型カード表示 -->
<?php if ($daysInMonth): ?>
    <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
        <?php
        $currentDate = sprintf('%s-%02d', $ym, $d);
        $isToday = ($currentDate === date('Y-m-d'));
        $isPlanned = isset($schedules[$currentDate]);

        $classes = [];
        if ($isToday) $classes[] = 'today';
        if ($isPlanned) $classes[] = 'planned';
        $class_attr = $classes ? 'day-card ' . implode(' ', $classes) : 'day-card';

        $planned = $isPlanned ? "{$schedules[$currentDate]['start']}〜{$schedules[$currentDate]['end']}" : '-';
        $att = $attendance[$currentDate] ?? ['start' => '-', 'end' => '-', 'work' => '-'];
        ?>
        <div class="<?= $class_attr ?>">
            <strong><?= $currentDate ?></strong>
            <div class="work-info">
                <p>予定：<?= $planned ?></p>
                <p>出勤：<?= $att['start'] ?></p>
                <p>退勤：<?= $att['end'] ?></p>
                <p>勤務時間：<?= $att['work'] ?></p>
            </div>
        </div>
    <?php endfor; ?>
<?php endif; ?>

    <p>✅ 今月の総勤務時間：<?= floor($totalSeconds / 3600) ?>時間 <?= floor(($totalSeconds % 3600) / 60) ?>分</p>
    <p><a href="index.php">← ホームへ戻る</a></p>
</body>
</html>
