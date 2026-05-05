<?php

function getMetaTagsFromUrl($url) {
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
    ]);

    $html = curl_exec($ch);
    curl_close($ch);

    if (!$html) {
        return [
            'url' => $url,
            'title' => null,
            'description' => null,
            'image' => null
        ];
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    // Função auxiliar
    function getMeta($xpath, $property) {
        $nodes = $xpath->query("//meta[@property='$property']");
        if ($nodes->length > 0) {
            return trim($nodes->item(0)->getAttribute('content'));
        }
        return null;
    }

    function getMetaName($xpath, $name) {
        $nodes = $xpath->query("//meta[@name='$name']");
        if ($nodes->length > 0) {
            return trim($nodes->item(0)->getAttribute('content'));
        }
        return null;
    }

    // OG tags
    $title = getMeta($xpath, 'og:title');
    $description = getMeta($xpath, 'og:description');
    $image = getMeta($xpath, 'og:image');
    $ogUrl = getMeta($xpath, 'og:url');

    // Fallbacks
    if (!$title) {
        $nodes = $xpath->query("//title");
        if ($nodes->length > 0) {
            $title = trim($nodes->item(0)->nodeValue);
        }
    }

    if (!$description) {
        $description = getMetaName($xpath, 'description');
    }

    return [
        'url' => $ogUrl ?: $url,
        'title' => $title,
        'description' => $description,
        'image' => $image
    ];
}


// 🔹 LISTA DE URLS
$urls = [
    "https://www.vivareal.com.br/",
    "https://www.zapimoveis.com.br/",
    "https://g1.globo.com/"
];

$resultados = [];

foreach ($urls as $url) {
    $resultados[] = getMetaTagsFromUrl($url);
}

// 🔹 Exibir resultado
header('Content-Type: application/json; charset=utf-8');
echo json_encode($resultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

