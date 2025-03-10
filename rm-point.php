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


putenv('WEBDRIVER_CHROME_DRIVER=C:\cmder\chromedriver.exe');

use DateInterval;
use DateTime;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

require_once('vendor/autoload.php');

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

$host = 'http://localhost:35543';

$capabilities = DesiredCapabilities::chrome();

$driver = RemoteWebDriver::create($host, $capabilities);

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
$url = 'https://portal.esss.co/Corpore.Net/Main.aspx?dtFim='.$daysInMonth.'%2f'.$currentMonth.'%2f'.$currentYear.'+00%3a00%3a00&MasterCaptionForAnnex='.urlencode('´').'1200+-+ANDERSON+NEGRINI+ISOTTON'.urlencode('´').'&Data=01%2f01%2f0001+00%3a00%3a00&ActionParameters=StaticFilter%3aCodColigada'.urlencode('|').'1'.urlencode('|').'Chapa'.urlencode('|').'1200&Coligada=1&ActionID=PtoatFunActionWeb&Origem=EspelhoCartao&dtIni=01%2f'.$currentMonth.'%2f'.$currentYear.'+00%3a00%3a00&ShowMode=3&AnnexKeyValues=1%3b1200%3b01%2f'.$currentMonth.'%2f'.$currentYear.'+00%3a00%3a00%3b'.$daysInMonth.'%2f'.$currentMonth.'%2f'.$currentYear.'+00%3a00%3a00&Chapa=1200';

$driver->get($url);

$driver->findElement(WebDriverBy::name('txtUser'))
    ->sendKeys($user);
$driver->findElement(WebDriverBy::name('txtPass'))
    ->sendKeys($pass);
$driver->findElement(WebDriverBy::name('btnLogin'))
    ->click();

    
$driver->findElement(WebDriverBy::name('GB$txtJustificativa'))
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

        if(str_contains($name, '$txtEnt1')){
            $value = $entrada1 = "{$entryTime}:". rand(10,15);
        }
        if(str_contains($name, '$txtSai1')){
            $value = $saida1 = '12:'. rand(0,15);
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