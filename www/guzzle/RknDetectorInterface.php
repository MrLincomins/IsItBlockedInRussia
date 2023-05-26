<?php

namespace guzzle;

interface RknDetectorInterface
{
    public function checkHost(string $host): RknResponse;

    public function checkHosts(array $hosts): RknResponse;

}