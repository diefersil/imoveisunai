<?php

$sites = [
    [
        "url" => "https://primeimoveisunai.com.br/imoveis/negociacao/locacao",
        "base_url" => "https://primeimoveisunai.com.br",
        "seletor_link" => ".property-title a",
        "seletor_preco" => ".property-price span"
    ],

    // Exemplo de outro site:
    // [
    //     "url" => "https://site.com.br/imoveis",
    //     "base_url" => "https://site.com.br",
    //     "seletor_link" => ".classe-link a",
    //     "seletor_preco" => ".classe-preco"
    // ]
];

function getHtml($url) {

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => "Mozilla/5.0",
        CURLOPT_TIMEOUT => 30,
    ]);

    $html = curl_exec($ch);

    curl_close($ch);

    return $html;
}

function absoluteUrl($url, $base) {

    if (strpos($url, "http") === 0) {
        return $url;
    }

    return rtrim($base, "/") . "/" . ltrim($url, "/");
}

function getMetaProperty($xpath, $property) {

    return trim(
        $xpath->evaluate(
            "string(//meta[@property='$property']/@content)"
        )
    );
}

function limparPreco($texto) {

    $texto = preg_replace('/\s+/', ' ', $texto);

    return trim($texto);
}

function cssToXpath($selector) {

    $selector = trim($selector);

    $partes = explode(" ", $selector);

    $xpath = "";

    foreach ($partes as $parte) {

        if (!$parte) {
            continue;
        }

        // .classe
        if (strpos($parte, ".") === 0) {

            $classe = substr($parte, 1);

            $xpath .= "//*[contains(concat(' ', normalize-space(@class), ' '), ' $classe ')]";
        }

        // tag.classe
        elseif (strpos($parte, ".") !== false) {

            $tmp = explode(".", $parte);

            $tag = $tmp[0];
            $classe = $tmp[1];

            $xpath .= "//$tag[contains(concat(' ', normalize-space(@class), ' '), ' $classe ')]";
        }

        // tag normal
        else {

            $xpath .= "//$parte";
        }
    }

    return $xpath;
}

libxml_use_internal_errors(true);

$resultados = [];

$urlsVisitadas = [];

foreach ($sites as $site) {

    $html = getHtml($site["url"]);

    if (!$html) {
        continue;
    }

    $dom = new DOMDocument();

    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);

    $xpathLinks = cssToXpath($site["seletor_link"]);
    $xpathPrecos = cssToXpath($site["seletor_preco"]);

    $links = $xpath->query($xpathLinks);
    $precos = $xpath->query($xpathPrecos);

    foreach ($links as $index => $link) {

        $href = trim($link->getAttribute("href"));

        if (!$href) {
            continue;
        }

        $urlAnuncio = absoluteUrl($href, $site["base_url"]);

        if (isset($urlsVisitadas[$urlAnuncio])) {
            continue;
        }

        $urlsVisitadas[$urlAnuncio] = true;

        $preco = "";

        if (isset($precos[$index])) {
            $preco = limparPreco($precos[$index]->textContent);
        }

        $detailHtml = getHtml($urlAnuncio);

        if (!$detailHtml) {
            continue;
        }

        $detailDom = new DOMDocument();

        @$detailDom->loadHTML($detailHtml);

        $detailXpath = new DOMXPath($detailDom);

        $resultados[] = [
            "url_anuncio" => $urlAnuncio,
            "url_imagem" => getMetaProperty($detailXpath, "og:image"),
            "preco" => $preco,
            "descricao" => getMetaProperty($detailXpath, "og:description")
        ];
    }
}

header("Content-Type: application/json; charset=utf-8");

echo json_encode(
    $resultados,
    JSON_PRETTY_PRINT |
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
);