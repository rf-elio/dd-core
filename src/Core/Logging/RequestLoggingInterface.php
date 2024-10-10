<?php

namespace Elio\ElioDataDiscovery\Core\Logging;

interface RequestLoggingInterface
{
    public function getRequestUri(): string ;
    public function getRemoteAddress(): string;
    public function getHttpUserAgent(): string;
}
