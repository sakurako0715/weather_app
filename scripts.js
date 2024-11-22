document.addEventListener("DOMContentLoaded", () => {
    // 通知の許可をリクエスト
    if (Notification && Notification.permission !== "granted") {
        Notification.requestPermission().then(permission => {
            if (permission !== "granted") {
                console.log("通知が許可されませんでした");
            }
        });
    }

    // サーバーからアラートデータを取得
    const alertContainer = document.getElementById("alerts-data");
    if (alertContainer) {
        const alerts = JSON.parse(alertContainer.dataset.alerts); // PHPからのデータ

        if (alerts.length > 0 && Notification.permission === "granted") {
            alerts.forEach(alert => {
                const notification = new Notification("天気警報", {
                    body: `${alert.event}\n${alert.description}`,
                    icon: "https://upload.wikimedia.org/wikipedia/commons/thumb/e/e3/Weather-storm-icon.png/128px-Weather-storm-icon.png" // 適当なアイコンURL
                });

                notification.onclick = () => {
                    window.focus(); // 通知をクリックしたらウィンドウをフォーカス
                };
            });
        }
    }
});
