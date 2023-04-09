<?php

namespace grizzle;

interface RknDetectorInterface
{
    public function checkHost(string $host): RknResponse;

}