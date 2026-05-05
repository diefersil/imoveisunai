<?php

require_once "vendor/autoload.php";

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

$url = "https://www.wimoveis.com.br/venda/imoveis/mg/unai";

$serverUrl = "http://localhost:9515";

$driver = RemoteWebDriver::create($serverUrl, DesiredCapabilities::chrome());

$driver->get($url);

sleep(5);

// Rola a página para carregar os imóveis
for ($i = 0; $i < 8; $i++) {
    $driver->executeScript("window.scrollBy(0, 2000);");
    sleep(2);
}

$dados = $driver->executeScript("
    const links = Array.from(document.querySelectorAll(\"a[href*='/propriedades/'], a[href*='/imovel/']\"));

    const imoveis = links.map(a => {
        const card =
            a.closest('article') ||
            a.closest('[data-qa]') ||
            a.closest('div');

        const texto = card ? card.innerText : a.innerText;

        const preco = texto.match(/R\\$\\s?[\\d.,]+/);
        const area = texto.match(/(\\d+)\\s?m²/i);
        const quartos = texto.match(/(\\d+)\\s?(quarto|quartos|dormitório|dormitórios)/i);
        const banheiros = texto.match(/(\\d+)\\s?(banheiro|banheiros)/i);
        const vagas = texto.match(/(\\d+)\\s?(vaga|vagas)/i);

        const img = card ? card.querySelector('img') : null;

        return {
            titulo: texto.replace(/\\n/g, ' ').replace(/\\s+/g, ' ').trim(),
            preco: preco ? preco[0] : null,
            area: area ? area[1] : null,
            quartos: quartos ? quartos[1] : null,
            banheiros: banheiros ? banheiros[1] : null,
            vagas: vagas ? vagas[1] : null,
            imagem: img ? img.src : null,
            url: a.href
        };
    });

    const unicos = [];
    const urls = new Set();

    for (const item of imoveis) {
        if (item.url && !urls.has(item.url)) {
            urls.add(item.url);
            unicos.push(item);
        }
    }

    return unicos;
");

$driver->quit();

file_put_contents(
    "imoveis_wimoveis_unai.json",
    json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);

$csv = fopen("imoveis_wimoveis_unai.csv", "w");

fputcsv($csv, [
    "titulo",
    "preco",
    "area",
    "quartos",
    "banheiros",
    "vagas",
    "imagem",
    "url"
], ";");

foreach ($dados as $imovel) {
    fputcsv($csv, [
        $imovel["titulo"] ?? "",
        $imovel["preco"] ?? "",
        $imovel["area"] ?? "",
        $imovel["quartos"] ?? "",
        $imovel["banheiros"] ?? "",
        $imovel["vagas"] ?? "",
        $imovel["imagem"] ?? "",
        $imovel["url"] ?? ""
    ], ";");
}

fclose($csv);

header("Content-Type: application/json; charset=utf-8");

echo json_encode([
    "total" => count($dados),
    "arquivo_json" => "imoveis_wimoveis_unai.json",
    "arquivo_csv" => "imoveis_wimoveis_unai.csv",
    "dados" => $dados
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);