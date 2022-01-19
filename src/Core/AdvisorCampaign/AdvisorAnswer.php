<?php

namespace Elio\FactFinder\Core\AdvisorCampaign;


class AdvisorAnswer
{
    private ?string $id = null;
    private ?string $text = null;
    private ?bool $selected = null;
    /**
     * @var AdvisorQuestion[]
     */
    private array $questions = [];

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
    public function getText(): string
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
     * @return array
     */
    public function toArray(): array
    {
        $questions = [];
        foreach ($this->questions as $question) {
            $questions[] = $question->toArray();
        }

        return [
            'id' => $this->id,
            'text' => $this->text,
            'selected' => $this->selected,
            'questions' => $questions
        ];
    }
}
