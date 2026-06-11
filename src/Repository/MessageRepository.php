<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MemberAccount;
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * @return Message[]
     */
    public function findMessagesForMember(MemberAccount $member): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.sender = :user OR m.recipient = :user')
            ->setParameter('user', $member)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Message[]
     */
    public function findMessagesBetween(MemberAccount $member, MemberAccount $contact): array
    {
        return $this->createQueryBuilder('m')
            ->where('(m.sender = :user AND m.recipient = :contact) OR (m.sender = :contact AND m.recipient = :user)')
            ->setParameter('user', $member)
            ->setParameter('contact', $contact)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
