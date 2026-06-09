
<?php

$url = 'https://cdn.vistahost.com.br/area38lt/vista.imobi/fotos/966/in7q8n_96668681c288f0c1.jpg';

$upload_dir = '.';
$filename = basename(parse_url($url, PHP_URL_PATH));
$filepath = $upload_dir . $filename;

if (!file_exists($upload_dir)) {
    wp_mkdir_p($upload_dir);
}

$ch = curl_init($url);

$fp = fopen($filepath, 'w+');

curl_setopt_array($ch, [
    CURLOPT_FILE => $fp,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_CONNECTTIMEOUT => 20,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36',
    CURLOPT_HTTPHEADER => [
        'Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
        'Referer: https://area38.com.br/',
    ],
]);

$success = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);
fclose($fp);

if ($success && $http_code == 200 && filesize($filepath) > 0) {
    echo 'Imagem baixada com sucesso: ' . $filepath;
} else {
    if (file_exists($filepath)) {
        unlink($filepath);
    }

    echo 'Erro ao baixar imagem. HTTP: ' . $http_code . ' - ' . $error;
}