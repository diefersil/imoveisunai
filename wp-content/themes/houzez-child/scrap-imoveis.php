<?php

$sites = [
    [
        "url" => "https://primeimoveisunai.com.br/imoveis/negociacao/locacao",
        "base_url" => "https://primeimoveisunai.com.br",
        "seletor_link" => ".property-title a",
        "seletor_preco" => ".property-price span"
    ],

    // Exemplo para adicionar outro site:
    // [
    //     "url" => "https://outrosite.com.br/imoveis",
    //     "base_url" => "https://outrosite.com.br",
    //     "seletor_link" => ".classe-do-link a",
    //     "seletor_preco" => ".classe-do-preco"
    // ],
];

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

function limparPreco($preco) {
    return trim(preg_replace('/\s+/', ' ', $preco));
}

function cssToXpath($selector) {
    $partes = explode(" ", trim($selector));
    $xpath = "";

    foreach ($partes as $parte) {
        if (!$parte) continue;

        if (str_starts_with($parte, ".")) {
            $classe = substr($parte, 1);
            $xpath .= "//*[contains(concat(' ', normalize-space(@class), ' '), ' $classe ')]";
        } elseif (str_contains($parte, ".")) {
            [$tag, $classe] = explode(".", $parte, 2);
            $xpath .= "//$tag[contains(concat(' ', normalize-space(@class), ' '), ' $classe ')]";
        } else {
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

    $links = $xpath->query(cssToXpath($site["seletor_link"]));
    $precos = $xpath->query(cssToXpath($site["seletor_preco"]));

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
            "url do anuncio" => $urlAnuncio,
            "url da imagem" => getMetaProperty($detailXpath, "og:image"),
            "preco" => $preco,
            "descricao" => getMetaProperty($detailXpath, "og:description")
        ];
    }
}

header("Content-Type: application/json; charset=utf-8");

echo json_encode($resultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);