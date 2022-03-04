<?php

namespace Elio\FactFinder\Api\Search\Response;

use Elio\FactFinder\Api\Response\Response;
use Elio\FactFinder\Core\AdvisorCampaign\AdvisorQuestion;

/**
 * Class AdvisorCampaignResponse
 * @package Elio\FactFinder\Api\Search\Response
 * @author Ralf Frommherz
 */
class AdvisorCampaignResponse extends Response
{
    /**
     * @var string
     */
    protected string $id;
    /**
     * @var string
     */
    protected string $name;
    /**
     * @var AdvisorQuestion[]
     */
    private array $activeQuestions;
    /**
     * @var AdvisorQuestion[]
     */
    private iterable $questionPath;
    /**
     * @var string
     */
    private string $answerPath;

    /**
     * @param string $id
     * @param string $name
     * @param AdvisorQuestion[] $activeQuestions
     * @param iterable|AdvisorQuestion[] $questionPath
     * @param string $answerPath
     */
    public function __construct(string $id, string $name, array $activeQuestions, iterable $questionPath, string $answerPath)
    {
        $this->id = $id;
        $this->name = $name;
        $this->activeQuestions = $activeQuestions;
        $this->questionPath = $questionPath;
        $this->answerPath = $answerPath;
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
