<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RegistrationRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Validator\UniqueRegistrationField;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RegistrationRequestRepository::class)]
#[ORM\Table(name: 'registration_requests')]
class RegistrationRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    private ?string $firstName = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'L\'adresse est obligatoire.')]
    private ?string $address = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de naissance est obligatoire.')]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Le numéro de sécurité sociale est obligatoire.')]
    #[Assert\Regex(pattern: '/^[0-9]{15}$/', message: 'Le numéro de sécurité sociale doit être composé de 15 chiffres.')]
    #[UniqueRegistrationField(field: 'socialSecurityNumber', message: 'Ce numéro de sécurité sociale est déjà utilisé.')]
    private ?string $socialSecurityNumber = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'Le pseudo de combattant est obligatoire.')]
    private ?string $fighterNickname = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Le numéro d\'accréditation CERFA 666 est obligatoire.')]
    #[Assert\Regex(pattern: '/^CERFA-666-[a-zA-Z0-9]+$/', message: 'Le numéro d\'accréditation doit respecter le format CERFA-666-XXX.')]
    #[UniqueRegistrationField(field: 'fighterCertificationNumber', message: 'Ce numéro d\'accréditation est déjà utilisé.')]
    private ?string $fighterCertificationNumber = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'Le choix du Pokémon Starter est obligatoire.')]
    #[Assert\Choice(choices: ['Bulbizarre', 'Salamèche', 'Carapuce'], message: 'Pokémon Starter non valide.')]
    private ?string $pokemonStarter = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'L\'adresse email est obligatoire.')]
    #[Assert\Email(message: 'L\'adresse email n\'est pas valide.')]
    private ?string $emailAddress = null;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $status = 'pending';

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $reviewedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reviewNote = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function getSocialSecurityNumber(): ?string
    {
        return $this->socialSecurityNumber;
    }

    public function setSocialSecurityNumber(?string $socialSecurityNumber): self
    {
        $this->socialSecurityNumber = $socialSecurityNumber;
        return $this;
    }

    public function getFighterNickname(): ?string
    {
        return $this->fighterNickname;
    }

    public function setFighterNickname(?string $fighterNickname): self
    {
        $this->fighterNickname = $fighterNickname;
        return $this;
    }

    public function getFighterCertificationNumber(): ?string
    {
        return $this->fighterCertificationNumber;
    }

    public function setFighterCertificationNumber(?string $fighterCertificationNumber): self
    {
        $this->fighterCertificationNumber = $fighterCertificationNumber;
        return $this;
    }

    public function getPokemonStarter(): ?string
    {
        return $this->pokemonStarter;
    }

    public function setPokemonStarter(?string $pokemonStarter): self
    {
        $this->pokemonStarter = $pokemonStarter;
        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;
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

    public function getReviewedAt(): ?\DateTimeInterface
    {
        return $this->reviewedAt;
    }

    public function setReviewedAt(?\DateTimeInterface $reviewedAt): self
    {
        $this->reviewedAt = $reviewedAt;
        return $this;
    }

    public function getReviewNote(): ?string
    {
        return $this->reviewNote;
    }

    public function setReviewNote(?string $reviewNote): self
    {
        $this->reviewNote = $reviewNote;
        return $this;
    }
}
