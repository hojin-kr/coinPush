<?php

$root = dirname (dirname (__FILE__));

include $root . '/ccxt.php';
include $root . '/process/image.php';

$exchanges = [];
$exchanges[] = new \ccxt\binance;
$exchanges[] = new \ccxt\kucoin;
$exchanges[] = new \ccxt\gateio;
$exchanges[] = new \ccxt\ftx;
$exchanges[] = new \ccxt\coinone;
$exchanges[] = new \ccxt\bithumb;

foreach($exchanges as $exchange) {
    $tm = time();
    persentageTop($exchange, $root, $exchange->id);
    echo "\n $exchange->id ".(time()-$tm)."s\n";
}

function persentageTop($exchange, $root, $name) {
    $lineToken = getenv("LINETOKEN");
    $ticks = [];
    foreach ($exchange->load_markets() as $symbol => $m) {
        $tick = $exchange->fetch_ticker($symbol);
        if(!isset($tick['symbol']) || !isset($tick['percentage'])) {
            continue;
        }
        $ticks[$tick['percentage']]['symbol'] = $tick['symbol'];
        $ticks[$tick['percentage']]['percentage'] = $tick['percentage'];
    }
    if(!empty($ticks)) {
        $ups = $ticks;
        krsort($ups);
        $message = "\n[$name]\n\n";
        foreach(array_splice($ups,0,8) as ['symbol'=>$symbol, 'percentage'=>$percentage]) {
            $percentage = (int)$percentage;
            $message .= "$symbol : $percentage% \n";
        }
        $downs = $ticks;
        ksort($downs);
        $message .= "--------------------\n";
        foreach(array_splice($downs,0,2) as ['symbol'=>$symbol, 'percentage'=>$percentage]) {
            $percentage = (int)$percentage;
            $message .= "$symbol : $percentage% \n";
        }
        $filename = $name;
        createImg($message, $root, $filename);
        echo shell_exec("curl -X POST -H 'Authorization: Bearer $lineToken' -F 'message=$message' -F 'imageFile=@/$root/process/temp/$filename.jpg' https://notify-api.line.me/api/notify 2>&1");
    } else {
        echo "\n$name ticks is emtpy\n";
    }
}

?>
