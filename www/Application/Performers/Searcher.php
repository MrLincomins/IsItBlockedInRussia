<?php

namespace Application\Performers;

use Application\Entities\Repository;
use Infrastructure\Repository\MysqlRepository;

class Searcher
{
    private Repository $db;

    public function __construct()
    {
        $this->db = new MysqlRepository();
    }

    public function domainSearch($domain): array
    {
        $domain = $this->removeProtocol($domain);
        $values = $this->db->domainSearch($domain);
        $subDomain  = $this->subDomainSearch($domain);
        if(!empty($subDomain)){
            foreach ($subDomain as $sub) {
                $values = array_merge($values, $sub);
            }
        }
        if (!empty($values)) {
            $valuesReturn = array();
            foreach ($values as $value) {
                $valuesReturn[] = $this->ipParse($value);
            }
        }
        return $valuesReturn ?? [['responce' => false]];
        //Делает поиск по домену
    }

    function removeProtocol($url): string
    {
        //Удаляет протоколы с домена
        $protocols = array('http://', 'https://', 'ftp://', 'ftps://', 'ssh://', 'telnet://', 'mailto://');
        foreach($protocols as $protocol) {
            if(str_starts_with($url, $protocol)) {
                return substr($url, strlen($protocol));
            }
        }
        return $url;
    }

    public function subDomainSearch($domain): array
    {
        $subDomain = explode('.', $domain, -2);
        if(!empty($subDomain)){
            $withoutSub = explode('.', $domain);
            $withoutSub = $withoutSub[1].'.'. $withoutSub[2];
            $subDomain = $this->db->domainSearch($withoutSub);
            $asteriskDomain = '*.'.$withoutSub;
            $asteriskDomain = $this->db->domainSearch($asteriskDomain);
            return [$asteriskDomain, $subDomain];
        } else{
            $asteriskDomain = '*.'.$domain;
            $asteriskDomain = $this->db->domainSearch($asteriskDomain);
            return [$asteriskDomain];
        }
    }

    public function ipv4Search($ipv4): array
    {
        $ipWithMask = $this->ipv4MaskSearch($ipv4);
        $ipv4 = ip2long($ipv4);
        $values = $this->db->ipv4Search((int)$ipv4);
        $result = array();

        if (!empty($values)) {
            foreach ($values as $value) {
                $result[] = $this->ipParse($value);
            }
        }

        if(!empty($ipWithMask)){
            foreach ($ipWithMask as $i=> $ipMask) {
                $ipWithMask[$i] = $this->ipParse($ipMask);
            }
        }
        $result = array_merge($result, (array)$ipWithMask);

        return $result ?? [['responce' => false]];
        //Делает поиск по ipv4 в бд и по маскам(подсетью вроде)

    }

    public function ipv4MaskSearch($ipv4): array|null
    {
        $result = array();
        $allMask = $this->db->ipv4MaskConclusion();
        foreach ($allMask as $mask) {
            $mask['ipv4'] = long2ip($mask['ipv4']);
            $mask = implode('/', $mask);
            if ($this->ip_in_range($ipv4, $mask)) {
                $ipMask = explode('/', $mask);
                $ipMask[0] = ip2long($ipMask[0]);
                $result = $this->db->ipv4MaskSearch($ipMask[0], $ipMask[1]);
            }
        }
        return $result ?? null;
        //Делает поиск по маске
    }

    public function ipParse($value): array
    {
        $ipv4_array = explode(' ', $value['ipv4']);
        $ipv6_array = explode(' ', $value['ipv6']);
        $ipv6_array = array_map('inet_ntop', $ipv6_array);
        $ipv4_array = array_map('long2ip', $ipv4_array);
        $ipv6 = implode(' ', $ipv6_array);
        $ipv4 = implode(' ', $ipv4_array);
        if(!empty($value['ipv4Mask'])){
            $ipv4 = $ipv4.'/'.$value['ipv4Mask'];
        }
        return [
            'responce' => true,
            'data' => [
                $ipv4,
                $ipv6,
                $value['domain'],
                $value['url'],
                $value['decision_org'],
                $value['decision_num'],
                $value['decision_date']
            ]];
        //Разбирает полученные данные из бд.
    }

    public function checkData($data): \Exception|bool
    {
        if (!empty(filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))) {
            return new \Exception('IPv6 addresses are unsupported');
        }

        elseif (empty(@gethostbyaddr($data))
            && empty(@gethostbyname($data))) {
            return new \Exception('Enter the actual IPv4 address or domain');
        }

        elseif ((mb_detect_encoding($data, ['UTF-8', 'ASCII'], true) == "UTF-8"
            && mb_detect_encoding($data, ['UTF-8', 'ASCII'], true) == "ASCII")) {
            return new \Exception('Unknown format');
        }

//        elseif (empty(filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
//            && empty(filter_var($data, FILTER_VALIDATE_URL, FILTER_FLAG_HOSTNAME))) {
//            return new \Exception('Please enter domain or IPv4');
//        } Из-за того, что в бд могут содержаться домены по типу таких: '*.1-xredbet27361.top', FILTER_VALIDATE_URL не работает, validate domain тож
        // Проверяет введённые пользователем значения
        else {
            return False;
        }
    }

    public function search(string $data): array|\Exception
    {
        $errors = $this->checkData($data);
        if ($errors) {
            return $this->checkData($data);
        }
        if (filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $this->ipv4Search($data);

        } else {
            return $this->domainSearch($data);
        }
        //Просто строит маршрут между другими функциями

    }

    function ip_in_range($ip, $range): bool
    {
        if (!strpos($range, '/')) {
            $range .= '/32';
        }
        list($range, $netmask) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
        $netmask_decimal = ~$wildcard_decimal;
        return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
        // Проверяет, содержится ли IPv4 в подсети
    }
}