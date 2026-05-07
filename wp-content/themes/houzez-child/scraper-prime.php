<?php

$config = [
    "url" => "https://primeimoveisunai.com.br/imoveis/negociacao/locacao",
    "card" => ".hover-effect",
    "link" => "a",
    "preco" => ".property-price span",
    "data_scraper" => date("Y-m-d H:i:s")
];

function getHtml($url) {
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_ENCODING => "",
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124 Safari/537.36",
        CURLOPT_HTTPHEADER => [
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: pt-BR,pt;q=0.9"
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $html = curl_exec($ch);
    curl_close($ch);

    return $html;
}

function limpar($texto) {
    return trim(preg_replace('/\s+/', ' ', $texto));
}

function limparPreco($preco) {
    return preg_replace('/\D/', '', $preco);
}

function cssToXpath($selector) {
    if (substr($selector, 0, 1) === ".") {
        $class = substr($selector, 1);
        return "//*[contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')]";
    }

    return "//{$selector}";
}

function absoluteUrl($base, $url) {
    if (!$url) return "";

    if (strpos($url, "http") === 0) {
        return $url;
    }

    $p = parse_url($base);

    if (substr($url, 0, 1) !== "/") {
        $url = "/" . $url;
    }

    return $p["scheme"] . "://" . $p["host"] . $url;
}

function getMetaOg($url) {
    $html = getHtml($url);

    if (!$html) {
        return [
            "og_title" => "",
            "og_image" => ""
        ];
    }

    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $dom->loadHTML($html);

    $xpath = new DOMXPath($dom);

    $ogTitle = "";
    $ogImage = "";

    $titleNode = $xpath->query("//meta[@property='og:title']");
    if ($titleNode->length > 0) {
        $ogTitle = $titleNode[0]->getAttribute("content");
    }

    $imageNode = $xpath->query("//meta[@property='og:image']");
    if ($imageNode->length > 0) {
        $ogImage = $imageNode[0]->getAttribute("content");
    }

    return [
        "og_title" => limpar($ogTitle),
        "og_image" => $ogImage
    ];
}

$html = getHtml($config["url"]);

libxml_use_internal_errors(true);

$dom = new DOMDocument();
$dom->loadHTML($html);

$xpath = new DOMXPath($dom);

$cards = $xpath->query(cssToXpath($config["card"]));

$imoveis = [];

foreach ($cards as $card) {

    $linkNode = $xpath->query(".//a", $card);

    if ($linkNode->length == 0) {
        continue;
    }

    $href = $linkNode[0]->getAttribute("href");

    if (!$href) {
        continue;
    }

    $urlImovel = absoluteUrl($config["url"], $href);

    $titulo = limpar($linkNode[0]->textContent);

    $preco = "";

    $precoNode = $xpath->query(".//*[contains(@class,'property-price')]//span", $card);

    if ($precoNode->length > 0) {
        $preco = limparPreco($precoNode[0]->textContent);
    }

    $meta = getMetaOg($urlImovel);

    $imoveis[$urlImovel] = [
        "titulo" => $titulo,
        "preco" => $preco,
        "url" => $urlImovel,
        "og_title" => $meta["og_title"],
        "og_image" => $meta["og_image"],
        "data_scraper" => $config["data_scraper"]
    ];
}

$imoveis = array_values($imoveis);

header("Content-Type: application/json; charset=utf-8");

echo json_encode([
    "total" => count($imoveis),
    "fonte" => $config["url"],
    "data_scraper" => $config["data_scraper"],
    "imoveis" => $imoveis
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);