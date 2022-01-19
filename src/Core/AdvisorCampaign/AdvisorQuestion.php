<?php

namespace Elio\FactFinder\Core\AdvisorCampaign;

use Shopware\Core\Framework\Struct\Struct;

class AdvisorQuestion
{
    private ?string $id = null;
    private ?string $text = null;
    private ?bool $visible = null;
    /**
     * @var AdvisorAnswer[]
     */
    private array $answers = [];

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
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     *
     * @return $this
     */
    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @return array
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }

    /**
     * @param array $answers
     *
     * @return $this
     */
    public function setAnswers(array $answers): self
    {
        $this->answers = $answers;
        return $this;
    }

    public function addAnswer(AdvisorAnswer $answer): void
    {
        $this->answers[] = $answer;
    }

    public function toArray(): array
    {
        $answers = [];
        foreach ($this->answers as $answer) {
            $answers[] = $answer->toArray();
        }

        return [
            'id' => $this->id,
            'text' => $this->text,
            'visible' => $this->visible,
            'answers' => $answers
        ];
    }
}
