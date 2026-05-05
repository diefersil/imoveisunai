<?php

$listagem = "https://www.zapimoveis.com.br/venda/imoveis/mg+unai/";

function getHtml($url) {
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => "",
        CURLOPT_TIMEOUT => 40,
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36",
        CURLOPT_HTTPHEADER => [
            "Accept-Language: pt-BR,pt;q=0.9",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8"
        ]
    ]);

    $html = curl_exec($ch);
    curl_close($ch);

    return $html;
}

function limpar($texto) {
    return trim(preg_replace("/\s+/", " ", $texto));
}

function absoluto($url) {
    if (str_starts_with($url, "http")) {
        return $url;
    }

    return "https://www.zapimoveis.com.br" . $url;
}

function extrairLinksAnuncios($html) {
    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $links = [];

    foreach ($xpath->query("//a[contains(@href, '/imovel/')]") as $a) {
        $href = absoluto($a->getAttribute("href"));

        if (!in_array($href, $links)) {
            $links[] = $href;
        }
    }

    return $links;
}

function meta($xpath, $property) {
    $q = "//meta[@property='$property']/@content | //meta[@name='$property']/@content";
    $nodes = $xpath->query($q);

    return $nodes->length ? limpar($nodes->item(0)->nodeValue) : null;
}

function extrairImagens($html) {
    $imagens = [];

    $html = str_replace(["\\u002F", "\\/"], "/", $html);

    preg_match_all('/https?:\/\/[^"\']+\.(jpg|jpeg|png|webp)(\?[^"\']*)?/i', $html, $matches);

    foreach ($matches[0] as $img) {
        $img = html_entity_decode($img);

        if (
            !str_contains($img, "logo") &&
            !str_contains($img, "icon") &&
            !str_contains($img, "favicon") &&
            !str_contains($img, "sprite") &&
            !in_array($img, $imagens)
        ) {
            $imagens[] = $img;
        }
    }

    return array_values($imagens);
}

function extrairDadosAnuncio($url) {
    $html = getHtml($url);

    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $titulo = meta($xpath, "og:title");
    $descricao = meta($xpath, "og:description");
    $imagemPrincipal = meta($xpath, "og:image");
    $urlCanonica = meta($xpath, "og:url");

    preg_match('/R\$\s?[\d\.\,]+/', $html, $preco);
    preg_match('/([\d\.]+)\s?m²/', $html, $area);
    preg_match('/(\d+)\squarto/', $html, $quartos);
    preg_match('/(\d+)\sbanheiro/', $html, $banheiros);
    preg_match('/(\d+)\svaga/', $html, $vagas);

    $imagens = extrairImagens($html);

    if ($imagemPrincipal && !in_array($imagemPrincipal, $imagens)) {
        array_unshift($imagens, $imagemPrincipal);
    }

    return [
        "titulo" => $titulo,
        "descricao" => $descricao,
        "preco" => $preco[0] ?? null,
        "area" => $area[1] ?? null,
        "quartos" => $quartos[1] ?? null,
        "banheiros" => $banheiros[1] ?? null,
        "vagas" => $vagas[1] ?? null,
        "url" => $urlCanonica ?: $url,
        "imagem_principal" => $imagemPrincipal,
        "imagens" => $imagens,
        "total_imagens" => count($imagens)
    ];
}

$htmlListagem = getHtml($listagem);
$links = extrairLinksAnuncios($htmlListagem);

$resultados = [];

foreach ($links as $link) {
    $resultados[] = extrairDadosAnuncio($link);
    sleep(1);
}

file_put_contents(
    "imoveis_zap_unai.json",
    json_encode($resultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);

$csv = fopen("imoveis_zap_unai.csv", "w");

fputcsv($csv, [
    "titulo",
    "descricao",
    "preco",
    "area",
    "quartos",
    "banheiros",
    "vagas",
    "url",
    "imagem_principal",
    "total_imagens",
    "imagens"
], ";");

foreach ($resultados as $imovel) {
    fputcsv($csv, [
        $imovel["titulo"],
        $imovel["descricao"],
        $imovel["preco"],
        $imovel["area"],
        $imovel["quartos"],
        $imovel["banheiros"],
        $imovel["vagas"],
        $imovel["url"],
        $imovel["imagem_principal"],
        $imovel["total_imagens"],
        implode(" | ", $imovel["imagens"])
    ], ";");
}

fclose($csv);

header("Content-Type: application/json; charset=utf-8");
echo json_encode([
    "total_anuncios" => count($resultados),
    "arquivo_json" => "imoveis_zap_unai.json",
    "arquivo_csv" => "imoveis_zap_unai.csv",
    "dados" => $resultados
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);