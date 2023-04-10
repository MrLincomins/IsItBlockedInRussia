<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

use Application\Performers\Searcher;


    $inputText = explode('.', 'ххх.new-rutor.org', -2);
    dd($inputText);

    dd((new Searcher())->search($inputText));
// Переходной файл для fetch в js

