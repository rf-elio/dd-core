<?php


namespace Elio\FactFinder\Api\Records\Request;


use Elio\FactFinder\Api\Request\ChannelRequest;

/**
 * Class DetailPageRequest
 *
 * @package Elio\FactFinder\Api\Records\Request
 */
class DetailPageRequest extends ChannelRequest
{
    private string $id;
    private string $idsOnly = 'false';
    private string $idType = 'productNumber';
    private int $maxResultsRecommendations = 0;
    private int $maxResultsSimilarProducts = 10;
    private string $usePersonalization = 'true';
    private ?string $sessionId = null;
    private ?string $purchaserId = null;
    private ?float $latitude = null;
    private ?float $longitude = null;
    private ?array $marketIds = null;
    private ?int $maxCountVariants = null;
    private string $withCampaigns = 'true';
    private string $withRecommendations = 'true';
    private string $withSimilarProducts = 'true';
    private string $withRecord = 'true';

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
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
     *
     * @return $this
     */
    public function setIdType(string $idType): self
    {
        $this->idType = $idType;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxResultsRecommendations(): int
    {
        return $this->maxResultsRecommendations;
    }

    /**
     * @param int $maxResultsRecommendations
     *
     * @return $this
     */
    public function setMaxResultsRecommendations(int $maxResultsRecommendations): self
    {
        $this->maxResultsRecommendations = $maxResultsRecommendations;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxResultsSimilarProducts(): int
    {
        return $this->maxResultsSimilarProducts;
    }

    /**
     * @param int $maxResultsSimilarProducts
     *
     * @return $this
     */
    public function setMaxResultsSimilarProducts(int $maxResultsSimilarProducts): self
    {
        $this->maxResultsSimilarProducts = $maxResultsSimilarProducts;
        return $this;
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
     *
     * @return $this
     */
    public function setSessionId(?string $sessionId): self
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPurchaserId(): ?string
    {
        return $this->purchaserId;
    }

    /**
     * @param string|null $purchaserId
     *
     * @return $this
     */
    public function setPurchaserId(?string $purchaserId): self
    {
        $this->purchaserId = $purchaserId;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * @param float|null $latitude
     *
     * @return $this
     */
    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /**
     * @param float|null $longitude
     *
     * @return $this
     */
    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getMarketIds(): ?array
    {
        return $this->marketIds;
    }

    /**
     * @param array|null $marketIds
     *
     * @return $this
     */
    public function setMarketIds(?array $marketIds): self
    {
        $this->marketIds = $marketIds;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxCountVariants(): ?int
    {
        return $this->maxCountVariants;
    }

    /**
     * @param int|null $maxCountVariants
     *
     * @return $this
     */
    public function setMaxCountVariants(?int $maxCountVariants): self
    {
        $this->maxCountVariants = $maxCountVariants;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdsOnly(): string
    {
        return $this->idsOnly;
    }

    /**
     * @param string $idsOnly
     *
     * @return $this
     */
    public function setIdsOnly(string $idsOnly): self
    {
        $this->idsOnly = $idsOnly;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsePersonalization(): string
    {
        return $this->usePersonalization;
    }

    /**
     * @param string $usePersonalization
     *
     * @return $this
     */
    public function setUsePersonalization(string $usePersonalization): self
    {
        $this->usePersonalization = $usePersonalization;
        return $this;
    }

    /**
     * @return string
     */
    public function getWithCampaigns(): string
    {
        return $this->withCampaigns;
    }

    /**
     * @param string $withCampaigns
     *
     * @return $this
     */
    public function setWithCampaigns(string $withCampaigns): self
    {
        $this->withCampaigns = $withCampaigns;
        return $this;
    }

    /**
     * @return string
     */
    public function getWithRecommendations(): string
    {
        return $this->withRecommendations;
    }

    /**
     * @param string $withRecommendations
     *
     * @return $this
     */
    public function setWithRecommendations(string $withRecommendations): self
    {
        $this->withRecommendations = $withRecommendations;
        return $this;
    }

    /**
     * @return string
     */
    public function getWithSimilarProducts(): string
    {
        return $this->withSimilarProducts;
    }

    /**
     * @param string $withSimilarProducts
     *
     * @return $this
     */
    public function setWithSimilarProducts(string $withSimilarProducts): self
    {
        $this->withSimilarProducts = $withSimilarProducts;
        return $this;
    }

    /**
     * @return string
     */
    public function getWithRecord(): string
    {
        return $this->withRecord;
    }

    /**
     * @param string $withRecord
     *
     * @return $this
     */
    public function setWithRecord(string $withRecord): self
    {
        $this->withRecord = $withRecord;
        return $this;
    }
}
