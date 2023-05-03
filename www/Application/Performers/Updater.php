<?php

namespace Application\Performers;

use Infrastructure\Repository\MysqlRepository;
use Application\Entities\Repository;

class Updater
{
    protected Repository $db;

    public function __construct()
    {
        $this->db = new MysqlRepository();
    }


    public function update(): int
    {
        $csv = file_get_contents('https://raw.githubusercontent.com/zapret-info/z-i/master/dump.csv');
        $lines = explode("\n", $csv);
        $inserts = array();
        $countInserts = 0;
        $this->db->truncateTable("blocked");
        foreach ($lines as $i => $line) {
            if ($i == 0)
                continue;
            if ($i === count($lines) - 1)
                break;
            $components = explode(';', $line);
            if ((count($components)) > 6) {
                preg_match('/"(.*?)"/', $line, $url);
                $line = preg_replace('/\"(.*?)\"/', '', $line);
                $components = explode(';', $line);
                $components[2] = $url[0];
            }
            $inserts[] = $this->updateParse($components);
            if (!empty($inserts)) {
                if (count($inserts) == 5000) {
                    $this->db->insertInto($inserts);
                    $countInserts = $countInserts + count($inserts);
                    $inserts = array();
                }
            }
        }
        if (count($inserts) > 0) {
            $this->db->insertInto($inserts);
            $countInserts = $countInserts + count($inserts);
            $inserts = array();
        }
        $this->db->addInfo($countInserts);
        return $countInserts;
        //Получает данные из Git-а, парсит их и добавляет в бд
    }



    public function updateParse($components): array|null
    {
        $ips = $this->ipInetPton($components[0]);
        $url = explode(' | ', $components[2]);
        $url = urldecode($url[0]);
        $url = $this->to_utf8($url);
        $domain = urldecode($components[1]);
        $domain = $this->to_utf8($domain);
        $decision_org = mb_convert_encoding($components[3], 'utf-8', 'windows-1251');
        $decision_num = mb_convert_encoding($components[4], 'utf-8', 'windows-1251');
        return array(
            'ipv4' => $ips[0],
            'ipv4Mask' => $ips[2],
            'ipv6' => $ips[1],
            'domain' => $domain,
            'url' => $url,
            'decision_org' => $decision_org,
            'decision_num' => $decision_num,
            'decision_date' => $components[5]
        );
        //Переводит все данные в читаемый формат, а ip в биты
    }

    public function ipInetPton($ips): array
    {
        $ipAndMask = array();
        $ipv4 = '';
        $ipv6 = '';
        $ips = explode('|', $ips);
        $ips = array_slice($ips, 0, 4);
        //Вырезает все ip, оставляет только 4 штуки
        foreach ($ips as $ip) {
            if(count(explode('/', $ip)) == 2){
                $ipAndMask = explode('/', $ip);
                $ipv4 .= ip2long($ipAndMask[0]) . '';
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ipv4 .= ip2long($ip) . ' ';
            } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $ipv6 .= inet_pton($ip) . ' ';
            }
        } 
        $ipv6 = rtrim($ipv6);
        $ipv4 = rtrim($ipv4);
        //Сортирует все ip на ipv4 и ipv6, а также переводит их в биты. (Также разбирает ip на маску)
        return [$ipv4, $ipv6, $ipAndMask[1] ?? null];
    }


    function to_utf8($string)
    {
        return preg_match('%^(?:
      [\x09\x0A\x0D\x20-\x7E]
    | [\xC2-\xDF][\x80-\xBF]
    | \xE0[\xA0-\xBF][\x80-\xBF]
    | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} 
    | \xED[\x80-\x9F][\x80-\xBF] 
    | \xF0[\x90-\xBF][\x80-\xBF]{2}   
    | [\xF1-\xF3][\x80-\xBF]{3}   
    | \xF4[\x80-\x8F][\x80-\xBF]{2} )*$%xs', $string) ? $string : iconv('CP1251', 'UTF-8', $string);
        //Обычный перевод в Utf-8
    }
}