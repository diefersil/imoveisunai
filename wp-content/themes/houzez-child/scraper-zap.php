<?php

ini_set('max_execution_time', 2000);
set_time_limit(2000);

date_default_timezone_set("America/Sao_Paulo");

$arquivoCsv = "resultado_scraper.csv";
$limiteRegistrosCsv = 100;

/**
 * REGRA GLOBAL DE CATEGORIA DO IMÓVEL
 *
 * Vale para todos os sites.
 */
$categoriaImovelRegras = [
    [
        "categoria" => "Casas",
        "strings" => "casa, sobrado, meia agua, meia água, casas, mansao, mansão"
    ],
    [
        "categoria" => "Fazendas",
        "strings" => "fazenda, fazendas, rural, chácara, chacara, sítio, sitio"
    ],
    [
        "categoria" => "Lotes",
        "strings" => "lote, lotes, terreno, terrenos"
    ],
    [
        "categoria" => "Apartamentos",
        "strings" => "apartamento, apartamentos, apto"
    ],
    [
        "categoria" => "Imóvel Urbano",
        "strings" => ""
    ]
];

/**
 * CONFIGURAÇÃO DOS SITES
 */
$sites = [
    [
        "nome_site" => "Imobiliária Exemplo",
        "usuario" => "Diego",
        "cidade" => "Unaí MG",

        // Pode ter várias categorias separadas por vírgula
        "categoria" => "Imóveis, Casas, Venda",

        // Pode ter várias tags separadas por vírgula
        "tags" => "casa, imóvel, venda, Unaí, oportunidade",

        "meta1" => "Venda",
        "meta2" => "Casa",
        "meta3" => "",
        "meta4" => "",

        // Entrada em dias. Na saída vira data_periodo_eua.
        "periodo" => 30,

        "url" => "https://www.exemplo.com.br/imoveis/",
        "numero_registros" => 100,

        /**
         * Frequência:
         *
         * Rodar sempre:
         * "frequencia" => [
         *     "tipo" => "sempre"
         * ],
         *
         * Rodar entre horários:
         * "frequencia" => [
         *     "tipo" => "horario",
         *     "horario_inicio" => "08:00",
         *     "horario_fim" => "18:00"
         * ],
         */
        "frequencia" => [
            "tipo" => "sempre"
        ],

        /**
         * Verificação opcional por string no card_nome.
         *
         * Se estiver vazio ou não existir, salva todos.
         * Se estiver preenchido, salva somente se o card_nome contiver
         * pelo menos uma das strings separadas por vírgula.
         */
        "verificar_string" => "Unaí, Paracatu, João Pinheiro",

        "seletores" => [
            "card" => "//div[contains(@class,'imovel')]",
            "card_nome" => ".//h2",
            "preco" => ".//*[contains(@class,'preco') or contains(@class,'price')]",
            "card_imagem_url" => ".//img",
            "card_url" => ".//a",

            // Seletor usado dentro da página interna do card_url
            "galeria" => ".//div[contains(@class,'gallery') or contains(@class,'galeria')]//img"
        ]
    ],

    /*
    [
        "nome_site" => "Outro Site",
        "usuario" => "Diego",
        "cidade" => "Paracatu MG",
        "categoria" => "Imóveis, Apartamentos, Aluguel",
        "tags" => "apartamento, aluguel, Paracatu",

        "meta1" => "Aluguel",
        "meta2" => "Apartamento",
        "meta3" => "",
        "meta4" => "",

        "periodo" => 15,

        "url" => "https://www.outrosite.com.br/imoveis/",
        "numero_registros" => 50,

        "frequencia" => [
            "tipo" => "horario",
            "horario_inicio" => "08:00",
            "horario_fim" => "18:00"
        ],

        "verificar_string" => "",

        "seletores" => [
            "card" => "//div[contains(@class,'card-imovel')]",
            "card_nome" => ".//h2",
            "preco" => ".//*[contains(@class,'valor')]",
            "card_imagem_url" => ".//img",
            "card_url" => ".//a",
            "galeria" => ".//img[contains(@src,'uploads') or contains(@data-src,'uploads') or contains(@data-lazy-src,'uploads')]"
        ]
    ],
    */
];

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

        // Exemplo: 08:00 até 18:00
        if ($horaInicio <= $horaFim) {
            return ($agora >= $horaInicio && $agora <= $horaFim);
        }

        // Exemplo: 22:00 até 06:00
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
 * NORMALIZAR PREÇO
 *
 * Exemplos:
 * R$ 1.200,00   => 1200
 * R$ 850.000,00 => 850000
 * 1.500,50      => 1500
 * R$ 2.000      => 2000
 */
function normalizarPrecoInteiro($preco) {

    $preco = limpar($preco);

    if ($preco === "") {
        return "";
    }

    // Remove símbolo, letras e espaços, mantendo apenas números, ponto e vírgula
    $preco = preg_replace('/[^\d,\.]/', '', $preco);

    if ($preco === "") {
        return "";
    }

    // Se tiver vírgula, considera o que vem depois como centavos
    if (strpos($preco, ",") !== false) {
        $partes = explode(",", $preco);
        $preco = $partes[0];
    }

    // Remove pontos de milhar
    $preco = str_replace(".", "", $preco);

    // Garante apenas números
    $preco = preg_replace('/\D/', '', $preco);

    return $preco;
}

/**
 * GERAR DATA FUTURA EM FORMATO AMERICANO
 *
 * Exemplo:
 * periodo = 30
 * saída = YYYY-MM-DD
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
 *
 * Se verificar_string estiver vazia ou não existir:
 * salva todos.
 *
 * Se verificar_string tiver valores separados por vírgula:
 * salva somente se card_nome contém pelo menos uma string.
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
 * DEFINIR CATEGORIA DO IMÓVEL PELO CARD_NOME
 *
 * A primeira categoria encontrada será retornada.
 * Se nenhuma string bater, retorna a primeira categoria com strings vazia.
 * Se não tiver categoria padrão, retorna vazio.
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

        // Se strings estiver vazio, guarda como categoria padrão
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
 * PEGAR ATRIBUTO COM FALLBACK
 */
function getAtributoFallback($node, $atributos) {

    if (!$node) {
        return "";
    }

    foreach ($atributos as $attr) {

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
        "srcset",
        "data-srcset"
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
 * PEGAR OG E GALERIA DA URL DO CARD
 */
function getDadosInternos($urlCard, $selectorGaleria = "") {

    $dados = [
        "og_title" => "",
        "og_image" => "",
        "og_description" => "",
        "og_status" => "",
        "galeria" => ""
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

    /**
     * OG TITLE
     */
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

    /**
     * OG IMAGE
     */
    $dados["og_image"] = getMetaContent($xpath, [
        "//meta[@property='og:image']",
        "//meta[@property='og:image:url']",
        "//meta[@name='twitter:image']"
    ]);

    if ($dados["og_image"] !== "") {
        $dados["og_image"] = urlAbsoluta($dados["og_image"], $urlCard);
    }

    /**
     * OG DESCRIPTION
     */
    $dados["og_description"] = getMetaContent($xpath, [
        "//meta[@property='og:description']",
        "//meta[@name='description']",
        "//meta[@name='twitter:description']"
    ]);

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
                    "data-srcset"
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

    // Remove BOM UTF-8 da primeira coluna, se existir
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

    // Primeiro coloca os novos. Assim, se houver duplicado, o novo prevalece.
    foreach ($registrosNovos as $item) {
        $chave = gerarChaveRegistro($item);
        $resultado[$chave] = $item;
    }

    // Depois adiciona os antigos que ainda não existem.
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

    $meta1 = $site["meta1"] ?? "";
    $meta2 = $site["meta2"] ?? "";
    $meta3 = $site["meta3"] ?? "";
    $meta4 = $site["meta4"] ?? "";

    $periodo = (int)($site["periodo"] ?? 0);
    $dataPeriodoEua = gerarDataPeriodoEua($periodo);

    $url = $site["url"] ?? "";
    $numeroRegistros = (int)($site["numero_registros"] ?? 0);
    $seletores = $site["seletores"] ?? [];

    $frequencia = $site["frequencia"] ?? [
        "tipo" => "sempre"
    ];

    $verificarString = $site["verificar_string"] ?? "";

    /**
     * VERIFICA FREQUÊNCIA DO SITE
     */
    if (!deveRodarAgora($frequencia)) {

        $logs[] = [
            "nome_site" => $nomeSite,
            "usuario" => $usuario,
            "cidade" => $cidade,
            "categoria" => $categoria,
            "tags" => $tags,
            "url" => $url,
            "status" => "ignorado_por_frequencia",
            "horario_atual" => date("H:i")
        ];

        continue;
    }

    if (empty($url)) {

        $logs[] = [
            "nome_site" => $nomeSite,
            "usuario" => $usuario,
            "cidade" => $cidade,
            "categoria" => $categoria,
            "tags" => $tags,
            "url" => $url,
            "status" => "url_vazia"
        ];

        continue;
    }

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

    $contador = 0;
    $ignoradosPorString = 0;

    foreach ($cards as $card) {

        if ($numeroRegistros > 0 && $contador >= $numeroRegistros) {
            break;
        }

        /**
         * DADOS DO CARD
         */
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

        /**
         * IGNORA CARD VAZIO
         */
        if (empty($cardNome) && empty($cardUrl)) {
            continue;
        }

        /**
         * VERIFICAÇÃO OPCIONAL POR STRING
         */
        if (!deveSalvarPorString($cardNome, $verificarString)) {
            $ignoradosPorString++;
            continue;
        }

        /**
         * DADOS INTERNOS:
         * OG TITLE, OG IMAGE, OG DESCRIPTION E GALERIA
         */
        $dadosInternos = getDadosInternos(
            $cardUrl,
            $seletores["galeria"] ?? ""
        );

        /**
         * EVITA DUPLICADOS NA EXECUÇÃO ATUAL
         */
        $hash = md5(
            mb_strtolower(
                $nomeSite . "|" .
                $usuario . "|" .
                $cidade . "|" .
                $categoria . "|" .
                $tags . "|" .
                $categoriaImovel . "|" .
                $meta1 . "|" .
                $meta2 . "|" .
                $meta3 . "|" .
                $meta4 . "|" .
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

            "meta1" => $meta1,
            "meta2" => $meta2,
            "meta3" => $meta3,
            "meta4" => $meta4,

            "data_periodo_eua" => $dataPeriodoEua,

            "url" => $url,

            "card_nome" => $cardNome,
            "preco" => $preco,
            "card_imagem_url" => $cardImagemUrl,
            "card_url" => $cardUrl,

            "og_title" => $dadosInternos["og_title"],
            "og_image" => $dadosInternos["og_image"],
            "og_description" => $dadosInternos["og_description"],
            "og_status" => $dadosInternos["og_status"],
            "galeria" => $dadosInternos["galeria"],

            "data_scraper_brasil" => date("d/m/Y H:i:s"),
            "data_scraper_eua" => date("Y-m-d H:i:s")
        ];

        $contador++;

        usleep(rand(400000, 1200000));
    }

    $logs[] = [
        "nome_site" => $nomeSite,
        "usuario" => $usuario,
        "cidade" => $cidade,
        "categoria" => $categoria,
        "tags" => $tags,
        "url" => $url,
        "status" => "ok",
        "cards_encontrados" => $cards->length,
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

    "meta1",
    "meta2",
    "meta3",
    "meta4",
    "data_periodo_eua",

    "url",

    "card_nome",
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

/**
 * LER ANTIGOS + MESCLAR COM NOVOS + LIMITAR 100
 */
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

// BOM UTF-8 para Excel
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