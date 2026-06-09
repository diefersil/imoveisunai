<?php

$url = 'https://cdn.vistahost.com.br/area38lt/vista.imobi/fotos/966/in7q8n_96668681c288f0c1.jpg';

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36',
    CURLOPT_HTTPHEADER => [
        'Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
        'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
        'Referer: https://area38.com.br/',
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$response = curl_exec($ch);

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$error = curl_error($ch);

curl_close($ch);

echo '<pre>';
echo "HTTP CODE: {$http_code}\n";
echo "CONTENT TYPE: {$content_type}\n";
echo "CURL ERROR: {$error}\n\n";
echo htmlspecialchars(substr($response, 0, 2000));
echo '</pre>';