<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\MemberAccount;
use App\Entity\ValidationToken;
use App\Exception\ExpiredTokenException;
use App\Repository\ValidationTokenRepository;
use App\Service\MemberActivationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MemberActivationServiceTest extends TestCase
{
    public function testVerifyTokenExpiredThrowsException(): void
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $passwordHasher = $this->createStub(UserPasswordHasherInterface::class);
        $tokenRepository = $this->createStub(ValidationTokenRepository::class);

        $token = new ValidationToken();
        $token->setExpiresAt((new \DateTime())->modify('-1 hour'));
        $token->setMemberAccount(new MemberAccount());

        $tokenRepository->method('findOneBy')->willReturn($token);

        $service = new MemberActivationService($entityManager, $passwordHasher, $tokenRepository);

        $this->expectException(ExpiredTokenException::class);
        $service->verifyToken('dummy');
    }
}
