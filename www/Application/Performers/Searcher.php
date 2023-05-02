<?php

namespace Application\Performers;

use Application\Entities\Repository;
use Infrastructure\Repository\MysqlRepository;
use JetBrains\PhpStorm\ArrayShape;

class Searcher
{
    private Repository $db;

    public function __construct()
    {
        $this->db = new MysqlRepository();
    }

    public function domainSearch(string $domain): array
    {
        $domain = parse_url($domain, PHP_URL_HOST) ?? parse_url($domain, PHP_URL_PATH);
        $values = $this->db->domainSearch($domain, idn_to_ascii($domain));

        $subDomains = $this->subDomainSearch($domain);
        foreach ($subDomains as $subDomain) {
            $values = array_merge($values, $subDomain);
        }

        $valuesReturn = [];
        foreach ($values as $value) {
            $valuesReturn[] = $this->ipParse($value);
        }

        $valuesReturn = array_map("unserialize", array_unique(array_map("serialize", $valuesReturn)));
        return $valuesReturn ?: [['responce' => false]];
    }


    public function subDomainSearch(string $domain): array
    {
        $subDomain = null;

        $parts = explode('.', $domain, -2);

        if(!empty($parts[0]) and $parts[0] != '*'){

            $parts = explode('.', $domain, );
            $withoutSub = $parts[1] . '.' . $parts[2];
            $subDomain = $this->db->domainSearch($withoutSub, idn_to_ascii($withoutSub));
            $asteriskDomain = '*.' . $withoutSub;
            $asteriskDomain = $this->db->asteririskDomainSearch($asteriskDomain);

        } elseif(!empty($parts[0]) and @$parts[0] == '*') {

            $parts = explode('.', $domain, );
            $withoutSub = $parts[1] . '.' . $parts[2];
            $asteriskDomain = '%.' . $withoutSub;
            $asteriskDomain = $this->db->asteririskDomainSearch($asteriskDomain);
        } else {

            $asteriskDomain = '*.' . $domain;
            $asteriskDomain = $this->db->asteririskDomainSearch($asteriskDomain);
        }
        if ($subDomain) {
            return [$asteriskDomain, $subDomain];
        } elseif ($asteriskDomain) {
            return [$asteriskDomain];
        } else {
            return [];
        }
    }


    public function ipv4Search(string $ipv4): array
    {
        $ipWithMask = $this->ipv4MaskSearch($ipv4);
        $ipv4 = ip2long($ipv4);
        $values = $this->db->ipv4Search((int)$ipv4);
        $result = [];

        foreach (array_merge($values, $ipWithMask) as $value) {
            $result[] = $this->ipParse($value);
        }

        return $result ?: [['responce' => false]];
    }

    public function ipv4MaskSearch(int|string $ipv4): array|null
    {
        $result = array();
        $allMask = $this->db->ipv4MaskConclusion();
        foreach ($allMask as $mask) {
            $mask['ipv4'] = long2ip((int)$mask['ipv4']);
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

    #[ArrayShape(['responce' => "bool", 'data' => "array"])]
    public function ipParse(mixed $value): array
    {
        if(is_array($value['ipv4'])) {
            $ipv4 = join(' ', array_map('long2ip', explode(' ', $value['ipv4'])));
        } else {
            $ipv4 = long2ip((int)$value['ipv4']);
        }
        $ipv6 = join(' ', array_map('inet_ntop', explode(' ', $value['ipv6'])));
        if (!empty($value['ipv4Mask'])) {
            $ipv4 .= '/' . $value['ipv4Mask'];
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
                $value['decision_date'],
            ],
        ];
    }


    public function checkData(string $data): \Exception|bool
    {
        if (!empty(filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))) {
            return new \Exception('IPv6 addresses are unsupported');
        } elseif (empty(@gethostbyaddr($data))
            && empty(@gethostbyname($data))) {
            return new \Exception('Enter the actual IPv4 address or domain');
        } elseif ((mb_detect_encoding($data, ['UTF-8', 'ASCII'], true) == "UTF-8"
            && mb_detect_encoding($data, ['UTF-8', 'ASCII'], true) == "ASCII")) {
            return new \Exception('Unknown format');
        }
        // Проверяет введённые пользователем значения
        else {
            return False;
        }
    }

    public function search(string $data): array|\Exception
    {
        $errors = $this->checkData($data);
        if ($errors) {
            return $errors;
        }
        if (filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $result = $this->ipv4Search($data);
        } else {
            $result = $this->domainSearch($data);
        }
        return $result;
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