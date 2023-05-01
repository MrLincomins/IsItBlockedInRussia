<?php
declare(strict_types=1);

require_once 'vendor/autoload.php';

use Application\Performers\Updater;
echo ((new Updater())->update().': строк было заполнено');
//Переходной файл для обновления (заполнения) бд
