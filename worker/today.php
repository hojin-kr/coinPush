<?php
// 거래소별 디테일링 하고 각각 트윗 게시
$root = dirname (dirname (__FILE__));

include $root . '/ccxt.php';
include $root . '/util.php';

date_default_timezone_set ('UTC');

$IS_TEST = false;

// 거래소별 파싱
$exchanges = [new \ccxt\binance, new \ccxt\kucoin, new \ccxt\coinone];
$_exchanges = [];
if(!$IS_TEST) {
    foreach($exchanges as $exchange) {
        echo "[LOG] fetch $exchange->id ... \n";
        $_exchanges[$exchange->id] = $exchange->fetch_tickers();
    }
} else {
    $fp = fopen("exchanges_json.txt", "r") or die("파일을 열 수 없습니다！");
    $_exchanges = json_decode(fgets($fp), true);
    fclose($fp);
}
if(!empty($_exchanges)) {
    // 거래소별 분석
    foreach($_exchanges as $exchangeId => $_exchange) {
        echo "[LOG] analysis $exchangeId ... \n";
        $_exchange = tickValidator($_exchange);
        $medias = [];
        $mediaIds = [];
        $medias[] = exportImage(sortPsersentage($exchangeId, $_exchange), $exchangeId."_persentage");
        $medias[] = exportImage(spot($exchangeId, $_exchange), $exchangeId."_spot");
        /**
         * todo add some analysis images
         * $medias[] = someMethod();
         */
        if(!empty($medias) && !$IS_TEST) {
            foreach($medias as $media) {
                // 이미지 업로드
                echo "[LOG] twurlUploadMedia  ... \n";
                $mediaIds[] = twurlUploadMedia($media)->media_id;
            }
        }
        // 트윗
        if(!$IS_TEST) {
            echo "[LOG] twurlUpdateStatus $exchangeId... \n";
            $status = "Today on $exchangeId #coin #binance #kucoin #coinone #doge #btc";
            twurlUpdateStatus($status, $mediaIds);
        }
        echo "[LOG] Today $exchangeId Done \n";
        sleep(5);
    }
}

function sortPsersentage(string $exchangeId, array $exchange) : string
{
    $percentages = [];
    foreach($exchange as $symbol => $data) {
        $percentages[$data['percentage']] = $data;
    }
    $ups = $percentages;
    krsort($ups);
    $message = "\nBiggest Change\n";
    $message .= alignmentLeftRight(" ","[$exchangeId]");
    $message .= getStringSpace(0, "-")."\n";
    foreach(array_splice($ups,0,8) as ['symbol'=>$symbol, 'percentage'=>$percentage, 'last'=>$last]) {
        $percentage = number_format($percentage, 2);
        if($last < 0.01) {
            $last = number_format($last, 10);
        }
        [0=>$coinName, 1=>$baseCurrencyName] = explode('/',$symbol);
        $message .= alignmentLeftRight("$coinName", "$last $baseCurrencyName", "$percentage%");
    }
    $downs = $percentages;
    ksort($downs);
    $message .= getStringSpace(0, "-")."\n";
    foreach(array_splice($downs,0,2) as ['symbol'=>$symbol, 'percentage'=>$percentage, 'last'=>$last]) {
        $percentage = number_format($percentage, 2);
        if($last < 0.01) {
            $last = number_format($last, 10);
        }
        [0=>$coinName, 1=>$baseCurrencyName] = explode('/',$symbol);
        $message .= alignmentLeftRight("$coinName", "$last $baseCurrencyName", "$percentage%");
    }
    $message .= getStringSpace(0, "-")."\n";
    $message .= date('Y-m-d H:i:s')." UTC\n\n";
    echo $message;
    return $message;
}

function spot(string $exchangeId, array $exchange) : string
{
    $usdtSymbols = [
        'BTC/USDT',
        'ETH/USDT',
        'SHIB/USDT',
        'BUSD/USDT',
        'BNB/USDT',
        'DOGE/USDT',
        'XRP/USDT',
        'AVAX/USDT',
        'SOL/USDT',
        'TRX/USDT',
    ];
    $krwSymbols = [
        'BTC/KRW',
        'ETH/KRW',
        'LUNA/KRW',
        'FIL/KRW',
        'ADA/KRW',
        'DOGE/KRW',
        'XRP/KRW',
        'AVAX/KRW',
        'SOL/KRW',
        'KLAY/KRW',
    ];
    $symbols = $usdtSymbols;
    if(in_array($exchangeId, ['coinone'])) {
        $symbols = $krwSymbols;
    }
    $spots = [];
    foreach($exchange as $symbol => $data) {
        if(in_array($symbol, $symbols)) {
            $spots[$symbol] = $data;
        }
    }
    $message = "\nPopular\n";
    $message .= alignmentLeftRight(" ","[$exchangeId]");
    $message .= getStringSpace(0, "-")."\n";
    foreach(array_splice($spots,0,10) as ['symbol'=>$symbol, 'percentage'=>$percentage, 'last'=>$last]) {
        $percentage = number_format($percentage, 2);
        if($last < 0.01) {
            $last = number_format($last, 10);
        }
        [0=>$coinName, 1=>$baseCurrencyName] = explode('/',$symbol);
        $message .= alignmentLeftRight("$coinName", "$last $baseCurrencyName", "$percentage%");
    }
    $message .= getStringSpace(0, "-")."\n";
    $message .= date('Y-m-d H:i:s')." UTC\n\n";
    echo $message;
    return $message;
}
?>
