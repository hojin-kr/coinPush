<?php

function twurlUpdateStatus($message, $mediaIds) {
    $TW_API_KEY = getenv("TW_API_KEY");
    $TW_API_KEY_SECRET = getenv("TW_API_KEY_SECRET");
    $TW_API_TOKEN = getenv("TW_API_TOKEN");
    $TW_API_TOKEN_SECRET = getenv("TW_API_TOKEN_SECRET");
    $mediaIds = implode(',',$mediaIds);
    return json_decode(shell_exec("twurl -c $TW_API_KEY -s $TW_API_KEY_SECRET -a $TW_API_TOKEN -S $TW_API_TOKEN_SECRET -d 'status=$message' /1.1/statuses/update.json?media_ids=$mediaIds"));
}

function twurlUploadMedia($file) {
    $TW_API_KEY = getenv("TW_API_KEY");
    $TW_API_KEY_SECRET = getenv("TW_API_KEY_SECRET");
    $TW_API_TOKEN = getenv("TW_API_TOKEN");
    $TW_API_TOKEN_SECRET = getenv("TW_API_TOKEN_SECRET");
    return json_decode(shell_exec("twurl -c $TW_API_KEY -s $TW_API_KEY_SECRET -a $TW_API_TOKEN -S $TW_API_TOKEN_SECRET -H 'upload.twitter.com' -X POST '/1.1/media/upload.json' --file '$file' --file-field 'media'"));
}

function lineNotify($message, $file) {
    $lineToken = getenv("LINETOKEN");
    echo shell_exec("curl -X POST -H 'Authorization: Bearer $lineToken' -F 'message=$message' -F 'imageFile=@$file' https://notify-api.line.me/api/notify 2>&1");
}

function exportImage(string $text, string $filename) {
    $root = dirname (__FILE__);
    // Create the image
    $im = imagecreatetruecolor(500, 500);
    // Create some colors
    $white = imagecolorallocate($im, 255, 255, 255);
    $grey = imagecolorallocate($im, 128, 128, 128);
    $black = imagecolorallocate($im, 0, 0, 0);
    imagefilledrectangle($im, 0, 0, 500, 500, $white);
    // Replace path by your own font path
    $font = "$root/font/D2Coding-Ver1.3-20171129.ttf";
    // Add some shadow to the text
    imagettftext($im, 20, 0, 10, 480, $black, $font, date('Y-M-D H:i:M'));
    // Add the text
    imagettftext($im, 20, 0, 10, 20, $black, $font, $text);
    imagettftext($im, 20, 0, 10, 20, $black, $font, $text);
    // Add Donation Mark
    // $QR  = imagecreatefrompng("$root/process/dogeQR.png");
    // imagettftext($im, 20, 0, 10, 436, $black, $font, "Donation [DOGE]");
    // imagettftext($im, 20, 0, 10, 458, $black, $font, "DAxWfmsfgyfxFmSYBcubhTyMKTDgAjp9Dq");
    // imagecopymerge($im,$QR,350,280,0,0,150,150,100);

    if ($im !== false) {
        $dir = "$root/temp/$filename.jpg";
        // Using imagepng() results in clearer text compared with imagejpeg()
        imagejpeg($im, $dir);
        imagedestroy($im);
        return $dir;
    } else {
        echo 'An error occurred.';
    }
}

?>
