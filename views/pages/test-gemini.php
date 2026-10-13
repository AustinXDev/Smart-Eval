<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found');
}


require_once __DIR__ . '/../../app/init.php';

use App\Services\AI\GeminiService;

$gemini = new GeminiService();

try {

    $result = $gemini->generate(
        'Give me a 4 heroes of the philippines.'
    );

    echo '<pre>';
    echo htmlspecialchars($result);
    echo '</pre>';

} catch (Throwable $e) {

    http_response_code(500);

    echo '<pre>';
    echo htmlspecialchars($e->getMessage());
    echo '</pre>';
}
