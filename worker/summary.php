<?php

$root = dirname (dirname (__FILE__));

include $root . '/ccxt.php';
include $root . '/process/image.php';

$exchange = new \ccxt\coinone (array (
    'enableRateLimit' => true,
    // 'verbose' => true, // uncomment for verbose output
));
persentageTop($exchange, $root, 'coinone');

$exchange = new \ccxt\binance (array (
    'enableRateLimit' => true,
    // 'verbose' => true, // uncomment for verbose output
));
persentageTop($exchange, $root, 'binance');

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
        krsort($ticks);
        $message = "\n[$name]\n\n";
        foreach(array_splice($ticks,0,8) as ['symbol'=>$symbol, 'percentage'=>$percentage]) {
            $percentage = (int)$percentage;
            $message .= "$symbol : $percentage% \n";
        }
        ksort($ticks);
        $message .= "--------------------\n";
        foreach(array_splice($ticks,0,2) as ['symbol'=>$symbol, 'percentage'=>$percentage]) {
            $percentage = (int)$percentage;
            $message .= "$symbol : $percentage% \n";
        }
        $filename = $name;
        createImg($message, $root, $filename);
        echo shell_exec("curl -X POST -H 'Authorization: Bearer $lineToken' -F 'message=$message' -F 'imageFile=@/$root/process/temp/$filename.jpg' https://notify-api.line.me/api/notify 2>&1");
    } else {
        echo "$name ticks is emtpy";
    }

}

?>
