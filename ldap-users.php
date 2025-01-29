<?php

$inputFile = 'files/contas-esss.txt';
$outputCsv = 'files/contas-esss.csv';

$handle = fopen($inputFile, 'r');
$output = fopen($outputCsv, 'w');

// Escrever o cabeçalho no CSV
fputcsv($output, ['Name', 'User', 'Department', 'Manager', 'Mail']);

$entry = [];
while (($line = fgets($handle)) !== false) {
    $line = trim($line);
    if (empty($line)) {
        // Se a linha estiver vazia, significa que terminamos de ler uma entrada
        if (!empty($entry)) {
            fputcsv($output, [
                $entry['displayName'] ?? '',
                $entry['uid'] ?? '',
                $entry['departmentNumber'] ?? '',
                $entry['manager'] ?? '',
                $entry['mail'] ?? ''
            ]);
            $entry = [];
        }
    } else {
        // Processar a linha atual
        list($key, $value) = explode(': ', $line, 2);
        if ($key === 'manager') {
            // Extrair apenas o uid do manager
            preg_match('/uid=([^,]+)/', $value, $matches);
            $value = $matches[1] ?? $value;
        }
        $entry[$key] = $value;
    }
}

// Escrever a última entrada se existir
if (!empty($entry)) {
    fputcsv($output, [
        $entry['displayName'] ?? '',
        $entry['uid'] ?? '',
        $entry['departmentNumber'] ?? '',
        $entry['manager'] ?? '',
        $entry['mail'] ?? ''
    ]);
}

fclose($handle);
fclose($output);

echo "CSV gerado com sucesso em: $outputCsv\n";

?>