<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'messages')]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MemberAccount::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?MemberAccount $sender = null;

    #[ORM\ManyToOne(targetEntity: MemberAccount::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?MemberAccount $recipient = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $body = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): ?MemberAccount
    {
        return $this->sender;
    }

    public function setSender(MemberAccount $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    public function getRecipient(): ?MemberAccount
    {
        return $this->recipient;
    }

    public function setRecipient(MemberAccount $recipient): self
    {
        $this->recipient = $recipient;
        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
