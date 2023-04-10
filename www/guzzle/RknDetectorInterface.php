<?php

namespace guzzle;

interface RknDetectorInterface
{
    public function checkHost(string $host): RknResponse;

}