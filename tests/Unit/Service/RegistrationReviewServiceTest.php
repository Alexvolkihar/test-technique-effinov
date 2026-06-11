<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\RegistrationRequest;
use App\Service\RegistrationReviewService;
use App\Service\ValidationEmailSender;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class RegistrationReviewServiceTest extends TestCase
{
    public function testApproveRequest(): void
    {
        $request = new RegistrationRequest();
        $request->setFirstName('Tyler')
            ->setLastName('Durden')
            ->setEmailAddress('tyler@example.com')
            ->setStatus('pending');

        $em = $this->createMock(EntityManagerInterface::class);
        $emailSender = $this->createMock(ValidationEmailSender::class);

        $em->expects($this->exactly(2))
            ->method('persist');
        $em->expects($this->once())
            ->method('flush');

        $emailSender->expects($this->once())
            ->method('sendValidationEmail');

        $service = new RegistrationReviewService($em, $emailSender);
        $member = $service->approve($request);

        $this->assertEquals('approved', $request->getStatus());
        $this->assertEquals('awaiting_password', $member->getStatus());
        $this->assertEquals('tyler@example.com', $member->getEmailAddress());
        $this->assertNotNull($member->getDisplayNumber());
    }

    public function testRejectRequest(): void
    {
        $request = new RegistrationRequest();
        $request->setFirstName('Tyler')
            ->setLastName('Durden')
            ->setStatus('pending');

        $em = $this->createMock(EntityManagerInterface::class);
        $emailSender = $this->createStub(ValidationEmailSender::class);

        $em->expects($this->once())
            ->method('flush');

        $service = new RegistrationReviewService($em, $emailSender);
        $service->reject($request, 'Inadequate background');

        $this->assertEquals('rejected', $request->getStatus());
        $this->assertEquals('Inadequate background', $request->getReviewNote());
    }
}
