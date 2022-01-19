<?php

namespace Elio\FactFinder\Api\Search\Response;

use Elio\FactFinder\Api\Response\Response;
use Elio\FactFinder\Core\AdvisorCampaign\AdvisorQuestion;

class AdvisorCampaignResponse extends Response
{
    private string $id;
    private string $name;
    /**
     * @var AdvisorQuestion[]
     */
    private array $questions;

    /**
     * @param string $id
     * @param string $name
     * @param array $questions
     */
    public function __construct(string $id, string $name, array $questions)
    {
        $this->id = $id;
        $this->name = $name;
        $this->questions = $questions;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getQuestions(): array
    {
        return $this->questions;
    }

    /**
     * @return array
     */
    public function questionsToArray(): array
    {
        $result = [];
        foreach ($this->questions as $question) {
            $result[] = $question->toArray();
        }
        return $result;
    }
}
