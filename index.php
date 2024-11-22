<?php
require_once 'config.php';

$city = $_POST['city'] ?? '';
$searchError = '';
$weatherData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $city) {
    $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&units=metric&lang=ja&appid=" . API_KEY;

    // cURLでリクエスト送信
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    // レスポンスをデコード
    $data = json_decode($response, true);

    if (!isset($data['cod']) || $data['cod'] != 200) {
        $searchError = "都市が見つかりませんでした: " . ($data['message'] ?? '理由不明');
    } else {
        $weatherData = $data;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>天気検索</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: ivory;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 24px;
            color: #81d4fa; 
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        form input[type="text"] {
            width: 60%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            outline: none;
        }

        form button {
            padding: 10px 20px;
            background-color: #b3e5fc; 
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        form button:hover {
            background-color: #81d4fa; /* ホバー時の水色 */
            transform: translateY(-2px); /* 浮き上がる */
        }

        .links {
            text-align: center;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px;
            background-color: #b3e5fc; /* ボタンも水色 */
            color: white;
            border: none;
            border-radius: 50px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .button:hover {
            background-color: #81d4fa; /* ホバー時に濃い水色 */
            transform: translateY(-3px); /* 浮き上がる */
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
        }

        .button:active {
            background-color: #4fc3f7; /* クリック時の濃い水色 */
            transform: translateY(0);
        }

        .button.alert-button {
            background-color: #ff9999; /* 警報ボタンはピンク */
        }

        .button.alert-button:hover {
            background-color: #ff6666; /* ホバー時に濃いピンク */
        }

        .error {
            color: #ff6666; /* エラーも水色から赤系に統一 */
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>天気検索</h1>

        <!-- 検索フォーム -->
        <form action="index.php" method="POST">
            <input type="text" name="city" placeholder="都市名を入力" required value="<?php echo htmlspecialchars($city); ?>">
            <button type="submit">検索</button>
        </form>

        <!-- エラーメッセージ表示 -->
        <?php if ($searchError): ?>
            <p class="error"><?php echo htmlspecialchars($searchError); ?></p>
        <?php elseif (!empty($city)): ?>
            <h3>検索結果: <?php echo htmlspecialchars($city); ?></h3>
            <div class="links">
                <a href="current_weather.php?city=<?php echo urlencode($city); ?>" class="button">現在の天気 🌞</a>
                <a href="weekly_forecast.php?city=<?php echo urlencode($city); ?>" class="button">週間天気予報 📅</a>
                <a href="alerts.php?city=<?php echo urlencode($city); ?>" class="button alert-button">天気警報 ⚠️</a>
                <a href="weather_ai_chat.php?city=<?php echo urlencode($city); ?>" class="button">天気AIチャット 💬</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
