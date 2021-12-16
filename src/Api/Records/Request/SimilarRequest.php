<?php


namespace Elio\FactFinder\Api\Records\Request;


use Elio\FactFinder\Api\Request\ChannelRequest;

class SimilarRequest extends ChannelRequest
{
    private string $id;

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
}
