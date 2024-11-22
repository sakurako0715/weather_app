<?php
require 'config.php';

// URLパラメータで都市名を取得
$city = isset($_GET['city']) ? $_GET['city'] : 'Tokyo';
$url = BASE_URL . "forecast?q=" . urlencode($city) . "&units=metric&lang=ja&cnt=7&appid=" . API_KEY;

// APIから天気予報データを取得
$response = @file_get_contents($url);
if ($response === false) {
    $error = "週間天気情報を取得できませんでした。";
} else {
    $data = json_decode($response, true);
    if (!isset($data['cod']) || $data['cod'] != "200") {
        $error = "エラー: " . $data['message'];
    } else {
        $forecast = $data['list'];
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>週間天気予報</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            background-color: ivory; 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0;
        }

        h1 {
            color: #81d4fa; /* タイトルを水色に変更 */
            text-align: center;
            margin-bottom: 20px;
        }

        .forecast-container {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            padding: 10px;
        }

        .forecast-item {
            text-align: center;
            width: 13%;
            font-size: 14px;
        }

        .forecast-item img {
            width: 50px; /* アイコンのサイズ */
            height: auto;
        }

        .forecast-item .temp {
            font-weight: bold;
            font-size: 16px;
        }

        .forecast-item .min-max {
            font-size: 14px;
        }

        .min-temp {
            color: blue; /* 最低気温を青文字 */
        }

        .max-temp {
            color: red; /* 最高気温を赤文字 */
        }

        .weather-description {
            font-size: 12px;
            color: #666;
        }

        .error {
            color: red;
        }

        button {
            background-color: #b3e5fc;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #81d4fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>週間天気予報</h1>
        <p>都市: <?php echo htmlspecialchars($city); ?></p>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php else: ?>
            <div class="forecast-container">
                <?php foreach ($forecast as $day): ?>
                    <div class="forecast-item">
                        <?php
                        // 月日だけ表示
                        $date = date('m月d日', $day['dt']);  // 月日だけ表示
                        $weather = $day['weather'][0]['description']; // 天気の詳細
                        $icon = $day['weather'][0]['icon']; // アイコンコード
                        $temp_min = $day['main']['temp_min']; // 最低気温
                        $temp_max = $day['main']['temp_max']; // 最高気温
                        ?>
                        <div class="date"><?php echo $date; ?></div>
                        <img src="https://openweathermap.org/img/wn/<?php echo $icon; ?>@2x.png" alt="<?php echo $weather; ?>">
                        <div class="min-max">
                            <span class="min-temp"><?php echo htmlspecialchars($temp_min); ?> ℃</span><br>
                            <span class="max-temp"><?php echo htmlspecialchars($temp_max); ?> ℃</span>
                        </div>
                        <div class="weather-description">
                            <?php echo htmlspecialchars($weather); ?> <!-- 天気の詳細を表示 -->
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- 戻るボタン -->
        <p><button onclick="history.back()">戻る</button></p>
    </div>
</body>
</html>
