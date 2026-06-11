<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MemberAccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberAccountRepository::class)]
#[ORM\Table(name: 'member_accounts')]
class MemberAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: RegistrationRequest::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?RegistrationRequest $registrationRequest = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $displayNumber = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $emailAddress = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $passwordHash = null;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $status = 'awaiting_password';

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $approvedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $passwordSetAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegistrationRequest(): ?RegistrationRequest
    {
        return $this->registrationRequest;
    }

    public function setRegistrationRequest(RegistrationRequest $registrationRequest): self
    {
        $this->registrationRequest = $registrationRequest;
        return $this;
    }

    public function getDisplayNumber(): ?string
    {
        return $this->displayNumber;
    }

    public function setDisplayNumber(string $displayNumber): self
    {
        $this->displayNumber = $displayNumber;
        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;
        return $this;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(?string $passwordHash): self
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getApprovedAt(): ?\DateTimeInterface
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(\DateTimeInterface $approvedAt): self
    {
        $this->approvedAt = $approvedAt;
        return $this;
    }

    public function getPasswordSetAt(): ?\DateTimeInterface
    {
        return $this->passwordSetAt;
    }

    public function setPasswordSetAt(?\DateTimeInterface $passwordSetAt): self
    {
        $this->passwordSetAt = $passwordSetAt;
        return $this;
    }
}
