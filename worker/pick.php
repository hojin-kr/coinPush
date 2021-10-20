<?php

$root = dirname (dirname (__FILE__));

include $root . '/ccxt.php';
include $root . '/util.php';

date_default_timezone_set ('UTC');

// 거래소별 파싱
$exchanges = [new \ccxt\binance, new \ccxt\kucoin, new \ccxt\coinone];
$_exchanges = [];

$usdtSymbols = ['BTC/USDT','ETH/USDT','XRP/USDT','DOGE/USDT'];
$krwSymbols = ['BTC/KRW','ETH/KRW','XRP/KRW','DOGE/KRW'];
$mediaIds = [];
foreach($exchanges as $exchange) {
    echo "[LOG] fetch $exchange->id ... \n";
    $symbols = $usdtSymbols;
    if(in_array($exchange->id, ['coinone'])) {
        $symbols = $krwSymbols;
    }
    $message = "\n[$exchange->id]\n\n";
    foreach($symbols as $symbol) {
        $tick = $exchange->fetch_ticker($symbol);
        if(!isset($tick['symbol']) || !isset($tick['percentage']) || !isset($tick['last'])) {
            continue;
        }
        ['symbol'=>$symbol, 'percentage'=>$percentage, 'last'=>$last] = $tick;
        $percentage = (int)$percentage;
        $message .= "$symbol : $percentage% ($last)\n";
    }
    echo $message;
    // 이미지 제작
    $file = exportImage($message, $exchange->id);
    echo "[LOG] exportImage $file ... \n";
    // 이미지 업로드
    $media = twurlUploadMedia($file);
    echo "[LOG] twurlUploadMedia $media->media_id ... \n";
    $mediaIds[] = $media->media_id;
}
// 트윗
$status = "Pick ".date('Y-M-D H:i:M');
twurlUpdateStatus($status, $mediaIds);
echo "[LOG] twurlUpdateStatus $status ... \n";
echo "[LOG] Pick Done \n";

?>
