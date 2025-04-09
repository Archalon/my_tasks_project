<?php

namespace App\Entity;

use App\Enum\TaskStatus;
use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['task:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 128)]
    #[Assert\NotBlank]
    #[Groups(['task:read', 'task:write'])]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Type('string')]
    #[Groups(['task:read', 'task:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    #[Assert\NotNull]
    #[Groups(['task:read', 'task:write'])]
    private ?\DateTimeInterface $deadline = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['task:read', 'task:write'])]
    private bool $completed = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['task:read'])]
    private bool $cancelled = false;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTimeInterface $deadline): static
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;
        return $this;
    }

    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    #[Groups(['task:read'])]
    public function getComputedStatus(): string
    {
        if ($this->cancelled) {
            return 'Cancelada';
        }

        if ($this->completed) {
            return 'Concluída';
        }

        $now = new \DateTime();
        return $this->deadline < $now ? 'Atrasada' : 'Agendada';
    }

    public function cancel(): void
    {
        $this->cancelled = true;
    }

    public function reactivate(): void
    {
        $this->cancelled = false;
    }

}
