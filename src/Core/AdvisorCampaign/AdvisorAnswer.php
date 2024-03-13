<?php

namespace Elio\ElioSearch\Core\AdvisorCampaign;

use Shopware\Core\Framework\Struct\Struct;

/**
 * Class AdvisorAnswer
 * @package Elio\ElioSearch\Core\AdvisorCampaign
 * @author Ralf Frommherz
 */
class AdvisorAnswer extends Struct
{
    protected ?string $id = null;
    protected ?string $text = null;
    protected bool $selected = false;
    protected string $answerPath = '';
    /**
     * @var AdvisorQuestion[]
     */
    protected array $questions = [];

    /**
     * @return string|null
     */
    public function getId(): ?string
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
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return $this
     */
    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSelected(): bool
    {
        return $this->selected;
    }

    /**
     * @param bool $selected
     *
     * @return $this
     */
    public function setSelected(bool $selected): self
    {
        $this->selected = $selected;
        return $this;
    }

    /**
     * @return array
     */
    public function getQuestions(): array
    {
        return $this->questions;
    }

    /**
     * @param array $questions
     *
     * @return $this
     */
    public function setQuestions(array $questions): self
    {
        $this->questions = $questions;
        return $this;
    }

    /**
     * @param AdvisorQuestion $question
     *
     * @return void
     */
    public function addQuestion(AdvisorQuestion $question): void
    {
        $this->questions[] = $question;
    }

    /**
     * @return string
     */
    public function getAnswerPath(): string
    {
        return $this->answerPath;
    }

    /**
     * @param string $answerPath
     * @return AdvisorAnswer
     */
    public function setAnswerPath(string $answerPath): self
    {
        $this->answerPath = $answerPath;
        return $this;
    }
}
