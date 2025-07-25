<?php

$metric = $argv[1] ?? 'download';
$csvFile = '/tmp/speedtest.csv';

if (!file_exists($csvFile)) {
    echo "0\n";
    exit(1);
}

$lines = file($csvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$lastLine = end($lines);
$data = str_getcsv($lastLine);

// Verifica se tem colunas suficientes
if (count($data) < 8) {
    echo "0\n";
    exit(1);
}

switch ($metric) {
    case 'download':
        echo $data[5]; // Download em Mbps
        break;
    case 'upload':
        echo $data[6]; // Upload em Mbps
        break;
    case 'ping':
        echo $data[7]; // Ping em ms
        break;
    case 'url':
        echo $data[8]; // Link do resultado
        break;
    case 'provider':
        echo $data[3]; // Nome do provedor e cidade
        break;
    default:
        echo "0\n";
        exit(1);
}
