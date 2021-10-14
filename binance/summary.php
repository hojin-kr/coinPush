<?php

$root = dirname (dirname (__FILE__));

include $root . '/ccxt.php';

$exchange = new \ccxt\binance (array (
    'enableRateLimit' => true,
    // 'verbose' => true, // uncomment for verbose output
));
$lineToken = getenv("LINETOKEN");
$ups = [];
$downs = [];
foreach ($exchange->load_markets() as $symbol => $m) {
    $tick = $exchange->fetch_ticker($symbol);
    if(!isset($tick['symbol']) || !isset($tick['percentage'])) {
        continue;
    }
    if(0 < $tick['percentage']) {
        $ups[$tick['percentage']]['symbol'] = $tick['symbol'];
        $ups[$tick['percentage']]['percentage'] = $tick['percentage'];
    } else {
        $downs[$tick['percentage']]['symbol'] = $tick['symbol'];
        $downs[$tick['percentage']]['percentage'] = $tick['percentage'];
    }
}
krsort($ups);
ksort($downs);
$ups = array_splice($ups,0,10);
$message = "\n[Binance]\nSummary Up\n";
if(!empty($ups)) {
    foreach($ups as ['symbol'=>$symbol, 'percentage'=>$percentage]) {
        $percentage = (int)$percentage;
        $message .= "$symbol : $percentage% \n";
    }
    echo shell_exec("curl -X POST -H 'Authorization: Bearer $lineToken' -F 'message=$message ' https://notify-api.line.me/api/notify 2>&1");
}

$downs = array_splice($downs,0,10);
$message = "\n[Binance]\nSummary Down\n";
if(!empty($downs)) {
    foreach($downs as ['symbol'=>$symbol, 'percentage'=>$percentage]) {
        $percentage = (int)$percentage;
        $message .= "$symbol : $percentage% \n";
    }
    echo shell_exec("curl -X POST -H 'Authorization: Bearer $lineToken' -F 'message=$message ' https://notify-api.line.me/api/notify 2>&1");
}

?>
