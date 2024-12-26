<?php

$csvFile = 'glpi.csv';
$outputFile = 'glpi2.csv';

if (($handle = fopen($csvFile, 'r')) !== false) {
    $output = fopen($outputFile, 'w');
    while (($data = fgetcsv($handle, 1000, ';')) !== false) {
        foreach ($data as &$field) {
            if ($field !== null) {
                $field = str_replace(["\r", "\n"], ' ', $field);
            }
        }
        fputcsv($output, $data, ';');
    }
    fclose($handle);
    fclose($output);
} else {
    echo "Erro ao abrir o arquivo CSV.";
}