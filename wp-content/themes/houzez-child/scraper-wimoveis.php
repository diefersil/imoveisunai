<?php

$listUrl = "https://www.wimoveis.com.br/venda/imoveis/mg/unai";

function getHtml($url) {
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 40,
        CURLOPT_ENCODING => "",
        CURLOPT_HTTPHEADER => [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: pt-BR,pt;q=0.9"
        ]
    ]);

    $html = curl_exec($ch);
    curl_close($ch);

    return $html;
}

function getOgMeta($html, $property) {
    $pattern1 = '/<meta[^>]+(?:property|name)=["\']' . preg_quote($property, '/') . '["\'][^>]+content=["\']([^"\']*)["\']/i';
    $pattern2 = '/<meta[^>]+content=["\']([^"\']*)["\'][^>]+(?:property|name)=["\']' . preg_quote($property, '/') . '["\']/i';

    if (preg_match($pattern1, $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, "UTF-8");
    }

    if (preg_match($pattern2, $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, "UTF-8");
    }

    return null;
}

function extractJsonLd($html) {
    preg_match_all(
        '/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is',
        $html,
        $matches
    );

    $items = [];

    foreach ($matches[1] as $jsonText) {
        $jsonText = trim(html_entity_decode($jsonText, ENT_QUOTES | ENT_HTML5, "UTF-8"));
        $data = json_decode($jsonText, true);

        if (!$data) {
            continue;
        }

        if (isset($data[0])) {
            foreach ($data as $item) {
                $items[] = $item;
            }
        } else {
            $items[] = $data;
        }
    }

    return $items;
}

function extractProperties($jsonLdItems) {
    $properties = [];

    foreach ($jsonLdItems as $item) {
        if (
            isset($item["@type"]) &&
            $item["@type"] === "SellAction" &&
            isset($item["object"]["url"])
        ) {
            $object = $item["object"];

            $properties[] = [
                "titulo" => $object["name"] ?? null,
                "descricao" => $object["description"] ?? null,
                "url" => $object["url"] ?? null,
                "area" => $object["floorSize"]["unitText"] ?? null,
                "bairro" => $object["address"]["addressLocality"] ?? null,
                "estado" => $object["address"]["addressRegion"] ?? null,
                "cep" => $object["address"]["postalCode"] ?? null,
                "endereco" => $object["address"]["streetAddress"] ?? null,
                "latitude" => $object["geo"]["latitude"] ?? null,
                "longitude" => $object["geo"]["longitude"] ?? null,
            ];
        }
    }

    return $properties;
}

$listHtml = getHtml($listUrl);

$jsonLdItems = extractJsonLd($listHtml);
$properties = extractProperties($jsonLdItems);

$results = [];

foreach ($properties as $property) {
    $detailHtml = getHtml($property["url"]);

    $results[] = [
        "titulo_lista" => $property["titulo"],
        "descricao_lista" => $property["descricao"],
        "url_imovel" => $property["url"],
        "area" => $property["area"],
        "bairro" => $property["bairro"],
        "estado" => $property["estado"],
        "cep" => $property["cep"],
        "endereco" => $property["endereco"],
        "latitude" => $property["latitude"],
        "longitude" => $property["longitude"],

        "og_title" => getOgMeta($detailHtml, "og:title"),
        "og_description" => getOgMeta($detailHtml, "og:description"),
        "og_image" => getOgMeta($detailHtml, "og:image"),
        "og_url" => getOgMeta($detailHtml, "og:url"),
    ];

    sleep(1);
}

header("Content-Type: application/json; charset=utf-8");

echo json_encode([
    "total" => count($results),
    "imoveis" => $results
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);