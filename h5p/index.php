<?php

require_once(__DIR__ . '/../config.php');
require_once('autoloader.php');

$interface = \core_h5p\framework::instance('interface');
$interface->fetchExternalData('http://example.com/myfolder/sympony.mp3?a=1&b=2#XYZ');
