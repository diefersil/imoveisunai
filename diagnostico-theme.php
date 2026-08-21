<?php

require_once __DIR__ . '/wp-load.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNOSTICO IMU ===\n\n";

echo "PHP: " . PHP_VERSION . "\n\n";

echo "DOCUMENT_ROOT:\n";
echo $_SERVER['DOCUMENT_ROOT'] . "\n\n";

echo "DIRETORIO DESTA INSTALACAO:\n";
echo __DIR__ . "\n\n";

echo "TEMA TEMPLATE:\n";
echo get_template() . "\n\n";

echo "TEMA STYLESHEET:\n";
echo get_stylesheet() . "\n\n";

echo "TEMPLATE DIRECTORY:\n";
echo get_template_directory() . "\n\n";

echo "STYLESHEET DIRECTORY:\n";
echo get_stylesheet_directory() . "\n\n";

$functions = get_stylesheet_directory() . '/functions.php';

echo "FUNCTIONS QUE O WORDPRESS DEVERIA USAR:\n";
echo $functions . "\n\n";

echo "FUNCTIONS EXISTE?\n";
echo file_exists($functions) ? "SIM\n\n" : "NAO\n\n";

if (file_exists($functions)) {

    $conteudo = file_get_contents($functions);

    echo "TESTE NOVO ESTA DENTRO DO ARQUIVO?\n";

    echo (
        strpos($conteudo, 'teste_functions_imu') !== false
        ? "SIM\n\n"
        : "NAO\n\n"
    );

    echo "ULTIMA ALTERACAO DO FUNCTIONS:\n";
    echo date('Y-m-d H:i:s', filemtime($functions)) . "\n\n";
}

echo "=== OPCACHE ===\n\n";

echo "opcache.enable: ";
var_export(ini_get('opcache.enable'));
echo "\n";

echo "opcache.validate_timestamps: ";
var_export(ini_get('opcache.validate_timestamps'));
echo "\n";

echo "opcache.revalidate_freq: ";
var_export(ini_get('opcache.revalidate_freq'));
echo "\n";

if (function_exists('opcache_is_script_cached')) {

    echo "functions em cache: ";

    var_export(
        opcache_is_script_cached($functions)
    );

    echo "\n";
}