<?php

$root = dirname (dirname (__FILE__));

include $root . '/ccxt.php';
include $root . '/util.php';

date_default_timezone_set ('UTC');

// 거래소별 파싱
$exchanges = [new \ccxt\binance, new \ccxt\kucoin, new \ccxt\coinone];
$_exchanges = [];
foreach($exchanges as $exchange) {
    echo "[LOG] fetch $exchange->id ... \n";
    $_exchanges[$exchange->id] = $exchange->fetch_tickers();
}
if(!empty($_exchanges)) {
    $mediaIds = [];
    foreach($_exchanges as $exchangeId => $_exchange) {
        echo "[LOG] analysis $exchangeId ... \n";
        $percentages = [];
        foreach($_exchange as $symbol => $data) {
            if(!isset($data['symbol']) || !isset($data['percentage']) || !isset($data['last'])) {
                continue;
            }
            // if(!in_array(explode('/',$data['symbol'])[1],['USDT','KRW', 'BTC']) || strrpos($data['symbol'], 'UP') || strrpos($data['symbol'], 'DOWN')) {
            //     continue;
            // }
            $percentages[$data['percentage']] = $data;
        }
        $ups = $percentages;
        krsort($ups);
        $message = "\n[$exchangeId]\n\n";
        foreach(array_splice($ups,0,8) as ['symbol'=>$symbol, 'percentage'=>$percentage, 'last'=>$last]) {
            $percentage = (int)$percentage;
            $message .= "$symbol : $percentage% ($last)\n";
        }
        $downs = $percentages;
        ksort($downs);
        $message .= "--------------------\n";
        foreach(array_splice($downs,0,2) as ['symbol'=>$symbol, 'percentage'=>$percentage, 'last'=>$last]) {
            $percentage = (int)$percentage;
            $message .= "$symbol : $percentage% ($last) \n";
        }
        echo $message;
        // 이미지 제작
        $file = exportImage($message, $exchangeId);
        echo "[LOG] exportImage $file ... \n";
        // 이미지 업로드
        $media = twurlUploadMedia($file);
        echo "[LOG] twurlUploadMedia $media->media_id ... \n";
        $mediaIds[] = $media->media_id;
    }
    // 트윗
    $status = "Today ".date('Y-M-D H:i:M');
    twurlUpdateStatus($status, $mediaIds);
    echo "[LOG] twurlUpdateStatus $status ... \n";
    echo "[LOG] Today Done \n";
}

?>
