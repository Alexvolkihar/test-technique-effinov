<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\MemberAccount;
use App\Entity\RegistrationRequest;
use App\Entity\ValidationToken;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationReviewService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidationEmailSender $emailSender
    ) {
    }

    public function approve(RegistrationRequest $request): MemberAccount
    {
        if ('pending' !== $request->getStatus()) {
            throw new \LogicException('Only pending registration requests can be reviewed.');
        }

        $request->setStatus('approved');
        $request->setReviewedAt(new \DateTime());

        $member = new MemberAccount();
        $member->setRegistrationRequest($request)
            ->setEmailAddress($request->getEmailAddress())
            ->setStatus('awaiting_password')
            ->setApprovedAt(new \DateTime())
            ->setDisplayNumber('FC-' . str_pad((string)$request->getId(), 5, '0', STR_PAD_LEFT));

        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);

        $token = new ValidationToken();
        $token->setMemberAccount($member)
            ->setTokenHash($tokenHash)
            ->setExpiresAt((new \DateTime())->modify('+24 hours'));

        $this->entityManager->persist($member);
        $this->entityManager->persist($token);
        $this->entityManager->flush();
        $this->emailSender->sendValidationEmail($member, $rawToken);

        return $member;
    }

    public function reject(RegistrationRequest $request, ?string $reason): void
    {
        if ('pending' !== $request->getStatus()) {
            throw new \LogicException('Only pending registration requests can be reviewed.');
        }

        $request->setStatus('rejected');
        $request->setReviewedAt(new \DateTime());
        $request->setReviewNote($reason);

        $this->entityManager->flush();
    }
}
