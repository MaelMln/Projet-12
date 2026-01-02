<?php

namespace App\Entity;

use App\Repository\AdviceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdviceRepository::class)]
class Advice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Content is required')]
    private ?string $content = null;

    /**
     * @var array<int> Array of months (1-12) when this advice applies
     */
    #[ORM\Column(type: 'json')]
    #[Assert\NotBlank(message: 'At least one month must be specified')]
    #[Assert\All([
        new Assert\Range(min: 1, max: 12, notInRangeMessage: 'Month must be between 1 and 12')
    ])]
    private array $months = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return array<int>
     */
    public function getMonths(): array
    {
        return $this->months;
    }

    /**
     * @param array<int> $months
     */
    public function setMonths(array $months): static
    {
        $this->months = array_unique(array_map('intval', $months));
        sort($this->months);

        return $this;
    }

    /**
     * Check if this advice applies to a specific month
     */
    public function appliesToMonth(int $month): bool
    {
        return in_array($month, $this->months, true);
    }
}
