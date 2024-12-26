<?php

// Diretório base onde os arquivos estão localizados
$baseDir = 'C:\Pessoal\Processo';

// Arquivo CSV de saída
$outputCsv = 'output.csv';

// Função recursiva para explorar os diretórios e encontrar arquivos
function exploreDirectory($dir, &$fileList) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $filePath = $dir . '/' . $file;
            if (is_dir($filePath)) {
                exploreDirectory($filePath, $fileList);
            } elseif (is_file($filePath)) {
                $fileList[] = $filePath;
            }
        }
    }
}

// Lista para armazenar todos os arquivos encontrados
$fileList = [];
exploreDirectory($baseDir, $fileList);

// Array para armazenar nomes de arquivos processados (sem extensão)
$processedFiles = [];

// Abrir o arquivo CSV para escrita
if (($handle = fopen($outputCsv, 'w')) !== FALSE) {
    // Escrever o cabeçalho no CSV, incluindo a nova coluna para o nome da pasta
    fputcsv($handle, ['Banco', 'Data', 'Valor', 'Pasta']);

    // Regex para capturar o padrão BANCO_DATA_VALOR
    $pattern = '/^([A-Z]+)_(\d{4}-\d{2}-\d{2})_([\d\.]+)$/';

    // Processar cada arquivo
    foreach ($fileList as $file) {
        // Extrair o nome do arquivo sem o caminho e sem a extensão
        $fileName = pathinfo($file, PATHINFO_FILENAME);
        // Extrair o nome da pasta onde o arquivo está localizado
        $folderName = basename(dirname($file));

        // Verificar se o arquivo já foi processado
        if (in_array($fileName, $processedFiles)) {
            // Excluir arquivo duplicado
            unlink($file);
            echo "Arquivo duplicado excluído: $file\n";
            continue;
        }

        // Verificar e extrair as partes do nome do arquivo
        if (preg_match($pattern, $fileName, $matches)) {
            // Banco, Data, Valor
            $banco = $matches[1];
            $data = $matches[2];
            $valor = $matches[3];

            // Escrever no CSV, incluindo a pasta
            fputcsv($handle, [$banco, $data, $valor, $folderName]);

            // Marcar este arquivo como processado
            $processedFiles[] = $fileName;
        }
    }

    // Fechar o arquivo CSV
    fclose($handle);

    echo "CSV gerado com sucesso em: $outputCsv\n";
} else {
    echo "Erro ao abrir o arquivo CSV para escrita.\n";
}

?>
