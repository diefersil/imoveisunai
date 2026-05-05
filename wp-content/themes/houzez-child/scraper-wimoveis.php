<?php

$listUrl = "https://www.wimoveis.com.br/venda/imoveis/mg/unai";

function getHtml($url) {
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_ENCODING => "",
        CURLOPT_HTTPHEADER => [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: pt-BR,pt;q=0.9,en;q=0.8",
        ]
    ]);

    $html = curl_exec($ch);
    curl_close($ch);

    return $html;
}

function absoluteUrl($url, $base = "https://www.wimoveis.com.br") {
    if (str_starts_with($url, "http")) {
        return $url;
    }

    return rtrim($base, "/") . "/" . ltrim($url, "/");
}

function getMetaOg($html, $property) {
    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $dom->loadHTML($html);

    $xpath = new DOMXPath($dom);

    $query = "//meta[@property='$property' or @name='$property']/@content";
    $nodes = $xpath->query($query);

    if ($nodes->length > 0) {
        return trim($nodes->item(0)->nodeValue);
    }

    return null;
}

function extractPropertyLinks($html) {
    preg_match_all('/href=["\']([^"\']+)["\']/i', $html, $matches);

    $links = [];

    foreach ($matches[1] as $href) {
        $href = html_entity_decode($href);

        if (
            str_contains($href, "/propriedades/") ||
            str_contains($href, "/imovel/")
        ) {
            $url = absoluteUrl($href);
            $links[$url] = $url;
        }
    }

    return array_values($links);
}

$html = getHtml($listUrl);

$propertyLinks = extractPropertyLinks($html);

$resultados = [];

foreach ($propertyLinks as $url) {
    $detailHtml = getHtml($url);

    if (!$detailHtml) {
        continue;
    }

    $resultados[] = [
        "url_anuncio" => $url,
        "og_title" => getMetaOg($detailHtml, "og:title"),
        "og_description" => getMetaOg($detailHtml, "og:description"),
        "og_image" => getMetaOg($detailHtml, "og:image"),
        "og_url" => getMetaOg($detailHtml, "og:url"),
    ];

    sleep(1);
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($resultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);