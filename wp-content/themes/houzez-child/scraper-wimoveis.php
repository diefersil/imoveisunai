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
    "https://www.wimoveis.com.br/propriedades/fazenda-a-venda-em-unai-mg-664-ha-melhor-regiao-da-3020695945.html?n_src=Listado&n_pg=1&n_pos=1",
    "https://www.zapimoveis.com.br/imovel/venda-terreno-lote-condominio-1-quarto-residencial-vitoria-unai-mg-241m2-id-2883562327/",
    "https://imoveisunai.com.br/imoveis/aluga-se-casa-no-riviera-park-com-2-quartos/"
];

$resultados = [];

foreach ($urls as $url) {
    $resultados[] = getMetaTagsFromUrl($url);
}

// 🔹 Exibir resultado
header('Content-Type: application/json; charset=utf-8');
echo json_encode($resultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

?>