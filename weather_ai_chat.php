<?php
require_once 'config.php';

function getWeatherData($city) {
    $url = "https://api.openweathermap.org/data/2.5/forecast?q=" . urlencode($city) . "&units=metric&lang=ja&appid=" . API_KEY;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function generateAIResponse($question, $weatherData) {
    $city = $weatherData['city']['name'];
    $forecast = $weatherData['list'][0];
    $temp = $forecast['main']['temp'];
    $description = $forecast['weather'][0]['description'];

    $responses = [
        '天気' => "{$city}の天気は{$description}、気温は{$temp}℃です。",
        '服装' => temperatureBasedClothingAdvice($temp),
        '傘' => isPrecipitationExpected($forecast) ? "雨の可能性があるので、傘を持参することをおすすめします。" : "傘は必要なさそうです。",
    ];

    foreach ($responses as $keyword => $response) {
        if (stripos($question, $keyword) !== false) {
            return $response;
        }
    }

    return "{$city}の天気は{$description}、気温は{$temp}℃です。服装や傘については、より具体的に質問してください。";
}

function temperatureBasedClothingAdvice($temp) {
    if ($temp < 10) return "寒いので、厚めのコートや防寒着をおすすめします。";
    if ($temp < 15) return "薄手のジャケットやカーディガンが適しています。";
    if ($temp < 20) return "長袖シャツやライトアウターがおすすめです。";
    if ($temp < 25) return "半袖シャツや薄手のジャケットが快適です。";
    return "半袖やTシャツで過ごせる暑さです。熱中症に注意してください。";
}

function isPrecipitationExpected($forecast) {
    return isset($forecast['rain']) || isset($forecast['snow']) || 
           (isset($forecast['weather'][0]['main']) && 
            in_array($forecast['weather'][0]['main'], ['Rain', 'Snow']));
}

$city = $_GET['city'] ?? '東京';
$weatherData = getWeatherData($city);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>天気AIチャット</title>
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

        .chat-container {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #ccc;
            height: 500px;
            display: flex;
            flex-direction: column;
        }

        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 10px;
        }

        .chat-input {
            display: flex;
            padding: 10px;
        }

        .chat-input input {
            flex-grow: 1;
            margin-right: 10px;
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
        <h1>天気AIチャット: <?php echo htmlspecialchars($city); ?></h1>
        <div class="chat-container">
            <div class="chat-messages" id="chatMessages"></div>
            <div class="chat-input">
                <input type="text" id="userInput" placeholder="質問を入力してください">
                <button onclick="sendMessage()">送信</button>
            </div>
        </div>
    </div>

    <script>
    function sendMessage() {
        const input = document.getElementById('userInput');
        const messagesContainer = document.getElementById('chatMessages');
        
        if (!input.value.trim()) return;

        // ユーザーメッセージ
        const userMessageEl = document.createElement('div');
        userMessageEl.textContent = '👤 ' + input.value;
        messagesContainer.appendChild(userMessageEl);

        // AIメッセージ
        const aiMessageEl = document.createElement('div');
        aiMessageEl.textContent = '🤖 ' + generateAIResponse(input.value);
        messagesContainer.appendChild(aiMessageEl);

        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        input.value = '';
    }

    function generateAIResponse(question) {
        const responses = {
            '天気': '<?php echo generateAIResponse('天気', $weatherData); ?>',
            '服装': '<?php echo generateAIResponse('服装', $weatherData); ?>',
            '傘': '<?php echo generateAIResponse('傘', $weatherData); ?>'
        };

        for (let [keyword, response] of Object.entries(responses)) {
            if (question.includes(keyword)) {
                return response;
            }
        }

        return '<?php echo generateAIResponse('', $weatherData); ?>';
    }
    </script>
    <p><button onclick="history.back()">戻る</button></p>
</body>
</html>
