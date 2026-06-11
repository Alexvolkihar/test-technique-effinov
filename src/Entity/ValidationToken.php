<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ValidationTokenRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ValidationTokenRepository::class)]
#[ORM\Table(name: 'validation_tokens')]
class ValidationToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MemberAccount::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?MemberAccount $memberAccount = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $tokenHash = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $expiresAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $consumedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMemberAccount(): ?MemberAccount
    {
        return $this->memberAccount;
    }

    public function setMemberAccount(MemberAccount $memberAccount): self
    {
        $this->memberAccount = $memberAccount;
        return $this;
    }

    public function getTokenHash(): ?string
    {
        return $this->tokenHash;
    }

    public function setTokenHash(string $tokenHash): self
    {
        $this->tokenHash = $tokenHash;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getConsumedAt(): ?\DateTimeInterface
    {
        return $this->consumedAt;
    }

    public function setConsumedAt(?\DateTimeInterface $consumedAt): self
    {
        $this->consumedAt = $consumedAt;
        return $this;
    }
}
