<?php

namespace Elio\FactFinder\Api\Search\Request;

class AdvisorStatus
{
    private string $answerPath;
    private string $id;

    /**
     * @param string $answerPath
     * @param string $id
     */
    public function __construct(string $answerPath, string $id)
    {
        $this->answerPath = $answerPath;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getAnswerPath(): string
    {
        return $this->answerPath;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
