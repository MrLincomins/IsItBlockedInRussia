<?php

namespace Infrastructure\Repository;

use Application\Entities\Repository;
use PDO;

class MysqlRepository extends ConnectDB implements Repository
{

    public function truncateTable($table): array
    {
        $sql = "TRUNCATE TABLE $table";
        $stmt = $this->connection->query($sql);
        return $stmt->fetchAll();
    }

    public function insertInto(array $values): bool
    {
        $this->connection->beginTransaction();
        $sql = "INSERT INTO blocked (ipv4, ipv4Mask, ipv6, decision_date, decision_org, decision_num, domain, url) 
        VALUES (:ipv4, :ipv4Mask, :ipv6, :decision_date, :decision_org, :decision_num, :domain, :url)";
        $stmt = $this->connection->prepare($sql);
        foreach ($values as $line) {
            $stmt->execute([
                'ipv4' => $line['ipv4'],
                'ipv4Mask' => $line['ipv4Mask'],
                'ipv6' => $line['ipv6'],
                'decision_date' => $line['decision_date'],
                'decision_org' => $line['decision_org'],
                'decision_num' => $line['decision_num'],
                'domain' => $line['domain'],
                'url' => $line['url']]);
        }
        $this->connection->commit();
        return True;
        //Добавление в бд
    }

    public function domainSearch(string $domain, string $punyDomain): array
    {
        $sql = "SELECT * FROM blocked WHERE domain = :domain OR domain = :punyDomain";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['domain' => $domain, 'punyDomain' => $punyDomain]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        //Поиск по домену
    }

    public function asteririskDomainSearch(string $domain): array
    {
        $sql = "SELECT * FROM blocked WHERE domain LIKE :domain";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['domain' => $domain]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function subDomainSearch(string $domain): array
    {
        $domain = '%.' . $domain;
        $sql = "SELECT * FROM blocked WHERE domain LIKE :domain";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['domain' => $domain]);
        return $stmt->fetchAll();
        //Поиск по домену
    }

    public function ipv4Search(int $ipv4): array
    {
        $sql = "SELECT * FROM blocked WHERE FIND_IN_SET(:ipv4, REPLACE(CONCAT_WS(',', ipv4), ' ', ',')) > 0;";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['ipv4' => $ipv4]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        //Поиск по ipv4
    }

    public function ipv4MaskConclusion(): array
    {
        $sql = "SELECT ipv4, ipv4Mask FROM blocked WHERE ipv4Mask IS NOT NULL";
        $stmt = $this->connection->query($sql);
        return $stmt->fetchAll();
        //Выводит все IPv4 с масками
    }

    public function ipv4MaskSearch($ipv4, $ipv4Mask): array
    {
        $sql = "SELECT * FROM blocked WHERE ipv4 = :ipv4 AND ipv4Mask = :ipv4Mask";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['ipv4' => $ipv4, 'ipv4Mask' => $ipv4Mask]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        //Поиск по ipv4 и по маске
    }

    public function addInfo($allLine): bool
    {
        $this->connection->query("TRUNCATE TABLE info");
        $date = date("Y-m-d H:i:s");
        $sql = "INSERT INTO info (date, allLine) VALUES (:date, :allLine)";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['date' => $date, 'allLine' => $allLine]);
        return True;
    }

    public function getInfo(): array
    {
        $sql = 'SELECT * FROM info LIMIT 1';
        $stmt = $this->connection->query($sql);
        return $stmt->fetchAll();

    }
}