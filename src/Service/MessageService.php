<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\MemberAccount;
use App\Entity\Message;
use App\Exception\InvalidMessageException;
use App\Repository\MemberAccountRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;

class MessageService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageRepository $messageRepository,
        private MemberAccountRepository $memberAccountRepository
    ) {
    }

    /**
     * @return MemberAccount[]
     */
    public function getActiveContacts(MemberAccount $member): array
    {
        $allMessages = $this->messageRepository->findMessagesForMember($member);
        $activeContacts = [];

        foreach ($allMessages as $msg) {
            $otherUser = $msg->getSender()->getId() === $member->getId() ? $msg->getRecipient() : $msg->getSender();
            if ('active' !== $otherUser->getStatus()) {
                continue;
            }
            if (!isset($activeContacts[$otherUser->getId()])) {
                $activeContacts[$otherUser->getId()] = $otherUser;
            }
        }

        return $activeContacts;
    }

    public function sendMessage(MemberAccount $sender, MemberAccount $recipient, string $body): Message
    {
        $body = trim($body);
        if (empty($body)) {
            throw new InvalidMessageException('Le message ne peut pas être vide.');
        }

        if ('active' !== $recipient->getStatus()) {
            throw new InvalidMessageException('Le destinataire choisi est invalide ou inactif.');
        }

        $message = new Message();
        $message->setSender($sender)
            ->setRecipient($recipient)
            ->setBody($body);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    /**
     * @param MemberAccount[] $activeContacts
     * @return MemberAccount[]
     */
    public function getPossibleRecipients(MemberAccount $member, array $activeContacts): array
    {
        $allActiveMembers = $this->memberAccountRepository->findActiveMembersExcept($member);
        
        return array_filter($allActiveMembers, function(MemberAccount $m) use ($activeContacts) {
            return !isset($activeContacts[$m->getId()]);
        });
    }
}
