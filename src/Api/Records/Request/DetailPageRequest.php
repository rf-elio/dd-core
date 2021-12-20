<?php


namespace Elio\FactFinder\Api\Records\Request;


use Elio\FactFinder\Api\Request\ChannelRequest;

class DetailPageRequest extends ChannelRequest
{
    private string $id;
    private bool $idsOnly = false;
    private string $idType = 'productNumber';
    private int $maxResultsRecommendations = 0;
    private int $maxResultsSimilarProducts = 10;
    private bool $usePersonalization = true;
    private ?string $sessionId = null;
    private ?string $purchaserId = null;
    private ?float $latitude = null;
    private ?float $longitude = null;
    private ?array $marketIds = null;
    private ?int $maxCountVariants = null;
    private bool $withCampaigns = true;
    private bool $withRecommendations = true;
    private bool $withSimilarProducts = true;
    private bool $withRecord = true;

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
     * @return bool
     */
    public function isIdsOnly(): bool
    {
        return $this->idsOnly;
    }

    /**
     * @param bool $idsOnly
     *
     * @return $this
     */
    public function setIdsOnly(bool $idsOnly): self
    {
        $this->idsOnly = $idsOnly;
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
     * @return bool
     */
    public function isUsePersonalization(): bool
    {
        return $this->usePersonalization;
    }

    /**
     * @param bool $usePersonalization
     *
     * @return $this
     */
    public function setUsePersonalization(bool $usePersonalization): self
    {
        $this->usePersonalization = $usePersonalization;
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
     * @return bool
     */
    public function isWithCampaigns(): bool
    {
        return $this->withCampaigns;
    }

    /**
     * @param bool $withCampaigns
     *
     * @return $this
     */
    public function setWithCampaigns(bool $withCampaigns): self
    {
        $this->withCampaigns = $withCampaigns;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWithRecommendations(): bool
    {
        return $this->withRecommendations;
    }

    /**
     * @param bool $withRecommendations
     *
     * @return $this
     */
    public function setWithRecommendations(bool $withRecommendations): self
    {
        $this->withRecommendations = $withRecommendations;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWithSimilarProducts(): bool
    {
        return $this->withSimilarProducts;
    }

    /**
     * @param bool $withSimilarProducts
     *
     * @return $this
     */
    public function setWithSimilarProducts(bool $withSimilarProducts): self
    {
        $this->withSimilarProducts = $withSimilarProducts;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWithRecord(): bool
    {
        return $this->withRecord;
    }

    /**
     * @param bool $withRecord
     *
     * @return $this
     */
    public function setWithRecord(bool $withRecord): self
    {
        $this->withRecord = $withRecord;
        return $this;
    }
}
