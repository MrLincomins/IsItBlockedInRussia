<?php
require_once 'vendor/autoload.php';
use guzzle\IsItBlockedRknDetector;

//dd((new IsItBlockedRknDetector())->checkHost('mptri.fn')); В формате строки

dd((new IsItBlockedRknDetector())->checkHosts(['mptri.fun', 'site.com'])); // В формате массива: ['domain', 'domain1']

?>
