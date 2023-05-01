<?php

declare(strict_types=1);

namespace Application\Entities;

interface Repository
{
    public function insertInto(array $values): bool;

    public function domainSearch(string $domain, string $punyDomain): array;

    public function ipv4Search(int $ipv4): array;

    public function ipv4MaskConclusion(): array;

    public function ipv4MaskSearch($ipv4, $ipv4Mask): array;



}
