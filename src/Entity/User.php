<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\Column(type="date")
     */
    private $joinedDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $agentLevel;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $closedTickets;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $reopenedTickets;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $escalatedTickets;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="assignedManager")
     */
    private $agents;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="agents")
     */
    private $assignedManager;

    /**
     * @ORM\OneToMany(targetEntity=Ticket::class, mappedBy="ticketOwner")
     */
    private $tickets;

    /**
     * @ORM\OneToMany(targetEntity=Ticket::class, mappedBy="assignedAgent")
     */
    private $assignedTickets;

    /**
     * @ORM\OneToMany(targetEntity=Ticket::class, mappedBy="closedBy")
     */
    private $resolvedTickets;

    public function __construct()
    {
        $this->assignedManager = new ArrayCollection();
        $this->tickets = new ArrayCollection();
        $this->assignedTickets = new ArrayCollection();
        $this->resolvedTickets = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->firstName; // TODO: Implement __toString() method.
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        // ROLE_USER is used for customers as lowest level.
        //$roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getJoinedDate(): ?\DateTimeInterface
    {
        return $this->joinedDate;
    }

    public function setJoinedDate(\DateTimeInterface $joinedDate): self
    {
        $this->joinedDate = $joinedDate;

        return $this;
    }

    public function getAgentLevel(): ?int
    {
        return $this->agentLevel;
    }

    public function setAgentLevel(?int $agentLevel): self
    {
        $this->agentLevel = $agentLevel;

        return $this;
    }

    public function getClosedTickets(): ?int
    {
        return $this->closedTickets;
    }

    public function setClosedTickets(?int $closedTickets): self
    {
        $this->closedTickets = $closedTickets;

        return $this;
    }

    public function getReopenedTickets(): ?int
    {
        return $this->reopenedTickets;
    }

    public function setReopenedTickets(?int $reopenedTickets): self
    {
        $this->reopenedTickets = $reopenedTickets;

        return $this;
    }

    public function getEscalatedTickets(): ?int
    {
        return $this->escalatedTickets;
    }

    public function setEscalatedTickets(?int $escalatedTickets): self
    {
        $this->escalatedTickets = $escalatedTickets;

        return $this;
    }

    public function getAgents(): ?self
    {
        return $this->agents;
    }

    public function setAgents(?self $agents): self
    {
        $this->agents = $agents;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getAssignedManager(): Collection
    {
        return $this->assignedManager;
    }

    public function addAssignedManager(self $assignedManager): self
    {
        if (!$this->assignedManager->contains($assignedManager)) {
            $this->assignedManager[] = $assignedManager;
            $assignedManager->setAgents($this);
        }

        return $this;
    }

    public function removeAssignedManager(self $assignedManager): self
    {
        if ($this->assignedManager->removeElement($assignedManager)) {
            // set the owning side to null (unless already changed)
            if ($assignedManager->getAgents() === $this) {
                $assignedManager->setAgents(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Ticket[]
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets[] = $ticket;
            $ticket->setTicketOwner($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getTicketOwner() === $this) {
                $ticket->setTicketOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Ticket[]
     */
    public function getAssignedTickets(): Collection
    {
        return $this->assignedTickets;
    }

    public function addAssignedTicket(Ticket $assignedTicket): self
    {
        if (!$this->assignedTickets->contains($assignedTicket)) {
            $this->assignedTickets[] = $assignedTicket;
            $assignedTicket->setAssignedAgent($this);
        }

        return $this;
    }

    public function removeAssignedTicket(Ticket $assignedTicket): self
    {
        if ($this->assignedTickets->removeElement($assignedTicket)) {
            // set the owning side to null (unless already changed)
            if ($assignedTicket->getAssignedAgent() === $this) {
                $assignedTicket->setAssignedAgent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Ticket[]
     */
    public function getResolvedTickets(): Collection
    {
        return $this->resolvedTickets;
    }

    public function addResolvedTicket(Ticket $resolvedTicket): self
    {
        if (!$this->resolvedTickets->contains($resolvedTicket)) {
            $this->resolvedTickets[] = $resolvedTicket;
            $resolvedTicket->setClosedBy($this);
        }

        return $this;
    }

    public function removeResolvedTicket(Ticket $resolvedTicket): self
    {
        if ($this->resolvedTickets->removeElement($resolvedTicket)) {
            // set the owning side to null (unless already changed)
            if ($resolvedTicket->getClosedBy() === $this) {
                $resolvedTicket->setClosedBy(null);
            }
        }

        return $this;
    }
}
