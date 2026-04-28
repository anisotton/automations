<?php

// An example of using php-webdriver.
// Do not forget to run composer install before. You must also have Selenium server started and listening on port 4444.

namespace Facebook\WebDriver;

function str_contains_array($haystack, array $needles) {
    foreach ($needles as $needle) {
        if (str_contains($haystack, $needle)) {
            return true;
        }
    }
    return false;
}

function detectChromeVersion(): ?string {
    $candidates = ['/opt/google/chrome/chrome', 'google-chrome', 'google-chrome-stable', 'chromium-browser', 'chromium'];
    foreach ($candidates as $bin) {
        $output = shell_exec("{$bin} --version 2>/dev/null");
        if ($output && preg_match('/\d+\.\d+\.\d+\.\d+/', $output, $m)) {
            return $m[0];
        }
    }
    return null;
}

function ensureChromedriverInstalled(): void {
    if (trim(shell_exec('which chromedriver 2>/dev/null') ?? '')) {
        return;
    }

    echo "\nChromeDriver não encontrado no sistema.\n";

    $version = detectChromeVersion();
    if (!$version) {
        echo "Não foi possível detectar a versão do Chrome. Instale o ChromeDriver manualmente.\n";
        exit(1);
    }

    echo "Chrome detectado: {$version}\n";
    $confirm = readline("Deseja instalar o ChromeDriver {$version} agora? [s/N]: ");
    if (strtolower(trim($confirm)) !== 's') {
        echo "Instalação cancelada. Execute novamente após instalar o ChromeDriver.\n";
        exit(1);
    }

    $zipPath = '/tmp/chromedriver.zip';
    $url = "https://storage.googleapis.com/chrome-for-testing-public/{$version}/linux64/chromedriver-linux64.zip";

    echo "Baixando ChromeDriver {$version}...\n";
    shell_exec("wget -q '{$url}' -O {$zipPath} 2>&1");

    if (!file_exists($zipPath) || filesize($zipPath) === 0) {
        echo "Falha ao baixar o ChromeDriver. Verifique a conexão e tente novamente.\n";
        exit(1);
    }

    echo "Instalando...\n";
    shell_exec("unzip -o {$zipPath} -d /tmp/ 2>&1");
    shell_exec("sudo mv /tmp/chromedriver-linux64/chromedriver /usr/local/bin/chromedriver && sudo chmod +x /usr/local/bin/chromedriver 2>&1");
    shell_exec("rm -f {$zipPath} && rm -rf /tmp/chromedriver-linux64");

    if (!trim(shell_exec('which chromedriver 2>/dev/null') ?? '')) {
        echo "Falha na instalação. Instale manualmente:\n";
        echo "  sudo mv /tmp/chromedriver-linux64/chromedriver /usr/local/bin/chromedriver\n";
        echo "  sudo chmod +x /usr/local/bin/chromedriver\n";
        exit(1);
    }

    echo "ChromeDriver instalado com sucesso!\n";
}

function ensureChromedriverRunning(int $port): void {
    $conn = @fsockopen('localhost', $port, $errno, $errstr, 1);
    if ($conn) {
        fclose($conn);
        return;
    }

    echo "ChromeDriver não está em execução na porta {$port}. Iniciando...\n";
    shell_exec("chromedriver --port={$port} > /tmp/chromedriver.log 2>&1 &");
    sleep(2);

    $conn = @fsockopen('localhost', $port, $errno, $errstr, 2);
    if (!$conn) {
        echo "Falha ao iniciar o ChromeDriver. Verifique o log: /tmp/chromedriver.log\n";
        exit(1);
    }

    fclose($conn);
    echo "ChromeDriver iniciado na porta {$port}.\n";
}

use DateInterval;
use DateTime;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

require_once('vendor/autoload.php');

ensureChromedriverInstalled();

$user = readline("Enter your username: ");

echo "Enter your password: ";
system('stty -echo');
$pass = trim(fgets(STDIN));
system('stty echo');
echo "\n";

$entryTime = readline("Enter the entry hour (HH): ");
$timeWork = readline("Enter the time work in hour (HH): ");
$currentMonth = readline("Enter the current month (MM): ");
$currentYear = readline("Enter the current year (YYYY): ");

$port = readline("Enter the chrome port (default 4444): ");
if (empty($port)) {
    $port = 4444;
}

// Set default values if not provided
if (empty($user)) {
    $user = 'aisotton';
}
if (empty($entryTime)) {
    $entryTime = '08';
}
if (empty($timeWork)) {
    $timeWork = '08';
}
if (empty($currentMonth)) {
    $currentMonth = date('m');
}
if (empty($currentYear)) {
    $currentYear = date('Y');
}


$nameInput = ['$txtEnt1', '$txtEnt2', '$txtSai1', '$txtSai2'];
$dayIgnore = ['DOM', 'SÁB'];

$host = "http://localhost:{$port}";

ensureChromedriverRunning($port);

$capabilities = DesiredCapabilities::chrome();

$driver = RemoteWebDriver::create($host, $capabilities);

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
$url = 'https://portal.esss.com/Corpore.Net/Main.aspx?dtFim='.$daysInMonth.'%2f'.$currentMonth.'%2f'.$currentYear.'+00%3a00%3a00&MasterCaptionForAnnex='.urlencode('´').'1200+-+ANDERSON+NEGRINI+ISOTTON'.urlencode('´').'&Data=01%2f01%2f0001+00%3a00%3a00&ActionParameters=StaticFilter%3aCodColigada'.urlencode('|').'1'.urlencode('|').'Chapa'.urlencode('|').'1200&Coligada=1&ActionID=PtoatFunActionWeb&Origem=EspelhoCartao&dtIni=01%2f'.$currentMonth.'%2f'.$currentYear.'+00%3a00%3a00&ShowMode=3&AnnexKeyValues=1%3b1200%3b01%2f'.$currentMonth.'%2f'.$currentYear.'+00%3a00%3a00%3b'.$daysInMonth.'%2f'.$currentMonth.'%2f'.$currentYear.'+00%3a00%3a00&Chapa=1200';

$driver->get($url);

$driver->findElement(WebDriverBy::name('txtUser'))
    ->sendKeys($user);
$driver->findElement(WebDriverBy::name('txtPass'))
    ->sendKeys($pass);
$driver->findElement(WebDriverBy::name('btnLogin'))
    ->click();

    
$driver->findElement(WebDriverBy::id('GB_txtJustificativa'))
    ->sendKeys('Home office');
    

$elements = $driver->findElements(WebDriverBy::cssSelector("#GB_pnGridBatidas input"));

$array = [];
$aux = 0;
$today = date_create();
foreach($elements as $element){
    $name = $element->getAttribute('name');
    $prefixId = explode('$',$name);
    array_pop($prefixId);
    $prefixId = implode('_',$prefixId);

    if(str_contains_array($name, $nameInput )){
        $elementDate = implode('-',array_reverse(explode('/',$driver->findElement(WebDriverBy::id($prefixId.'_lblData'))->getText())));
        $date = date_create($elementDate);

        if($date->format('Y-m-d') >= $today->format('Y-m-d')){
            break;
        }
        $idSpanDay = $prefixId.'_lblDia';
        $day = $driver->findElement(WebDriverBy::id($idSpanDay))->getText();

        if(str_contains_array($day, $dayIgnore )){
            continue;
        }

        // Verificar se o campo já tem valor
        $currentValue = $element->getAttribute('value');
        if (!empty($currentValue)) {
            // Se já tem valor, usar esse valor nas variáveis para os cálculos
            if(str_contains($name, '$txtEnt1')){
                $entrada1 = $currentValue;
            } else if(str_contains($name, '$txtSai1')){
                $saida1 = $currentValue;
            } else if(str_contains($name, '$txtEnt2')){
                $entrada2 = $currentValue;
            }
            continue; // Pular para o próximo campo sem sobrescrever
        }

        // Código para gerar e preencher valores apenas se o campo estiver vazio
        if(str_contains($name, '$txtEnt1')){
            $value = $entrada1 = "{$entryTime}:". rand(10,15);
        }
        if(str_contains($name, '$txtSai1')){
            $value = $saida1 = '12:'. sprintf('%02d', rand(0,15));
        }
        if(str_contains($name, '$txtEnt2')){
            $value = $entrada2 = '12:'.rand(45,59);
        }
        if(str_contains($name, '$txtSai2')){
            $dtIni1 = date_create($entrada1);
            $dtIni2 = date_create($entrada2);
            $dtHorTrab = date_create("{$timeWork}:".rand(0,10));

            $diffHora = $dtHorTrab->diff(date_create($dtIni1->diff(new DateTime($saida1))->format('%H:%I')))->format("%H hours + %i minutes") ;

            $value = $dtIni2->add(DateInterval::createFromDateString($diffHora))->format("H:i");
            $element->sendKeys($value);

            print_r($date->format('Y-m-d') . " - {$dtHorTrab->format("H:i")} \n");
        }


        $element->sendKeys($value);
    }
}

exit();