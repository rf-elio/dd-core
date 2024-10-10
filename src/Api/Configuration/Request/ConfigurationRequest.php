<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Configuration\Request;

use Elio\ElioDataDiscovery\Api\Request\ChannelRequest;

class ConfigurationRequest extends ChannelRequest
{
    private string $type;
    private ?string $searchTerm;
    private ?int $limit;
    private ?int $offset;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getSearchTerm(): ?string
    {
        return $this->searchTerm;
    }

    /**
     * @param string|null $searchTerm
     */
    public function setSearchTerm(?string $searchTerm): void
    {
        $this->searchTerm = $searchTerm;
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @param int|null $limit
     */
    public function setLimit(?int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return int|null
     */
    public function getOffset(): ?int
    {
        return $this->offset;
    }

    /**
     * @param int|null $offset
     */
    public function setOffset(?int $offset): void
    {
        $this->offset = $offset;
    }
}
