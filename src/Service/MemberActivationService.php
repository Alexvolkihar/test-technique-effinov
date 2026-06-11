<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\MemberAccount;
use App\Entity\ValidationToken;
use App\Exception\AlreadyConsumedTokenException;
use App\Exception\ExpiredTokenException;
use App\Exception\InvalidAccountStatusException;
use App\Exception\InvalidTokenException;
use App\Repository\ValidationTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MemberActivationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidationTokenRepository $tokenRepository
    ) {
    }

    public function verifyToken(string $rawToken): ValidationToken
    {
        $tokenHash = hash('sha256', $rawToken);
        $validationToken = $this->tokenRepository->findOneBy(['tokenHash' => $tokenHash]);

        if (null === $validationToken) {
            throw new InvalidTokenException();
        }

        if (null !== $validationToken->getConsumedAt()) {
            throw new AlreadyConsumedTokenException();
        }

        if ($validationToken->getExpiresAt() < new \DateTime()) {
            throw new ExpiredTokenException();
        }

        $memberAccount = $validationToken->getMemberAccount();
        if ('awaiting_password' !== $memberAccount->getStatus()) {
            throw new InvalidAccountStatusException();
        }

        return $validationToken;
    }

    public function setupPassword(ValidationToken $token, string $password): MemberAccount
    {
        if (strlen($password) < 8) {
            throw new \InvalidArgumentException('Le mot de passe doit contenir au moins 8 caractères.');
        }

        $memberAccount = $token->getMemberAccount();
        
        // Re-create security/User representation to hash password
        $userDummy = new \App\Security\User(
            $memberAccount->getId(),
            $memberAccount->getEmailAddress(),
            '',
            $memberAccount->getStatus()
        );

        $hashedPassword = $this->passwordHasher->hashPassword($userDummy, $password);
        $memberAccount->setPasswordHash($hashedPassword);
        $memberAccount->setStatus('active');
        $memberAccount->setPasswordSetAt(new \DateTime());

        $token->setConsumedAt(new \DateTime());

        $this->entityManager->flush();

        return $memberAccount;
    }
}
