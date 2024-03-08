<?php

namespace Elio\ElioSearch\Api\Search\Response;

use Elio\ElioSearch\Api\Response\Response;
use Elio\ElioSearch\Core\AdvisorCampaign\AdvisorQuestion;

/**
 * Class AdvisorCampaignResponse
 * @package Elio\ElioSearch\Api\Search\Response
 * @author Ralf Frommherz
 */
class AdvisorCampaignResponse extends Response
{
    /**
     * @param string $id
     * @param string $name
     * @param AdvisorQuestion[] $activeQuestions
     * @param iterable|AdvisorQuestion[] $questionPath
     * @param string $answerPath
     */
    public function __construct(
        protected string $id,
        protected string $name,
        private readonly array $activeQuestions,
        private readonly iterable $questionPath,
        private readonly string $answerPath
    ) {}

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
     * @return AdvisorQuestion[]
     */
    public function getActiveQuestions(): array
    {
        return $this->activeQuestions;
    }

    /**
     * @return AdvisorQuestion[]
     */
    public function getQuestionPath()
    {
        return $this->questionPath;
    }

    /**
     * @return string
     */
    public function getAnswerPath(): string
    {
        return $this->answerPath;
    }
}
