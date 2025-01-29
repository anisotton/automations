<?php 

$inputCsv = 'files/products-sf-20240102.csv';
$outputCsv = 'files/products-unique.csv';

if (($handle = fopen($inputCsv, 'r')) !== false) {
    $output = fopen($outputCsv, 'w');
    
    // Ler o cabeçalho
    $header = fgetcsv($handle);
    fputcsv($output, $header);

    $uniqueProducts = [];

    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
        $productName = $data[1];
        
        
        // Remover o tipo do nome do produto
        $productName = preg_replace('/\s-\s(Lease|Paid-Up|Tecs|TECS)(\s*\(.*\))?/', '', $productName);
        // Verificar se o produto já foi adicionado
        if (!in_array($productName, $uniqueProducts)) {
            $uniqueProducts[] = $productName;
            $data[1] = $productName;
            fputcsv($output, $data);
        }
    }

    fclose($handle);
    fclose($output);

    echo "CSV gerado com sucesso em: $outputCsv\n";
} else {
    echo "Erro ao abrir o arquivo CSV.\n";
}

?>