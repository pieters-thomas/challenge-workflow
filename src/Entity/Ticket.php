<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TicketRepository::class)
 */
class Ticket
{
    public const OPEN = 1;
    public const CLOSED = 2;
    public const WONTFIX = 3;


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tickets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ticketOwner;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="assignedTickets")
     */
    private $assignedAgent;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="resolvedTickets")
     */
    private $closedBy;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $opened;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closed;

    /**
     * @ORM\Column(type="smallint")
     */
    private $priority;

    /**
     * @ORM\Column(type="smallint")
     */
    private $level;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="ticketId", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $subject;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->subject;// TODO: Implement __toString() method.
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicketOwner(): ?User
    {
        return $this->ticketOwner;
    }

    public function setTicketOwner(?User $ticketOwner): self
    {
        $this->ticketOwner = $ticketOwner;

        return $this;
    }

    public function getAssignedAgent(): ?User
    {
        return $this->assignedAgent;
    }

    public function setAssignedAgent(?User $assignedAgent): self
    {
        $this->assignedAgent = $assignedAgent;

        return $this;
    }

    public function getClosedBy(): ?User
    {
        return $this->closedBy;
    }

    public function setClosedBy(?User $closedBy): self
    {
        $this->closedBy = $closedBy;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getOpened(): ?\DateTimeInterface
    {
        return $this->opened;
    }

    public function setOpened(\DateTimeInterface $opened): self
    {
        $this->opened = $opened;

        return $this;
    }

    public function getClosed(): ?\DateTimeInterface
    {
        return $this->closed;
    }

    public function setClosed(?\DateTimeInterface $closed): self
    {
        $this->closed = $closed;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setTicketId($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTicketId() === $this) {
                $comment->setTicketId(null);
            }
        }

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
