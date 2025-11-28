<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use DateTimeImmutable;
use DH\Auditor\Provider\Doctrine\Auditing\Annotation as Audit;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[Audit\Auditable]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message: 'Title should not be null', groups: ['Default', 'edit', 'closed'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotNull(message: 'Description should not be null', groups: ['Default', 'edit', 'closed'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Project should not be null', groups: ['Default', 'edit', 'closed'])]
    private ?Project $project = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'task', orphanRemoval: true)]
    private Collection $comments;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'State should not be null', groups: ['Default', 'edit', 'closed', 'reopen'])]
    private ?State $state = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\NotNull(message: 'Priority should not be null', groups: ['Default', 'edit', 'reopen'])]
    private ?Priority $priority = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?User $assignee = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class)]
    private Collection $taskRelated;

    #[ORM\Column(nullable: true)]
    #[Assert\GreaterThanOrEqual(
        value: 'today',
        message: 'The end date cannot be in the past',
    )]
    private ?\DateTime $initialEndDate = null;

    #[ORM\Column(nullable: true)]
    #[Assert\LessThanOrEqual(
        value: 'today',
        message: 'The closing date cannot be in the future',
        groups: ['closing']
    )]
    private ?\DateTime $closingDate = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->taskRelated = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setTask($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTask() === $this) {
                $comment->setTask(null);
            }
        }

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getPriority(): ?Priority
    {
        return $this->priority;
    }

    public function setPriority(?Priority $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getAssignee(): ?User
    {
        return $this->assignee;
    }

    public function setAssignee(?User $assignee): static
    {
        $this->assignee = $assignee;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getTaskRelated(): Collection
    {
        return $this->taskRelated;
    }

    public function addTaskRelated(self $taskRelated): static
    {
        if (!$this->taskRelated->contains($taskRelated)) {
            $this->taskRelated->add($taskRelated);
        }

        return $this;
    }

    public function removeTaskRelated(self $taskRelated): static
    {
        $this->taskRelated->removeElement($taskRelated);

        return $this;
    }

    public function getInitialEndDate(): ?\DateTime
    {
        return $this->initialEndDate;
    }

    public function setInitialEndDate(?\DateTime $initialEndDate): static
    {
        $this->initialEndDate = $initialEndDate;

        return $this;
    }

    public function getClosingDate(): ?\DateTime
    {
        return $this->closingDate;
    }

    public function setClosingDate(?\DateTime $closingDate): static
    {
        $this->closingDate = $closingDate;

        return $this;
    }

    /**
     * Retourne un statut ('danger', 'warning', 'ok' ou 'expired') en fonction
     * de l'écart entre la date de fin initiale et la date d'aujourd'hui.
     *
     * @return string
     */
    public function getDeadlineStatus(): string
    {
        $returnValue = '';

        // Si la date n'est pas définie, on retourne un statut neutre ou une erreur
        if ($this->closingDate !== null) {
            return '';
        }

        // Si la date n'est pas définie, on retourne un statut neutre ou une erreur
        if ($this->initialEndDate === null) {
            return 'n/a';
        }

        $now = new DateTimeImmutable();
        $initialEndDate = $this->initialEndDate;

        // Dif between two date in hours
        $diffInSeconds = $initialEndDate->getTimestamp() - $now->getTimestamp();
        $diffInHours = $diffInSeconds / 3600;

        // Moins de 5 jours restants (5 jours * 24 heures = 120 heures)
        if ($diffInHours < (5 * 24)) {
            $returnValue = 'warning';
        }

        // Moins de 24 heures restantes
        if ($diffInHours < 24) {
            $returnValue = 'danger';
        }

        // Par défaut (plus de 5 jours restants)
        return $returnValue;
    }
}
