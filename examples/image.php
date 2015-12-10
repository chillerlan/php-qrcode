<?php

require_once '../vendor/autoload.php';

use chillerlan\QRCode\Output\QRImage;
use chillerlan\QRCode\QRCode;

$im = (new QRCode('https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s', new QRImage))->output();

echo '<img src="'.$im.'" />';
