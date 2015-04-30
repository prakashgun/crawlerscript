<?php

if (!empty($_SERVER['HTTP_HOST'])) {
    exit('Run only in CLI (terminal) mode');
}

require_once __DIR__ . '/vendor/autoload.php';

use Sgun\Web;

$startURL = 'http://www.smallwebsites.co';
$depth = 3;
$crawler = new Web\Crawler();
$crawler->setUrl($startURL);
$crawler->setDepth($depth);
if ($crawler->run() === false) {
    echo "Error occured. " . $crawler->getError();
}
