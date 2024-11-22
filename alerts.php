<?php
require 'config.php';

$city = isset($_GET['city']) ? $_GET['city'] : '東京';
$searchUrl = BASE_URL . "weather?q=" . urlencode($city) . "&appid=" . API_KEY;
$weatherData = json_decode(@file_get_contents($searchUrl), true);

if (isset($weatherData['coord'])) {
    $lat = $weatherData['coord']['lat'];
    $lon = $weatherData['coord']['lon'];

    $alertsUrl = BASE_URL . "onecall?lat=$lat&lon=$lon&appid=" . API_KEY . "&lang=ja";
    $alertsData = json_decode(@file_get_contents($alertsUrl), true);

    $alerts = isset($alertsData['alerts']) ? $alertsData['alerts'] : [];
} else {
    $alerts = [];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>天気警報</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            background-color: ivory; /* 背景色をアイボリーに設定 */
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0;
        }

        h1 {
            color: #ff6666; /* 赤色（警報に適した色） */
            text-align: center;
            margin-bottom: 20px;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
        }

        li strong {
            color: #d32f2f; /* 見出し部分をさらに強調 */
        }

        p {
            text-align: center;
        }

        button {
            background-color: #ff9999; /* ボタンも警報に合わせた赤色 */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #ff6666; /* ホバー時の赤色を濃く */
        }
    </style>
    <script src="js/scripts.js" defer></script>
</head>
<body>
    <h1>天気警報: <?php echo htmlspecialchars($city); ?></h1>

    <div id="alerts-data" data-alerts='<?php echo json_encode($alerts); ?>'></div>

    <?php if (!empty($alerts)): ?>
        <ul>
            <?php foreach ($alerts as $alert): ?>
                <li>
                    <strong><?php echo htmlspecialchars($alert['event']); ?></strong><br>
                    発信元: <?php echo htmlspecialchars($alert['sender_name']); ?><br>
                    詳細: <?php echo nl2br(htmlspecialchars($alert['description'])); ?><br>
                    開始: <?php echo date('Y-m-d H:i', $alert['start']); ?><br>
                    終了: <?php echo date('Y-m-d H:i', $alert['end']); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>現在、警報や注意報はありません。</p>
        <p><button onclick="history.back()">戻る</button></p>
    <?php endif; ?>
</body>
</html>
