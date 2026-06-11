<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\MemberAccount;
use App\Exception\InvalidMessageException;
use App\Repository\MemberAccountRepository;
use App\Repository\MessageRepository;
use App\Service\MessageService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class MessageServiceTest extends TestCase
{
    public function testSendMessageEmptyBodyThrowsException(): void
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $messageRepository = $this->createStub(MessageRepository::class);
        $memberRepository = $this->createStub(MemberAccountRepository::class);

        $service = new MessageService($entityManager, $messageRepository, $memberRepository);

        $sender = new MemberAccount();
        $recipient = new MemberAccount();
        $recipient->setStatus('active');

        $this->expectException(InvalidMessageException::class);
        $service->sendMessage($sender, $recipient, '  ');
    }
}
