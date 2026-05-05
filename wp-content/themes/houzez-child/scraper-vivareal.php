<?php

$url = "https://www.vivareal.com.br/venda/minas-gerais/unai/";

function getHtml($url) {
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
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

$html = getHtml($url);

libxml_use_internal_errors(true);

$dom = new DOMDocument();
$dom->loadHTML($html);

$xpath = new DOMXPath($dom);

$imoveis = [];

// Pega links de imóveis
$links = $xpath->query("//a[contains(@href, '/imovel/')]");

foreach ($links as $link) {
    $href = $link->getAttribute("href");

    if (!$href) continue;

    if (strpos($href, "http") !== 0) {
        $href = "https://www.vivareal.com.br" . $href;
    }

    $texto = limpar($link->textContent);

    if (strlen($texto) < 20) continue;

    preg_match('/R\$\s?[\d\.\,]+/', $texto, $preco);
    preg_match('/\d+\s?m²/', $texto, $area);
    preg_match('/\d+\s?quartos?/', $texto, $quartos);
    preg_match('/\d+\s?banheiros?/', $texto, $banheiros);
    preg_match('/\d+\s?vagas?/', $texto, $vagas);

    $img = $xpath->query(".//img", $link);
    $imagem = "";

    if ($img->length > 0) {
        $imagem = $img[0]->getAttribute("src") ?: $img[0]->getAttribute("data-src");
    }

    $imoveis[$href] = [
        "titulo" => $texto,
        "preco" => $preco[0] ?? "",
        "area" => $area[0] ?? "",
        "quartos" => $quartos[0] ?? "",
        "banheiros" => $banheiros[0] ?? "",
        "vagas" => $vagas[0] ?? "",
        "url" => $href,
        "imagem" => $imagem
    ];
}

$imoveis = array_values($imoveis);

header("Content-Type: application/json; charset=utf-8");
echo json_encode($imoveis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);