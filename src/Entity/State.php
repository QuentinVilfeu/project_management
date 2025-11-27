<?php

namespace App\Entity;

use App\Repository\StateRepository;
use DH\Auditor\Provider\Doctrine\Auditing\Annotation as Audit;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StateRepository::class)]
#[UniqueEntity(fields: ['title'], message: 'This state already exists')]
#[UniqueEntity(fields: ['weight'], message: 'This weight already exists')]
#[Audit\Auditable]
class State
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 2)]
    #[Assert\NotNull(message: 'Title should not be null')]
    private ?string $title = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotNull(message: 'Color should not be null')]
    #[Assert\Regex(
        pattern: '/^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/',
        message: 'The value "{{ value }}" is not a valid hexadecimal color (expected format: #RRGGBB or #RGB)'
    )]
    private ?string $color = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Weight should not be null')]
    #[Assert\PositiveOrZero(message: 'Weight must be a positive integer')]
    private ?int $weight = null;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'state')]
    private Collection $tasks;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isClosingState;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getTitle();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setState($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getState() === $this) {
                $task->setState(null);
            }
        }

        return $this;
    }

    public function getIsClosingState(): ?bool
    {
        return $this->isClosingState;
    }

    public function setIsClosingState(bool $isClosingState): static
    {
        $this->isClosingState = $isClosingState;

        return $this;
    }
}
