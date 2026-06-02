<?php

ini_set('max_execution_time', 2000);
set_time_limit(2000);

date_default_timezone_set("America/Sao_Paulo");

$arquivoCsv = "scraper-res.csv";
$limiteRegistrosCsv = 300;

/**
 * REGRA GLOBAL DE CATEGORIA DO IMÓVEL
 */
$categoriaImovelRegras = [
    [
        "categoria" => "Casas",
        "strings" => "casa, sobrado, meia agua, meia água, casas, mansao, mansão"
    ],
    [
        "categoria" => "Fazendas",
        "strings" => "fazenda,fazendas,rural,chácara,chacara,sítio,sitio"
    ],
    [
        "categoria" => "Sítios e Chácaras",
        "strings" => "chácara,chacaras,sitio,sitios"
    ],
    [
        "categoria" => "Chácaras",
        "strings" => "chácara,chacaras"
    ],
    [
        "categoria" => "Lotes e Terrenos",
        "strings" => "lote, lotes, terreno, terrenos"
    ],
    [
        "categoria" => "Apartamentos",
        "strings" => "apartamento, apartamentos, apto"
    ],
    [
        "categoria" => "Kitnet",
        "strings" => "kitnet,kitinets,quitinete"
    ]
];

/**
 * REGRA GLOBAL DE STATUS DO IMÓVEL
 *
 * Verifica card_nome + descricao interna.
 */
$StatusImovelRegras = [
    [
        "status" => "Aluguel",
        "strings" => "aluguel,aluga,aluga-se,locação,locações,locacao, locacoes,alugar"
    ],
    [
        "status" => "Venda",
        "strings" => "venda,vende,vende-se,à venda,a venda,compra,comprar,vender"
    ]
];

/**
 * CONFIGURAÇÃO DOS SITES
 *
 * A chave "url" pode ser uma string única:
 * "url" => "https://site.com/pagina"
 *
 * Ou várias URLs para paginação:
 * "url" => [
 *     "https://site.com/pagina",
 *     "https://site.com/pagina/page/2",
 *     "https://site.com/pagina/page/3"
 * ]
 */
$sites = [
    [
        "nome_site" => "Prime Imóveis - Locação",
        "usuario" => "imoveisunai",
        "cidade" => "Unaí MG",
        "categoria" => "",
        "tags" => "",
        "contato" => "(38) 99970-6070",
        "periodo" => 30,
        "url" => "https://primeimoveisunai.com.br/imoveis/negociacao/locacao",
        "numero_registros" => 20,
        "frequencia" => [
            "tipo" => "sempre"
        ],
        "verificar_string" => "",
        "seletores" => [
            "card" => "//div[contains(@class,'property-main')]",
            "card_nome" => ".//h3[contains(@class,'property-title')]",
            "preco" => ".//div[contains(@class,'property-price')]//span",
            "card_imagem_url" => ".//img[contains(@class,'img-fluid')]",
            "card_url" => ".//a",
            "galeria" => "//img[contains(@class,'img-fluid')]",
            "descricao" => "//div[contains(@class,'inner-box property-dsc')]"
        ]
    ],
    [
        "nome_site" => "Sucesso Imóveis - Geral",
        "usuario" => "imoveisunai",
        "cidade" => "Unaí MG",
        "categoria" => "",
        "tags" => "",
        "contato" => "(38) 99935-9555",
        "periodo" => 30,
        "url" => [
            "https://sucessoimoveis.imb.br/imoveis",
            "https://sucessoimoveis.imb.br/imoveis/page/2",
            "https://sucessoimoveis.imb.br/imoveis/page/3"
        ],
        "numero_registros" => 20,

        "frequencia" => [
            "tipo" => "sempre"
        ],
        "verificar_string" => "",
        "seletores" => [
            "card" => "//div[contains(@class,'g5ere__property-item-inner')]",
            "card_nome" => ".//h3",
            "preco" => ".//span[contains(@class,'g5ere__lpp-price')]",
            "card_imagem_url" => ".//div[contains(@class,'g5ere__property-featured')]//a[contains(@style,'background-image')]",
            "card_url" => ".//a[contains(@class,'g5core__entry-thumbnail')]",
            "galeria" => "//div[contains(@class,'g5core__entry-thumbnail')]//img",
            "descricao" => "//div[contains(@class,'g5ere__property-block-description')]"
        ]
    ],
    [
        "nome_site" => "W Imóveis",
        "usuario" => "imoveisunai",
        "cidade" => "Unaí MG",
        "categoria" => "",
        "tags" => "",
        "contato" => "",
        "periodo" => 30,
        "url" => [
            "https://www.wimoveis.com.br/venda/rurais/fazenda/mg/unai"/
        ],
        "numero_registros" => 5,

        "frequencia" => [
            "tipo" => "sempre"
        ],
        "verificar_string" => "",
        "seletores" => [
            "card" => "//div[contains(@class,'.//a[contains(@href, '/propriedades/') and contains(@href, '.html')]/@href')]",
            "card_nome" => ".//h2[contains(@class,'postingLocations-module__location-block')]//span",
            "preco" => ".//div[contains(@class,'postingPrices-module__price')]",
            "card_imagem_url" => ".//img[contains(@class,'is-selected')]",
            "card_url" => ".//a[contains(@class,'g5core__entry-thumbnail')]",
            "galeria" => "//div[contains(@id,'new-gallery-portal')]//img",
            "descricao" => "//div[contains(@id,'longDescription')]"
        ]
    ],
    [
        "nome_site" => "Kenlo",
        "usuario" => "imoveisunai",
        "cidade" => "Unaí MG",
        "categoria" => "",
        "tags" => "",
        "contato" => "",
        "periodo" => 30,
        "url" => [
            "https://portal.kenlo.com.br/imoveis/a-venda/fazenda/unai"/
        ],
        "numero_registros" => 5,

        "frequencia" => [
            "tipo" => "sempre"
        ],
        "verificar_string" => "",
        "seletores" => [
            "card" => "//li[contains(@class,'cards_digital_carousel-item')]",
            "card_nome" => ".//p[contains(@class,'card-with-buttons__title')]//span",
            "preco" => ".//div[contains(@class,'postingPrices-module__price')]",
            "card_imagem_url" => ".//img[contains(@class,'cards_digital_carousel-image')]",
            "card_url" => ".//a[contains(@class,'card-with-buttons')]",
            "galeria" => "//div[contains(@class,'overflow-image-gallery')]//img",
            "descricao" => "//div[contains(@class,'box-description')]"
        ]
    ]
];

/**
 * NORMALIZAR URLS DO SITE
 */
function normalizarUrlsSite($url) {

    if (empty($url)) {
        return [];
    }

    if (is_array($url)) {

        $urls = [];

        foreach ($url as $itemUrl) {

            $itemUrl = trim((string)$itemUrl);

            if ($itemUrl !== "" && !in_array($itemUrl, $urls)) {
                $urls[] = $itemUrl;
            }
        }

        return $urls;
    }

    $url = trim((string)$url);

    if ($url === "") {
        return [];
    }

    return [$url];
}

/**
 * VERIFICA SE O SITE DEVE RODAR AGORA
 */
function deveRodarAgora($frequencia) {

    if (empty($frequencia) || empty($frequencia["tipo"])) {
        return true;
    }

    $tipo = $frequencia["tipo"];

    if ($tipo === "sempre") {
        return true;
    }

    if ($tipo === "horario") {

        $inicio = $frequencia["horario_inicio"] ?? "";
        $fim = $frequencia["horario_fim"] ?? "";

        if (empty($inicio) || empty($fim)) {
            return false;
        }

        $agora = strtotime(date("H:i"));
        $horaInicio = strtotime($inicio);
        $horaFim = strtotime($fim);

        if ($horaInicio <= $horaFim) {
            return ($agora >= $horaInicio && $agora <= $horaFim);
        }

        return ($agora >= $horaInicio || $agora <= $horaFim);
    }

    return true;
}

/**
 * CURL
 */
function getHtml($url) {

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36",
        CURLOPT_TIMEOUT => 40,
        CURLOPT_CONNECTTIMEOUT => 20,
        CURLOPT_ENCODING => "",
        CURLOPT_REFERER => "https://www.google.com/",
        CURLOPT_HTTPHEADER => [
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "Accept-Language: pt-BR,pt;q=0.9,en;q=0.8",
            "Cache-Control: no-cache",
        ],
    ]);

    $html = curl_exec($ch);

    $erro = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $urlFinal = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

    curl_close($ch);

    return [
        "html" => $html,
        "erro" => $erro,
        "http_code" => $httpCode,
        "url_final" => $urlFinal,
        "ok" => ($html && $httpCode >= 200 && $httpCode < 400)
    ];
}

/**
 * LIMPAR TEXTO
 */
function limpar($texto) {
    return trim(
        preg_replace('/\s+/', ' ', strip_tags((string)$texto))
    );
}

/**
 * PEGAR HTML INTERNO DE UM NODE
 */
function getInnerHtml($node) {

    if (!$node) {
        return "";
    }

    $html = "";

    foreach ($node->childNodes as $child) {
        $html .= $node->ownerDocument->saveHTML($child);
    }

    return trim($html);
}

/**
 * NORMALIZA LISTAS SEPARADAS POR VÍRGULA
 */
function normalizarListaVirgula($texto) {

    $texto = limpar($texto);

    if ($texto === "") {
        return "";
    }

    $partes = explode(",", $texto);
    $limpos = [];

    foreach ($partes as $parte) {

        $valor = limpar($parte);

        if ($valor !== "" && !in_array($valor, $limpos)) {
            $limpos[] = $valor;
        }
    }

    return implode(", ", $limpos);
}

/**
 * REMOVER ACENTOS PARA COMPARAÇÃO
 */
function normalizarBusca($texto) {

    $texto = limpar($texto);
    $texto = mb_strtolower($texto, "UTF-8");

    $comAcento = [
        "á", "à", "ã", "â", "ä",
        "é", "è", "ê", "ë",
        "í", "ì", "î", "ï",
        "ó", "ò", "õ", "ô", "ö",
        "ú", "ù", "û", "ü",
        "ç"
    ];

    $semAcento = [
        "a", "a", "a", "a", "a",
        "e", "e", "e", "e",
        "i", "i", "i", "i",
        "o", "o", "o", "o", "o",
        "u", "u", "u", "u",
        "c"
    ];

    return str_replace($comAcento, $semAcento, $texto);
}

/**
 * NORMALIZAR PREÇO
 *
 * Exemplos:
 * R$ 1.200,00      => 1200
 * R$ 850.000,00    => 850000
 * 1.500,50         => 1500
 * R$ 2.000         => 2000
 * 180 mil          => 180000
 * R$ 180 mil       => 180000
 * 1,2 milhão       => 1200000
 * 1.2 milhão       => 1200000
 */
function normalizarPrecoInteiro($preco) {

    $precoOriginal = limpar($preco);

    if ($precoOriginal === "") {
        return "";
    }

    $precoBusca = normalizarBusca($precoOriginal);

    /**
     * CASO: "180 mil", "R$ 180 mil", "850 mil"
     */
    if (preg_match('/(\d+(?:[.,]\d+)?)\s*mil\b/i', $precoBusca, $match)) {

        $numero = str_replace(",", ".", $match[1]);
        $valor = (float)$numero * 1000;

        return (string)(int)round($valor);
    }

    /**
     * CASO: "1,2 milhão", "1.2 milhao", "2 milhões"
     */
    if (preg_match('/(\d+(?:[.,]\d+)?)\s*(milhao|milhoes)\b/i', $precoBusca, $match)) {

        $numero = str_replace(",", ".", $match[1]);
        $valor = (float)$numero * 1000000;

        return (string)(int)round($valor);
    }

    /**
     * CASO PADRÃO:
     * R$ 1.200,00 => 1200
     */
    $preco = preg_replace('/[^\d,\.]/', '', $precoOriginal);

    if ($preco === "") {
        return "";
    }

    if (strpos($preco, ",") !== false) {
        $partes = explode(",", $preco);
        $preco = $partes[0];
    }

    $preco = str_replace(".", "", $preco);
    $preco = preg_replace('/\D/', '', $preco);

    return $preco;
}

/**
 * GERAR DATA FUTURA EM FORMATO AMERICANO
 */
function gerarDataPeriodoEua($periodo) {

    $periodo = (int)$periodo;

    if ($periodo <= 0) {
        return "";
    }

    return date("Y-m-d", strtotime("+" . $periodo . " days"));
}

/**
 * VERIFICAÇÃO OPCIONAL POR STRING
 */
function deveSalvarPorString($cardNome, $verificarString) {

    $verificarString = limpar($verificarString ?? "");

    if ($verificarString === "") {
        return true;
    }

    $listaStrings = explode(",", $verificarString);

    foreach ($listaStrings as $string) {

        $string = limpar($string);

        if ($string === "") {
            continue;
        }

        if (mb_stripos($cardNome, $string, 0, "UTF-8") !== false) {
            return true;
        }
    }

    return false;
}

/**
 * DEFINIR CATEGORIA DO IMÓVEL PELO CARD_NOME
 */
function definirCategoriaImovel($cardNome, $regrasCategoriaImovel) {

    if (empty($regrasCategoriaImovel) || !is_array($regrasCategoriaImovel)) {
        return "";
    }

    $cardNomeBusca = normalizarBusca($cardNome);
    $categoriaPadrao = "";

    foreach ($regrasCategoriaImovel as $regra) {

        $categoria = limpar($regra["categoria"] ?? "");
        $strings = limpar($regra["strings"] ?? "");

        if ($categoria === "") {
            continue;
        }

        if ($strings === "") {
            if ($categoriaPadrao === "") {
                $categoriaPadrao = $categoria;
            }

            continue;
        }

        $listaStrings = explode(",", $strings);

        foreach ($listaStrings as $string) {

            $stringBusca = normalizarBusca($string);

            if ($stringBusca === "") {
                continue;
            }

            if (mb_stripos($cardNomeBusca, $stringBusca, 0, "UTF-8") !== false) {
                return $categoria;
            }
        }
    }

    return $categoriaPadrao;
}

/**
 * DEFINIR STATUS DO IMÓVEL
 *
 * Verifica card_nome + descricao.
 */
function definirStatusImovel($cardNome, $descricao, $regrasStatusImovel) {

    if (empty($regrasStatusImovel) || !is_array($regrasStatusImovel)) {
        return "";
    }

    $textoBusca = normalizarBusca($cardNome . " " . $descricao);
    $statusPadrao = "";

    foreach ($regrasStatusImovel as $regra) {

        $status = limpar($regra["status"] ?? "");
        $strings = limpar($regra["strings"] ?? "");

        if ($status === "") {
            continue;
        }

        if ($strings === "") {
            if ($statusPadrao === "") {
                $statusPadrao = $status;
            }

            continue;
        }

        $listaStrings = explode(",", $strings);

        foreach ($listaStrings as $string) {

            $stringBusca = normalizarBusca($string);

            if ($stringBusca === "") {
                continue;
            }

            if (mb_stripos($textoBusca, $stringBusca, 0, "UTF-8") !== false) {
                return $status;
            }
        }
    }

    return $statusPadrao;
}

/**
 * CRIAR DOM XPATH
 */
function criarXpath($html) {

    libxml_use_internal_errors(true);

    $dom = new DOMDocument();

    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);

    libxml_clear_errors();

    return new DOMXPath($dom);
}

/**
 * TRANSFORMAR URL RELATIVA EM ABSOLUTA
 */
function urlAbsoluta($url, $base) {

    $url = trim((string)$url);

    if ($url === "") {
        return "";
    }

    if (preg_match('/^https?:\/\//i', $url)) {
        return $url;
    }

    $partes = parse_url($base);

    if (empty($partes["scheme"]) || empty($partes["host"])) {
        return $url;
    }

    if (strpos($url, "//") === 0) {
        return $partes["scheme"] . ":" . $url;
    }

    $dominio = $partes["scheme"] . "://" . $partes["host"];

    if (strpos($url, "/") === 0) {
        return $dominio . $url;
    }

    $path = isset($partes["path"]) ? dirname($partes["path"]) : "";

    return rtrim($dominio . "/" . trim($path, "/"), "/") . "/" . ltrim($url, "/");
}

/**
 * PEGAR URL DO ATRIBUTO STYLE
 *
 * Exemplo:
 * style="background-image: url(https://site.com/imagem.jpg)"
 */
function getUrlFromStyle($style) {

    $style = trim((string)$style);

    if ($style === "") {
        return "";
    }

    if (preg_match('/url\((["\']?)(.*?)\1\)/i', $style, $match)) {
        return trim($match[2]);
    }

    return "";
}

/**
 * PEGAR ATRIBUTO COM FALLBACK
 */
function getAtributoFallback($node, $atributos) {

    if (!$node) {
        return "";
    }

    foreach ($atributos as $attr) {

        if ($attr === "style") {

            $style = trim($node->getAttribute("style"));
            $urlStyle = getUrlFromStyle($style);

            if ($urlStyle !== "") {
                return $urlStyle;
            }

            continue;
        }

        $valor = trim($node->getAttribute($attr));

        if ($valor !== "") {

            if ($attr === "srcset" || $attr === "data-srcset") {
                $partes = explode(",", $valor);
                $valor = trim(explode(" ", trim($partes[0]))[0]);
            }

            return $valor;
        }
    }

    return "";
}

/**
 * PEGAR TEXTO PELO SELETOR
 */
function getTextoSeletor($xpath, $contexto, $seletor) {

    if (empty($seletor)) {
        return "";
    }

    $node = $xpath->query($seletor, $contexto);

    if ($node && $node->length > 0) {
        return limpar($node->item(0)->textContent);
    }

    return "";
}

/**
 * PEGAR URL PELO SELETOR
 */
function getUrlSeletor($xpath, $contexto, $seletor, $baseUrl) {

    if (empty($seletor)) {
        return "";
    }

    $node = $xpath->query($seletor, $contexto);

    if (!$node || $node->length === 0) {
        return "";
    }

    $url = getAtributoFallback($node->item(0), [
        "href",
        "src",
        "data-src",
        "data-lazy-src",
        "data-original",
        "data-full",
        "data-image",
        "data-large",
        "srcset",
        "data-srcset",
        "style"
    ]);

    return urlAbsoluta($url, $baseUrl);
}

/**
 * PEGAR META CONTENT
 */
function getMetaContent($xpath, $queries) {

    foreach ($queries as $query) {

        $node = $xpath->query($query);

        if ($node && $node->length > 0) {

            $content = limpar($node->item(0)->getAttribute("content"));

            if ($content !== "") {
                return $content;
            }
        }
    }

    return "";
}

/**
 * PEGAR OG, DESCRIÇÃO E GALERIA DA URL DO CARD
 */
function getDadosInternos($urlCard, $selectorGaleria = "", $selectorDescricao = "") {

    $dados = [
        "og_title" => "",
        "og_image" => "",
        "og_description" => "",
        "og_status" => "",
        "galeria" => "",
        "descricao" => ""
    ];

    if (empty($urlCard)) {
        $dados["og_status"] = "sem_card_url";
        return $dados;
    }

    $resposta = getHtml($urlCard);

    if (!$resposta["ok"]) {

        $dados["og_status"] = "erro_http_" . $resposta["http_code"];

        if (!empty($resposta["erro"])) {
            $dados["og_status"] .= " - " . $resposta["erro"];
        }

        return $dados;
    }

    $xpath = criarXpath($resposta["html"]);

    $dados["og_title"] = getMetaContent($xpath, [
        "//meta[@property='og:title']",
        "//meta[@name='twitter:title']"
    ]);

    if ($dados["og_title"] === "") {

        $titleNode = $xpath->query("//title");

        if ($titleNode && $titleNode->length > 0) {
            $dados["og_title"] = limpar($titleNode->item(0)->textContent);
        }
    }

    $dados["og_image"] = getMetaContent($xpath, [
        "//meta[@property='og:image']",
        "//meta[@property='og:image:url']",
        "//meta[@name='twitter:image']"
    ]);

    if ($dados["og_image"] !== "") {
        $dados["og_image"] = urlAbsoluta($dados["og_image"], $urlCard);
    }

    $dados["og_description"] = getMetaContent($xpath, [
        "//meta[@property='og:description']",
        "//meta[@name='description']",
        "//meta[@name='twitter:description']"
    ]);

    /**
     * DESCRIÇÃO INTERNA DO IMÓVEL COM HTML INTERNO
     */
    if (!empty($selectorDescricao)) {

        $descricaoNode = $xpath->query($selectorDescricao);

        if ($descricaoNode && $descricaoNode->length > 0) {
            $dados["descricao"] = getInnerHtml($descricaoNode->item(0));
        }
    }

    /**
     * GALERIA DE IMAGENS
     */
    if (!empty($selectorGaleria)) {

        $imagens = [];

        $nodesGaleria = $xpath->query($selectorGaleria);

        if ($nodesGaleria && $nodesGaleria->length > 0) {

            foreach ($nodesGaleria as $imgNode) {

                $imgUrl = getAtributoFallback($imgNode, [
                    "src",
                    "data-src",
                    "data-lazy-src",
                    "data-original",
                    "data-full",
                    "data-image",
                    "data-large",
                    "href",
                    "srcset",
                    "data-srcset",
                    "style"
                ]);

                $imgUrl = urlAbsoluta($imgUrl, $urlCard);

                if (!empty($imgUrl) && !in_array($imgUrl, $imagens)) {
                    $imagens[] = $imgUrl;
                }
            }
        }

        if (!empty($imagens)) {
            $dados["galeria"] = implode(",", $imagens);
        }
    }

    $dados["og_status"] = "ok";

    return $dados;
}

/**
 * GERAR CHAVE ÚNICA DO REGISTRO
 */
function gerarChaveRegistro($item) {

    $cardUrl = trim($item["card_url"] ?? "");

    if ($cardUrl !== "") {
        return md5(mb_strtolower($cardUrl, "UTF-8"));
    }

    return md5(
        mb_strtolower(
            ($item["nome_site"] ?? "") . "|" .
            ($item["card_nome"] ?? "") . "|" .
            ($item["preco"] ?? ""),
            "UTF-8"
        )
    );
}

/**
 * LER CSV EXISTENTE
 */
function lerCsvExistente($arquivoCsv, $colunas) {

    $registros = [];

    if (!file_exists($arquivoCsv)) {
        return $registros;
    }

    $fp = fopen($arquivoCsv, "r");

    if (!$fp) {
        return $registros;
    }

    $cabecalho = fgetcsv($fp, 0, ";");

    if (!$cabecalho) {
        fclose($fp);
        return $registros;
    }

    if (isset($cabecalho[0])) {
        $cabecalho[0] = preg_replace('/^\xEF\xBB\xBF/', '', $cabecalho[0]);
    }

    while (($linha = fgetcsv($fp, 0, ";")) !== false) {

        $item = [];

        foreach ($colunas as $index => $coluna) {
            $item[$coluna] = $linha[$index] ?? "";
        }

        $registros[] = $item;
    }

    fclose($fp);

    return $registros;
}

/**
 * MESCLAR REGISTROS SEM DUPLICAR E LIMITAR TOTAL
 */
function mesclarRegistrosLimitados($registrosAntigos, $registrosNovos, $limite) {

    $resultado = [];

    foreach ($registrosNovos as $item) {
        $chave = gerarChaveRegistro($item);
        $resultado[$chave] = $item;
    }

    foreach ($registrosAntigos as $item) {
        $chave = gerarChaveRegistro($item);

        if (!isset($resultado[$chave])) {
            $resultado[$chave] = $item;
        }
    }

    return array_slice(array_values($resultado), 0, $limite);
}

/**
 * PROCESSAMENTO
 */
$resultados = [];
$logs = [];

foreach ($sites as $site) {

    $nomeSite = $site["nome_site"] ?? "";
    $usuario = $site["usuario"] ?? "";
    $cidade = $site["cidade"] ?? "";

    $categoria = normalizarListaVirgula($site["categoria"] ?? "");
    $tags = normalizarListaVirgula($site["tags"] ?? "");

    $contato = $site["contato"] ?? "";

    $periodo = (int)($site["periodo"] ?? 0);
    $dataPeriodoEua = gerarDataPeriodoEua($periodo);

    $urlsSite = normalizarUrlsSite($site["url"] ?? "");
    $urlPrincipal = $urlsSite[0] ?? "";

    $numeroRegistros = (int)($site["numero_registros"] ?? 0);
    $seletores = $site["seletores"] ?? [];

    $frequencia = $site["frequencia"] ?? [
        "tipo" => "sempre"
    ];

    $verificarString = $site["verificar_string"] ?? "";

    if (!deveRodarAgora($frequencia)) {

        $logs[] = [
            "nome_site" => $nomeSite,
            "usuario" => $usuario,
            "cidade" => $cidade,
            "categoria" => $categoria,
            "tags" => $tags,
            "url" => $urlPrincipal,
            "status" => "ignorado_por_frequencia",
            "horario_atual" => date("H:i")
        ];

        continue;
    }

    if (empty($urlsSite)) {

        $logs[] = [
            "nome_site" => $nomeSite,
            "usuario" => $usuario,
            "cidade" => $cidade,
            "categoria" => $categoria,
            "tags" => $tags,
            "url" => "",
            "status" => "url_vazia"
        ];

        continue;
    }

    $contador = 0;
    $ignoradosPorString = 0;
    $cardsEncontradosTotal = 0;

    foreach ($urlsSite as $url) {

        $resposta = getHtml($url);

        if (!$resposta["ok"]) {

            $logs[] = [
                "nome_site" => $nomeSite,
                "usuario" => $usuario,
                "cidade" => $cidade,
                "categoria" => $categoria,
                "tags" => $tags,
                "url" => $url,
                "status" => "erro_http",
                "http_code" => $resposta["http_code"],
                "erro" => $resposta["erro"]
            ];

            continue;
        }

        $xpath = criarXpath($resposta["html"]);

        $selectorCard = $seletores["card"] ?? "";

        if (empty($selectorCard)) {

            $logs[] = [
                "nome_site" => $nomeSite,
                "usuario" => $usuario,
                "cidade" => $cidade,
                "categoria" => $categoria,
                "tags" => $tags,
                "url" => $url,
                "status" => "selector_card_vazio"
            ];

            continue;
        }

        $cards = $xpath->query($selectorCard);

        if (!$cards || $cards->length === 0) {

            $logs[] = [
                "nome_site" => $nomeSite,
                "usuario" => $usuario,
                "cidade" => $cidade,
                "categoria" => $categoria,
                "tags" => $tags,
                "url" => $url,
                "status" => "sem_cards"
            ];

            continue;
        }

        $cardsEncontradosTotal += $cards->length;

        foreach ($cards as $card) {

            if ($numeroRegistros > 0 && $contador >= $numeroRegistros) {
                break 2;
            }

            $cardNome = getTextoSeletor(
                $xpath,
                $card,
                $seletores["card_nome"] ?? ""
            );

            $categoriaImovel = definirCategoriaImovel(
                $cardNome,
                $categoriaImovelRegras
            );

            $precoOriginal = getTextoSeletor(
                $xpath,
                $card,
                $seletores["preco"] ?? ""
            );

            $preco = normalizarPrecoInteiro($precoOriginal);

            $cardImagemUrl = getUrlSeletor(
                $xpath,
                $card,
                $seletores["card_imagem_url"] ?? "",
                $url
            );

            $cardUrl = getUrlSeletor(
                $xpath,
                $card,
                $seletores["card_url"] ?? "",
                $url
            );

            if (empty($cardNome) && empty($cardUrl)) {
                continue;
            }

            if (!deveSalvarPorString($cardNome, $verificarString)) {
                $ignoradosPorString++;
                continue;
            }

            $dadosInternos = getDadosInternos(
                $cardUrl,
                $seletores["galeria"] ?? "",
                $seletores["descricao"] ?? ""
            );

            $galeria = $dadosInternos["galeria"];

            if (empty($galeria)) {
                $galeria = $cardImagemUrl;
            }

            $descricao = $dadosInternos["descricao"] ?? "";

            $statusImovel = definirStatusImovel(
                $cardNome,
                $descricao,
                $StatusImovelRegras
            );

            $hash = md5(
                mb_strtolower(
                    $nomeSite . "|" .
                    $usuario . "|" .
                    $cidade . "|" .
                    $categoria . "|" .
                    $tags . "|" .
                    $categoriaImovel . "|" .
                    $statusImovel . "|" .
                    $contato . "|" .
                    $periodo . "|" .
                    $cardNome . "|" .
                    $preco . "|" .
                    $cardUrl,
                    "UTF-8"
                )
            );

            if (isset($resultados[$hash])) {
                continue;
            }

            $resultados[$hash] = [
                "nome_site" => $nomeSite,
                "usuario" => $usuario,
                "cidade" => $cidade,
                "categoria" => $categoria,
                "tags" => $tags,
                "categoria_imovel" => $categoriaImovel,
                "status_imovel" => $statusImovel,

                "contato" => $contato,

                "data_periodo_eua" => $dataPeriodoEua,

                "url" => $url,

                "card_nome" => $cardNome,
                "descricao" => $descricao,
                "preco" => $preco,
                "card_imagem_url" => $cardImagemUrl,
                "card_url" => $cardUrl,

                "og_title" => $dadosInternos["og_title"],
                "og_image" => $dadosInternos["og_image"],
                "og_description" => $dadosInternos["og_description"],
                "og_status" => $dadosInternos["og_status"],
                "galeria" => $galeria,

                "data_scraper_brasil" => date("d/m/Y H:i:s"),
                "data_scraper_eua" => date("Y-m-d H:i:s")
            ];

            $contador++;

            usleep(rand(400000, 1200000));
        }
    }

    $logs[] = [
        "nome_site" => $nomeSite,
        "usuario" => $usuario,
        "cidade" => $cidade,
        "categoria" => $categoria,
        "tags" => $tags,
        "url" => $urlPrincipal,
        "status" => "ok",
        "cards_encontrados" => $cardsEncontradosTotal,
        "registros_salvos" => $contador,
        "ignorados_por_string" => $ignoradosPorString
    ];
}

/**
 * COLUNAS DO CSV
 */
$colunas = [
    "nome_site",
    "usuario",
    "cidade",
    "categoria",
    "tags",
    "categoria_imovel",
    "status_imovel",

    "contato",
    "data_periodo_eua",

    "url",

    "card_nome",
    "descricao",
    "preco",
    "card_imagem_url",
    "card_url",

    "og_title",
    "og_image",
    "og_description",
    "og_status",
    "galeria",

    "data_scraper_brasil",
    "data_scraper_eua"
];

$registrosAntigos = lerCsvExistente($arquivoCsv, $colunas);

$registrosFinais = mesclarRegistrosLimitados(
    $registrosAntigos,
    array_values($resultados),
    $limiteRegistrosCsv
);

/**
 * SALVAR CSV
 */
$fp = fopen($arquivoCsv, "w");

if (!$fp) {
    header("Content-Type: application/json; charset=utf-8");

    echo json_encode([
        "status" => "error",
        "mensagem" => "Não foi possível criar o arquivo CSV.",
        "arquivo_csv" => $arquivoCsv
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    exit;
}

fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($fp, $colunas, ";");

foreach ($registrosFinais as $item) {

    $linha = [];

    foreach ($colunas as $coluna) {
        $linha[] = $item[$coluna] ?? "";
    }

    fputcsv($fp, $linha, ";");
}

fclose($fp);

/**
 * RETORNO JSON
 */
header("Content-Type: application/json; charset=utf-8");

echo json_encode([
    "status" => "success",
    "arquivo_csv" => $arquivoCsv,
    "data_execucao" => date("d/m/Y H:i:s"),
    "horario_atual" => date("H:i"),
    "total_sites" => count($sites),
    "total_resultados_novos" => count($resultados),
    "total_resultados_csv" => count($registrosFinais),
    "limite_registros_csv" => $limiteRegistrosCsv,
    "logs" => $logs,
    "resultado" => array_values($resultados)
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

exit;