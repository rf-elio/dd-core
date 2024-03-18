<?php

namespace Elio\ElioDataDiscovery\Api\Recommendations\Request;

use Elio\ElioDataDiscovery\Api\Request\ChannelRequest;

class SimilarRequest extends ChannelRequest
{
    private string $id;
    private string $idType = 'productNumber';
    private int $maxResults;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getMaxResults(): int
    {
        return $this->maxResults;
    }

    /**
     * @param int $maxResults
     */
    public function setMaxResults(int $maxResults): void
    {
        $this->maxResults = $maxResults;
    }

    /**
     * @return string
     */
    public function getIdType(): string
    {
        return $this->idType;
    }

    /**
     * @param string $idType
     */
    public function setIdType(string $idType): void
    {
        $this->idType = $idType;
    }
}
