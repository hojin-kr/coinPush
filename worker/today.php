<?php
// 거래소별 디테일링 하고 각각 트윗 게시
$root = dirname (dirname (__FILE__));

include $root . '/ccxt.php';
include $root . '/util.php';

date_default_timezone_set ('UTC');

$IS_TEST = false;

// 거래소별 파싱
$exchanges = [new \ccxt\binance, new \ccxt\kucoin];
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
        $medias[] = exportImage(spotVolume($exchangeId, $_exchange), $exchangeId."_spot_volume");
        /**
         * todo add some analysis images
         * $medias[] = someMethod();
         */
        if(!empty($medias) && !$IS_TEST) {
            foreach($medias as $media) {
                // 이미지 업로드
                echo "[LOG] twurlUploadMedia  ... \n";
                // x remove
                // $mediaIds[] = twurlUploadMedia($media)->media_id;
            }
        }
        // 트윗
        if(!$IS_TEST) {
            echo "[LOG] twurlUpdateStatus $exchangeId... \n";
            $status = "Today on $exchangeId #coin #binance #kucoin #analysis #doge #btc";
            // x remove
            // twurlUpdateStatus($status, $mediaIds);
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
        } else {
            $last = number_format($last, 2);
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
    $spots = [];
    foreach($exchange as $symbol => $data) {
        if(explode('/',$symbol)[1] == 'USDT') {
            $spots[$symbol] = $data;
        }
    }
    usort($spots, "sortVolume");
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

function sortVolume($a, $b)
{
    if ($a['quoteVolume'] == $b['quoteVolume']) {
        return 0;
    }
    return ($a['quoteVolume'] > $b['quoteVolume']) ? -1 : 1;
}


function spotVolume(string $exchangeId, array $exchange) : string
{
    $spots = [];
    foreach($exchange as $symbol => $data) {
        if(explode('/',$symbol)[1] == 'USDT') {
            $spots[$symbol] = $data;
        }
    }
    usort($spots, "sortVolume");
    $spots = array_splice($spots,0,10);
    $sumVolume = 0;
    $cntVolume = 0;
    foreach($spots as $data) {
        $sumVolume += $data['quoteVolume'];
        $cntVolume += 1;
    }
    $message = "\nVolume\n";
    $message .= alignmentLeftRight(" ","[$exchangeId]");
    $message .= getStringSpace(0, "-")."\n";
    foreach($spots as ['symbol'=>$symbol, 'quoteVolume'=>$volume]) {
        if(is_null($volume)) {
            continue;
        }
        [0=>$coinName, 1=>$baseCurrencyName] = explode('/',$symbol);
        $volumePercentage = number_format($volume/$sumVolume*100,2);
        $volume = number_format($volume,2);
        $message .= alignmentLeftRight("$coinName", "$volume $baseCurrencyName", "$volumePercentage%");
    }
    $message .= getStringSpace(0, "-")."\n";
    $message .= date('Y-m-d H:i:s')." UTC\n\n";
    echo $message;
    return $message;
}
?>
