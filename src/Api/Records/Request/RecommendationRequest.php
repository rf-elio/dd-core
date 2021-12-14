<?php


namespace Elio\FactFinder\Api\Records\Request;


use Elio\FactFinder\Api\Request\ChannelRequest;

class RecommendationRequest extends ChannelRequest
{
    /**
     * @var string[]
     */
    private array $ids;
    private int $maxResults = 10;
    private ?string $sessionId = null;


    /**
     * @return string[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * @param string[] $ids
     */
    public function setIds(array $ids): void
    {
        $this->ids = $ids;
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
     * @return string|null
     */
    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    /**
     * @param string|null $sessionId
     */
    public function setSessionId(?string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

}
