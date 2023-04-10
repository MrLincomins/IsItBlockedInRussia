<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

use Application\Performers\Searcher;

if (isset($_GET['inputText'])) {

    $inputText = trim($_GET['inputText']);
    $values = (new Searcher())->search($inputText);
    echo json_encode((['message' => $values]));
    exit;
}
// Переходной файл для fetch в js

