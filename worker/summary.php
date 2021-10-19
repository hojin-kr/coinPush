<?php

$root = dirname (dirname (__FILE__));

include $root . '/ccxt.php';
include $root . '/process/image.php';

$exchanges = [];
$exchanges[] = new \ccxt\binance;
$exchanges[] = new \ccxt\kucoin;
$exchanges[] = new \ccxt\gateio;
$exchanges[] = new \ccxt\coinone;

$TW_API_KEY = getenv("TW_API_KEY");
$TW_API_KEY_SECRET = getenv("TW_API_KEY_SECRET");
$TW_API_TOKEN = getenv("TW_API_TOKEN");
$TW_API_TOKEN_SECRET = getenv("TW_API_TOKEN_SECRET");
$mediaIds = [];
foreach($exchanges as $exchange) {
    $tm = time();
    $ret = persentageTop($exchange, $root, $exchange->id);
    if($ret['code'] == 200) {
        $mediaIds[] = $ret['data']->media_id;
    }
    echo "\n $exchange->id ".(time()-$tm)."s\n";
}
// tweet
$message = "Maximum change information by exchange ".date('Y-M-D H:i:M');
$_mediaIds = implode(',', $mediaIds);
shell_exec("twurl -c $TW_API_KEY -s $TW_API_KEY_SECRET -a $TW_API_TOKEN -S $TW_API_TOKEN_SECRET -d 'status=$message' /1.1/statuses/update.json?media_ids=$_mediaIds");

function persentageTop($exchange, $root, $name) {
    $lineToken = getenv("LINETOKEN");
    $TW_API_KEY = getenv("TW_API_KEY");
    $TW_API_KEY_SECRET = getenv("TW_API_KEY_SECRET");
    $TW_API_TOKEN = getenv("TW_API_TOKEN");
    $TW_API_TOKEN_SECRET = getenv("TW_API_TOKEN_SECRET");
    $ticks = [];
    foreach ($exchange->load_markets() as $symbol => $m) {
        $tick = $exchange->fetch_ticker($symbol);
        if(count($ticks ?? []) > 5) break;
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
        $filename = createImg($message, $root, $name);
        // line noti
        echo shell_exec("twurl -X POST -H 'Authorization: Bearer $lineToken' -F 'message=$message' -F 'imageFile=@/$root/process/temp/$filename.jpg' https://notify-api.line.me/api/notify 2>&1");
        // midia upload
        var_dump($TW_API_KEY);
                var_dump($TW_API_KEY_SECRET);
                var_dump($TW_API_TOKEN);
                var_dump($TW_API_TOKEN_SECRET);
        echo "twurl -c $TW_API_KEY -s $TW_API_KEY_SECRET -a $TW_API_TOKEN -S $TW_API_TOKEN_SECRET -H 'upload.twitter.com' -X POST '/1.1/media/upload.json' --file '$root/process/temp/$filename' --file-field 'media'";
        $ret['data'] = json_decode(shell_exec("twurl -c $TW_API_KEY -s $TW_API_KEY_SECRET -a $TW_API_TOKEN -S $TW_API_TOKEN_SECRET -H 'upload.twitter.com' -X POST '/1.1/media/upload.json' --file '$root/process/temp/$filename' --file-field 'media'"));
        $ret['code'] = 200;
        return $ret;
    } else {
        echo "\n$name ticks is emtpy\n";
        return ['code'=>400];
    }
}

?>
