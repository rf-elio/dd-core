<?php


namespace Elio\FactFinder\Api\Request;

/**
 * Trait RemoteIpTrait
 *
 * @package Elio\FactFinder\Api\Request
 */
trait RemoteIpTrait
{
    protected ?string $remoteIp;

    /**
     * @return string|null
     */
    public function getRemoteIp(): ?string
    {
        return $this->remoteIp;
    }

    /**
     * @param string|null $remoteIp
     */
    public function setRemoteIp(?string $remoteIp): void
    {
        $this->remoteIp = $remoteIp;
    }
}
