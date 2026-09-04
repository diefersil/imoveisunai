<?php

/**
 * FUNÇÕES DE E-MAIL PARA NOVO IMÓVEL
 *
 * Este arquivo deve ser chamado pelo scraper-res.php.
 * Ele concentra somente as funções responsáveis por:
 * - identificar imóveis novos em relação ao CSV antigo;
 * - montar o remetente;
 * - enviar e-mails de notificação para imóveis novos cadastrados.
 */

/**
 * FILTRAR IMÓVEIS REALMENTE NOVOS NO CSV
 *
 * Compara as chaves dos registros finais com as chaves do CSV antigo.
 * Assim o e-mail é disparado somente para imóvel que ainda não existia
 * e que entrou no arquivo scraper-res.csv nesta execução.
 */
function filtrarImoveisNovosCadastrados($registrosAntigos, $registrosFinais) {

    $chavesAntigas = [];

    foreach ($registrosAntigos as $itemAntigo) {
        $chavesAntigas[gerarChaveRegistro($itemAntigo)] = true;
    }

    $novos = [];
    $chavesNovas = [];

    foreach ($registrosFinais as $itemFinal) {

        $chave = gerarChaveRegistro($itemFinal);

        if (isset($chavesAntigas[$chave]) || isset($chavesNovas[$chave])) {
            continue;
        }

        $novos[] = $itemFinal;
        $chavesNovas[$chave] = true;
    }

    return $novos;
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
 * ENVIAR E-MAIL QUANDO UM IMÓVEL NOVO FOR CADASTRADO
 *
 * Envia um e-mail para cada imóvel realmente novo cadastrado no CSV.
 * O link enviado é o card_url capturado/gerado pelo scraper.
 */
function enviarEmailNovoImovelCadastrado($item, $emailDestino) {

    $emailDestino = trim((string)$emailDestino);

    if ($emailDestino === "") {
        return [
            "status" => "nao_enviado",
            "motivo" => "email_destino_vazio",
            "card_nome" => $item["card_nome"] ?? "",
            "card_url" => $item["card_url"] ?? ""
        ];
    }

    $cardNome = limpar($item["card_nome"] ?? "");
    $linkGerado = trim((string)($item["card_url"] ?? ""));

    if ($cardNome === "") {
        $cardNome = "Imóvel sem título";
    }

    $assunto = "Novo imóvel cadastrado: " . $cardNome;

    $mensagem = "Novo imóvel cadastrado no scraper.\n\n";
    $mensagem .= "Imóvel: " . $cardNome . "\n";
    $mensagem .= "Link gerado: " . ($linkGerado !== "" ? $linkGerado : "Sem link") . "\n";
    $mensagem .= "Site de origem: " . limpar($item["nome_site"] ?? "") . "\n";
    $mensagem .= "Data do cadastro: " . date("d/m/Y H:i:s") . "\n";

    $remetente = getEmailRemetenteNotificacaoNovoImovel($item);

    $headersArray = [
        "MIME-Version: 1.0",
        "Content-Type: text/plain; charset=UTF-8",
        "From: Scraper Imóveis <" . $remetente . ">"
    ];

    $assuntoEmail = $assunto;

    if (function_exists("mb_encode_mimeheader")) {
        $assuntoEmail = mb_encode_mimeheader($assunto, "UTF-8", "B", "\r\n");
    }

    if (function_exists("wp_mail")) {
        $enviado = wp_mail($emailDestino, $assuntoEmail, $mensagem, $headersArray);
    } else {
        $enviado = @mail($emailDestino, $assuntoEmail, $mensagem, implode("\r\n", $headersArray));
    }

    return [
        "status" => $enviado ? "enviado" : "erro_envio",
        "email_destino" => $emailDestino,
        "card_nome" => $cardNome,
        "card_url" => $linkGerado,
        "data" => date("d/m/Y H:i:s")
    ];
}

/**
 * ENVIAR E-MAILS DOS IMÓVEIS NOVOS
 */
function enviarEmailsNovosImoveisCadastrados($imoveisNovos, $emailDestino) {

    global $enviarEmailNovoImovel;

    $logsEmail = [];

    if (normalizarBusca($enviarEmailNovoImovel) !== "sim") {

        foreach ($imoveisNovos as $item) {
            $logsEmail[] = [
                "status" => "nao_enviado",
                "motivo" => "envio_desativado",
                "card_nome" => $item["card_nome"] ?? "",
                "card_url" => $item["card_url"] ?? ""
            ];
        }

        return $logsEmail;
    }

    foreach ($imoveisNovos as $item) {
        $logsEmail[] = enviarEmailNovoImovelCadastrado($item, $emailDestino);
    }

    return $logsEmail;
}
