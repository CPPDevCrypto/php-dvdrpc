<?php

require './vendor/autoload.php';

use Cppdevcrypto\Dividend\Client as DividendClient;

$DVDd = new DividendClient('http://dividendcashrpc:5pdjzTZrjsuJk6ivFSD65phgWZXQ5aCtXB4PWrvaFAUa@localhost:19998/');

$block = $DVDd->getBlock('000006eeaf84bb9cbab937799e64c47c160a11a0cb88249bfe268c18e690a12f');

echo $block('height')->get();
