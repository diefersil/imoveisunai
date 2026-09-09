<?php

/**
 * FUNÇÕES DE E-MAIL DO SCRAPER
 *
 * Este arquivo deve ser chamado pelo scraper.php.
 *
 * IMPORTANTE:
 * A função antiga que enviava o e-mail individual com a mensagem
 * "E-mail individual do scraper desativado." foi removida daqui.
 *
 * O e-mail de novo imóvel deve ficar somente no hook do WordPress,
 * no arquivo imu-email-post-imoveis.php, quando um post do tipo
 * imoveis for publicado.
 */

/**
 * LIMPAR TEXTO PARA O E-MAIL
 */
function scraperEmailLimparTexto($texto) {

    if (function_exists("limpar")) {
        return limpar($texto);
    }

    return trim(
        preg_replace('/\s+/', ' ', strip_tags((string)$texto))
    );
}

/**
 * NORMALIZAR TEXTO PARA COMPARAÇÃO
 */
function scraperEmailNormalizarBusca($texto) {

    if (function_exists("normalizarBusca")) {
        return normalizarBusca($texto);
    }

    $texto = scraperEmailLimparTexto($texto);
    $texto = strtolower($texto);

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
 * CARREGAR WORDPRESS PARA USAR WP_MAIL
 *
 * Se o scraper estiver rodando fora do WordPress, tenta carregar wp-load.php
 * usando a raiz já detectada pelo scraper.php.
 */
function carregarWordPressParaEmailScraper() {

    if (function_exists("wp_mail")) {
        return true;
    }

    $possiveisCaminhos = [];

    if (!empty($GLOBALS["raizWordPress"])) {
        $possiveisCaminhos[] = rtrim((string)$GLOBALS["raizWordPress"], "/\\") . "/wp-load.php";
    }

    $possiveisCaminhos[] = __DIR__ . "/wp-load.php";
    $possiveisCaminhos[] = dirname(__DIR__) . "/wp-load.php";
    $possiveisCaminhos[] = dirname(__DIR__, 2) . "/wp-load.php";
    $possiveisCaminhos[] = dirname(__DIR__, 3) . "/wp-load.php";

    foreach ($possiveisCaminhos as $wpLoad) {

        if (is_string($wpLoad) && file_exists($wpLoad)) {
            require_once $wpLoad;
            break;
        }
    }

    return function_exists("wp_mail");
}

/**
 * ENVIAR E-MAIL DO SCRAPER
 */
function enviarEmailScraper($emailDestino, $assunto, $mensagem, $headersArray) {

    carregarWordPressParaEmailScraper();

    $assuntoEmail = $assunto;

    if (function_exists("mb_encode_mimeheader")) {
        $assuntoEmail = mb_encode_mimeheader($assunto, "UTF-8", "B", "\r\n");
    }

    if (function_exists("wp_mail")) {
        return wp_mail($emailDestino, $assuntoEmail, $mensagem, $headersArray);
    }

    return @mail($emailDestino, $assuntoEmail, $mensagem, implode("\r\n", $headersArray));
}

/**
 * DEFINIR E-MAIL REMETENTE DA NOTIFICAÇÃO
 */
function getEmailRemetenteNotificacaoNovoImovel($item) {

    $urlBase = trim((string)($item["contato_site"] ?? ""));

    if ($urlBase === "") {
        $urlBase = trim((string)($item["card_url"] ?? ""));
    }

    $host = "";

    if ($urlBase !== "") {
        $host = parse_url($urlBase, PHP_URL_HOST) ?: "";
    }

    if ($host === "" && function_exists("home_url")) {
        $host = parse_url(home_url("/"), PHP_URL_HOST) ?: "";
    }

    if ($host === "" && !empty($_SERVER["HTTP_HOST"])) {
        $host = $_SERVER["HTTP_HOST"];
    }

    $host = preg_replace('/^www\./i', '', trim((string)$host));
    $host = preg_replace('/[^a-z0-9.-]/i', '', $host);

    if ($host === "") {
        $host = "localhost.local";
    }

    return "nao-responda@" . $host;
}


/**
 * COMPATIBILIDADE: FILTRAR IMÓVEIS NOVOS DO SCRAPER
 *
 * Mantida somente para evitar erro caso algum scraper antigo ainda chame.
 * Não dispara e-mail. O e-mail de novo imóvel deve ser enviado apenas pelo hook
 * do WordPress quando um post do tipo "imoveis" for publicado.
 */
function filtrarImoveisNovosCadastrados($registrosAntigos, $registrosFinais) {
    return [];
}

/**
 * COMPATIBILIDADE: E-MAIL INDIVIDUAL DE IMÓVEL NOVO NO SCRAPER DESATIVADO
 *
 * Esta função não envia mais e-mail do scraper.
 * Use o hook do WordPress para novo post_type "imoveis".
 */
function enviarEmailsNovosImoveisCadastrados($imoveisNovos, $emailDestino) {
    $logsEmail = [];

    if (!empty($imoveisNovos) && is_array($imoveisNovos)) {
        foreach ($imoveisNovos as $item) {
            $logsEmail[] = [
                "status" => "nao_enviado",
                "motivo" => "email_individual_do_scraper_desativado_usar_hook_post_imoveis",
                "card_nome" => is_array($item) ? ($item["card_nome"] ?? "") : "",
                "card_url" => is_array($item) ? ($item["card_url"] ?? "") : "",
                "data" => date("d/m/Y H:i:s")
            ];
        }
    }

    return $logsEmail;
}

/**
 * CONTAR STATUS DOS LOGS DO SCRAPER
 */
function contarStatusLogsScraper($logs) {

    $contagem = [];

    if (empty($logs) || !is_array($logs)) {
        return $contagem;
    }

    foreach ($logs as $item) {

        $status = scraperEmailLimparTexto($item["status"] ?? "sem_status");

        if ($status === "") {
            $status = "sem_status";
        }

        if (!isset($contagem[$status])) {
            $contagem[$status] = 0;
        }

        $contagem[$status]++;
    }

    ksort($contagem);

    return $contagem;
}

/**
 * CONTAR RESULTADOS POR SITE
 */
function contarResultadosPorSiteScraper($resultados) {

    $contagem = [];

    if (empty($resultados) || !is_array($resultados)) {
        return $contagem;
    }

    foreach ($resultados as $item) {

        $nomeSite = scraperEmailLimparTexto($item["nome_site"] ?? "Site não informado");

        if ($nomeSite === "") {
            $nomeSite = "Site não informado";
        }

        if (!isset($contagem[$nomeSite])) {
            $contagem[$nomeSite] = 0;
        }

        $contagem[$nomeSite]++;
    }

    arsort($contagem);

    return $contagem;
}

/**
 * FORMATAR LISTA SIMPLES PARA O E-MAIL DE RESUMO
 */
function formatarListaResumoEmail($titulo, $itens) {

    $texto = $titulo . "\n";

    if (empty($itens) || !is_array($itens)) {
        return $texto . "- Nenhum registro\n";
    }

    foreach ($itens as $nome => $total) {
        $texto .= "- " . $nome . ": " . (int)$total . "\n";
    }

    return $texto;
}

/**
 * FORMATAR SITES QUE RODARAM OU FORAM IGNORADOS NO RESUMO
 */
function formatarSitesExecucaoResumoEmail($titulo, $sites) {

    $texto = $titulo . "\n";

    if (empty($sites) || !is_array($sites)) {
        return $texto . "- Nenhum site\n";
    }

    foreach ($sites as $site) {

        $nomeSite = scraperEmailLimparTexto($site["nome_site"] ?? "Site não informado");
        $tipoFrequencia = scraperEmailLimparTexto($site["tipo_frequencia"] ?? "");
        $horarioInicio = scraperEmailLimparTexto($site["horario_inicio"] ?? "");
        $horarioFim = scraperEmailLimparTexto($site["horario_fim"] ?? "");
        $status = scraperEmailLimparTexto($site["status"] ?? "");

        if ($nomeSite === "") {
            $nomeSite = "Site não informado";
        }

        $linha = "- " . $nomeSite;

        if ($tipoFrequencia !== "") {
            $linha .= " | frequência: " . $tipoFrequencia;
        }

        if ($horarioInicio !== "" || $horarioFim !== "") {
            $linha .= " | horário configurado: " . ($horarioInicio !== "" ? $horarioInicio : "--:--") . " até " . ($horarioFim !== "" ? $horarioFim : "--:--");
        }

        if ($status !== "") {
            $linha .= " | status: " . $status;
        }

        $texto .= $linha . "\n";
    }

    return $texto;
}

/**
 * ENVIAR E-MAIL COM RESUMO DA EXECUÇÃO DO SCRAPER
 *
 * Este e-mail é enviado ao final da execução do scraper.php.
 * Ele não depende de existir imóvel novo.
 */
function enviarEmailResumoScraperRealizado($resumoScraper, $emailDestino) {

    global $enviarEmailResumoScraper;
    global $gravar_csv;

    $emailDestino = trim((string)$emailDestino);

    if (scraperEmailNormalizarBusca($enviarEmailResumoScraper ?? "nao") !== "sim") {
        return [
            "status" => "nao_enviado",
            "motivo" => "envio_resumo_desativado",
            "data" => date("d/m/Y H:i:s")
        ];
    }

    if (scraperEmailNormalizarBusca($gravar_csv ?? "") !== "sim") {
        return [
            "status" => "nao_enviado",
            "motivo" => "modo_teste_gravar_csv_nao",
            "data" => date("d/m/Y H:i:s")
        ];
    }

    if ($emailDestino === "") {
        return [
            "status" => "nao_enviado",
            "motivo" => "email_destino_resumo_vazio",
            "data" => date("d/m/Y H:i:s")
        ];
    }

    $resumoScraper = is_array($resumoScraper) ? $resumoScraper : [];

    $dataExecucao = scraperEmailLimparTexto($resumoScraper["data_execucao"] ?? date("d/m/Y H:i:s"));
    $assunto = "Resumo do scraper de imóveis - " . $dataExecucao;

    $logs = $resumoScraper["logs"] ?? [];
    $resultados = $resumoScraper["resultado"] ?? [];

    $statusLogs = contarStatusLogsScraper($logs);
    $resultadosPorSite = contarResultadosPorSiteScraper($resultados);
    $sitesExecutadosScraper = $resumoScraper["sites_executados_scraper"] ?? [];
    $sitesIgnoradosPorFrequencia = $resumoScraper["sites_ignorados_por_frequencia"] ?? [];

    $mensagem = "Resumo do scraper realizado.\n\n";
    $mensagem .= "Data da execução: " . $dataExecucao . "\n";
    $mensagem .= "Horário atual: " . scraperEmailLimparTexto($resumoScraper["horario_atual"] ?? "") . "\n";
    $mensagem .= "Status: " . scraperEmailLimparTexto($resumoScraper["status"] ?? "") . "\n";
    $mensagem .= "Gravar CSV: " . scraperEmailLimparTexto($resumoScraper["gravar_csv"] ?? "") . "\n";
    $mensagem .= "Status CSV imóveis: " . scraperEmailLimparTexto($resumoScraper["csv_status"] ?? "") . "\n";
    $mensagem .= "Status CSV usuários: " . scraperEmailLimparTexto($resumoScraper["csv_usuarios_status"] ?? "") . "\n\n";

    $mensagem .= "Totais\n";
    $mensagem .= "- Sites configurados: " . (int)($resumoScraper["total_sites"] ?? 0) . "\n";
    $mensagem .= "- Sites executados nesta execução: " . (int)($resumoScraper["total_sites_executados_scraper"] ?? 0) . "\n";
    $mensagem .= "- Sites ignorados por frequência/horário: " . (int)($resumoScraper["total_sites_ignorados_por_frequencia"] ?? 0) . "\n";
    $mensagem .= "- Resultados coletados nesta execução: " . (int)($resumoScraper["total_resultados_novos"] ?? 0) . "\n";
    $mensagem .= "- Total atual no CSV de imóveis: " . (int)($resumoScraper["total_resultados_csv"] ?? 0) . "\n";
    $mensagem .= "- Total no CSV de usuários: " . (int)($resumoScraper["total_usuarios_csv"] ?? 0) . "\n\n";

    $mensagem .= "Imagens\n";
    $mensagem .= "- Baixar imagens: " . scraperEmailLimparTexto($resumoScraper["baixar_imagens"] ?? "") . "\n";
    $mensagem .= "- Imagens baixadas: " . (int)($resumoScraper["total_imagens_baixadas"] ?? 0) . "\n";
    $mensagem .= "- Imagens que já existiam: " . (int)($resumoScraper["total_imagens_ja_existiam"] ?? 0) . "\n";
    $mensagem .= "- Erros de imagem: " . (int)($resumoScraper["total_erros_imagens"] ?? 0) . "\n\n";

    $mensagem .= formatarSitesExecucaoResumoEmail("Sites que rodaram nesta execução", $sitesExecutadosScraper) . "\n";
    $mensagem .= formatarSitesExecucaoResumoEmail("Sites ignorados por frequência/horário", $sitesIgnoradosPorFrequencia) . "\n";
    $mensagem .= formatarListaResumoEmail("Resultados por site", $resultadosPorSite) . "\n";
    $mensagem .= formatarListaResumoEmail("Status dos logs", $statusLogs) . "\n";

    $mensagem .= "Arquivos\n";
    $mensagem .= "- Imóveis: " . scraperEmailLimparTexto($resumoScraper["arquivo_csv"] ?? "") . "\n";
    $mensagem .= "- Usuários: " . scraperEmailLimparTexto($resumoScraper["arquivo_csv_usuarios"] ?? "") . "\n";

    $itemRemetenteResumo = [];

    if (!empty($resultados) && is_array($resultados)) {
        $itemRemetenteResumo = $resultados[0] ?? [];
    }

    $remetente = getEmailRemetenteNotificacaoNovoImovel($itemRemetenteResumo);

    $headersArray = [
        "MIME-Version: 1.0",
        "Content-Type: text/plain; charset=UTF-8",
        "From: Scraper Imóveis <" . $remetente . ">"
    ];

    $enviado = enviarEmailScraper($emailDestino, $assunto, $mensagem, $headersArray);

    return [
        "status" => $enviado ? "enviado" : "erro_envio",
        "email_destino" => $emailDestino,
        "assunto" => $assunto,
        "data" => date("d/m/Y H:i:s")
    ];
}
