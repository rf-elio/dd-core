<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Request;

use Symfony\Component\HttpFoundation\Request;

trait MetaDataTrait
{
    private string $requestUri;
    private string $remoteAddress;
    private string $httpUserAgent;

    public function setMetaDataFromRequest(Request $request): void
    {
        $this->setRequestUri($request->attributes->get('sw-original-request-uri') ?? $request->getRequestUri());
        $this->setRemoteAddress($request->getClientIp());
        $this->setHttpUserAgent($request->headers->get('User-Agent'));
    }

    public function getRequestUri(): string
    {
        return $this->requestUri;
    }

    public function setRequestUri(string $requestUri): void
    {
        $this->requestUri = $requestUri;
    }

    public function getRemoteAddress(): string
    {
        return $this->remoteAddress;
    }

    public function setRemoteAddress(string $remoteAddress): void
    {
        $this->remoteAddress = $remoteAddress;
    }

    public function getHttpUserAgent(): string
    {
        return $this->httpUserAgent;
    }

    public function setHttpUserAgent(string $httpUserAgent): void
    {
        $this->httpUserAgent = $httpUserAgent;
    }
}
