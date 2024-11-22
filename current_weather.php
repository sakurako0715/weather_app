<?php
require 'config.php';

// 都市名を取得（デフォルトはTokyo）
$city = isset($_GET['city']) ? $_GET['city'] : 'Tokyo';

// 現在の天気データ取得
$url = BASE_URL . "weather?q=" . urlencode($city) . "&units=metric&lang=ja&appid=" . API_KEY;
$response = @file_get_contents($url);
if ($response === false) {
    $error = "現在の天気情報を取得できませんでした。";
} else {
    $data = json_decode($response, true);
    if (!isset($data['cod']) || $data['cod'] != 200) {
        $error = "エラー: " . $data['message'];
    } else {
        $weather = $data['weather'][0]['description'];
        $icon = $data['weather'][0]['icon'];
        $temp = $data['main']['temp'];
        $temp_min = $data['main']['temp_min'];
        $temp_max = $data['main']['temp_max'];
    }
}

// 時間ごとの天気データ取得
$forecastUrl = BASE_URL . "forecast?q=" . urlencode($city) . "&units=metric&lang=ja&appid=" . API_KEY;
$forecastResponse = @file_get_contents($forecastUrl);
if ($forecastResponse !== false) {
    $forecastData = json_decode($forecastResponse, true);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>現在の天気</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: ivory;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 400px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #81d4fa; /* 水色 */
            text-align: center;
            margin-bottom: 20px;
        }

        .weather-container {
            text-align: center;
            margin-top: 20px;
        }

        .weather-icon {
            width: 100px;
            height: auto;
        }

        .temp {
            font-size: 20px;
            font-weight: bold;
        }

        .min-max {
            font-size: 16px;
        }

        .min-temp {
            color: blue;
        }

        .max-temp {
            color: red;
        }

        .error {
            color: red;
        }

        .forecast-section {
            margin: 20px auto;
            text-align: center;
        }

        .hourly-forecast, .weekly-forecast {
            margin-top: 20px;
        }

        .hourly-chart {
            width: 100%;
            height: 200px;
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <h1>現在の天気</h1>
        <p>都市: <?php echo htmlspecialchars($city); ?></p>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php else: ?>
            <!-- 現在の天気 -->
            <div class="weather-container">
                <img src="https://openweathermap.org/img/wn/<?php echo $icon; ?>@2x.png" alt="<?php echo $weather; ?>" />
                <div class="weather-description"><?php echo htmlspecialchars($weather); ?></div>
                <div class="temp">現在の気温: <?php echo htmlspecialchars($temp); ?> ℃</div>
                <div class="min-max">
                    最低気温: <span class="min-temp"><?php echo htmlspecialchars($temp_min); ?> ℃</span><br>
                    最高気温: <span class="max-temp"><?php echo htmlspecialchars($temp_max); ?> ℃</span>
                </div>
            </div>

            <!-- 時間ごとの天気 -->
            <div class="forecast-section hourly-forecast">
                <h2 class="forecast-header">時間ごとの天気</h2>
                <canvas id="hourly-chart" class="hourly-chart"></canvas>
            </div>
        <?php endif; ?>

        <p><button onclick="history.back()">戻る</button></p>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const ctx = document.getElementById('hourly-chart').getContext('2d');
            const forecastData = <?php echo json_encode($forecastData['list'] ?? []); ?>;

            // 最初の8時間分のデータを抽出
            const labels = forecastData.slice(0, 8).map(item => new Date(item.dt_txt).getHours() + '時');
            const temps = forecastData.slice(0, 8).map(item => item.main.temp);

            // グラフ描画
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '気温 (°C)',
                        data: temps,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        fill: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: '時間',
                            },
                        },
                        y: {
                            title: {
                                display: true,
                                text: '気温 (°C)',
                            },
                        },
                    },
                },
            });
        });
    </script>
</body>
</html>
