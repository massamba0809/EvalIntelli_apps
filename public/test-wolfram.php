<?php
// test-wolfram.php - Test isolé de Wolfram Alpha

// Votre clé Wolfram Alpha (remplacez par la vraie)
$WOLFRAM_APP_ID = 'AGQXL5-JEY67YHVU6'; // ⚠️ METTEZ VOTRE VRAIE CLÉ

// Questions de test
$questions = [
    '2+2',
    'solve x+5=10',
    'solve 2x+5=15',
    'derivative of x^2',
    'integral of x^2'
];

echo "<h1>🧮 Test Wolfram Alpha</h1>";
echo "<hr>";

foreach ($questions as $question) {
    echo "<h2>Question: {$question}</h2>";

    // URL de l'API Wolfram
    $url = "https://api.wolframalpha.com/v2/simple?appid={$WOLFRAM_APP_ID}&i=" . urlencode($question);

    echo "<p><strong>URL:</strong> <a href='{$url}' target='_blank'>{$url}</a></p>";

    // Appel à l'API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    // Affichage du résultat
    echo "<p><strong>Status HTTP:</strong> {$httpCode}</p>";

    if ($error) {
        echo "<p style='color: red;'><strong>Erreur cURL:</strong> {$error}</p>";
    } else {
        echo "<p><strong>Réponse:</strong></p>";

        if ($httpCode == 200 && !empty(trim($response))) {
            echo "<div style='background: #e8f5e8; padding: 10px; border: 1px solid #4caf50;'>";
            echo "<strong style='color: green;'>✅ SUCCÈS:</strong><br>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
            echo "</div>";
        } else {
            echo "<div style='background: #ffeaea; padding: 10px; border: 1px solid #f44336;'>";
            echo "<strong style='color: red;'>❌ ÉCHEC:</strong><br>";
            echo "<pre>" . htmlspecialchars($response ?: 'Réponse vide') . "</pre>";
            echo "</div>";
        }
    }

    echo "<hr>";
}

echo "<h2>🔧 Diagnostic</h2>";
echo "<ul>";
echo "<li><strong>Clé API utilisée:</strong> " . substr($WOLFRAM_APP_ID, 0, 8) . "...</li>";
echo "<li><strong>Serveur PHP:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>cURL activé:</strong> " . (function_exists('curl_init') ? 'Oui' : 'Non') . "</li>";
echo "<li><strong>Allow URL fopen:</strong> " . (ini_get('allow_url_fopen') ? 'Oui' : 'Non') . "</li>";
echo "</ul>";

?>
