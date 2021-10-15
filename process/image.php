<?php

function createImg(string $text, string $root, string $filename) {
    // Set the content-type
    header('Content-Type: image/jpg');

    // Create the image
    $im = imagecreatetruecolor(500, 500);

    // Create some colors
    $white = imagecolorallocate($im, 255, 255, 255);
    $grey = imagecolorallocate($im, 128, 128, 128);
    $black = imagecolorallocate($im, 0, 0, 0);
    imagefilledrectangle($im, 0, 0, 500, 500, $white);

    // Replace path by your own font path
    $font = "$root/process/D2Coding-Ver1.3-20171129.ttf";
    // Add some shadow to the text
    imagettftext($im, 20, 0, 11, 21, $grey, $font, $text);
    // Add the text
    imagettftext($im, 20, 0, 10, 20, $black, $font, $text);
    if ($im !== false) {
        // Using imagepng() results in clearer text compared with imagejpeg()
        imagejpeg($im, "$root/process/temp/$filename.jpg");
        imagedestroy($im);
    } else {
        echo 'An error occurred.';
    }
}

