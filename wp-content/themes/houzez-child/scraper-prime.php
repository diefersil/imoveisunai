<?php

$listUrl = "https://primeimoveisunai.com.br/imoveis/negociacao/locacao";

function getHtml($url) {
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36",
        CURLOPT_TIMEOUT => 30,
    ]);

    $html = curl_exec($ch);
    curl_close($ch);

    return $html;
}

function absoluteUrl($url, $base) {
    if (str_starts_with($url, "http")) {
        return $url;
    }

    return rtrim($base, "/") . "/" . ltrim($url, "/");
}

function getMetaProperty($xpath, $property) {
    return trim($xpath->evaluate("string(//meta[@property='$property']/@content)"));
}

$html = getHtml($listUrl);

libxml_use_internal_errors(true);

$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

$baseUrl = "https://primeimoveisunai.com.br";

$links = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' property-title ')]//a");

$resultados = [];
$urlsVisitadas = [];

foreach ($links as $link) {
    $href = trim($link->getAttribute("href"));

    if (!$href) {
        continue;
    }

    $imovelUrl = absoluteUrl($href, $baseUrl);

    if (isset($urlsVisitadas[$imovelUrl])) {
        continue;
    }

    $urlsVisitadas[$imovelUrl] = true;

    $detailHtml = getHtml($imovelUrl);

    $detailDom = new DOMDocument();
    @$detailDom->loadHTML($detailHtml);
    $detailXpath = new DOMXPath($detailDom);

    $resultados[] = [
        "og_title" => getMetaProperty($detailXpath, "og:title"),
        "og_url" => getMetaProperty($detailXpath, "og:url"),
        "og_description" => getMetaProperty($detailXpath, "og:description"),
        "url_coletada" => $imovelUrl
    ];
}

header("Content-Type: application/json; charset=utf-8");

echo json_encode($resultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);