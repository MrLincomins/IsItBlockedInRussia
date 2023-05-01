<?php
require_once 'vendor/autoload.php';
use guzzle\IsItBlockedRknDetector;

dd((new IsItBlockedRknDetector())->checkHost('ххх.new-rutor.org'));

?>
