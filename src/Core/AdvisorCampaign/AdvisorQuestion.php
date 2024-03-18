<?php

namespace Elio\ElioDataDiscovery\Core\AdvisorCampaign;

use Shopware\Core\Framework\Struct\Struct;

/**
 * Class AdvisorQuestion
 * @package Elio\ElioDataDiscovery\Core\AdvisorCampaign
 * @author Ralf Frommherz
 */
class AdvisorQuestion extends Struct
{
    protected ?string $id = null;
    protected ?string $text = null;
    protected bool $visible = false;
    /**
     * @var AdvisorAnswer[]
     */
    protected array $answers = [];

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

    /**
     * Returns the selected answer
     *
     * @return AdvisorAnswer|null
     */
    public function getSelectedAnswer(): ?AdvisorAnswer
    {
        foreach ($this->answers as $answer) {
            if ($answer->isSelected()) {
                return $answer;
            }
        }

        return null;
    }
}
