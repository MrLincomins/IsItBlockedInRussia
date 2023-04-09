<?php
require_once '../vendor/autoload.php';
use grizzle\IsItBlockedRknDetector;

dd((new IsItBlockedRknDetector())->checkHost('ххх.new-rutor.org'));

?>
