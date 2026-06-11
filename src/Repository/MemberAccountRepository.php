<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MemberAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MemberAccount>
 */
class MemberAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MemberAccount::class);
    }

    /**
     * @return MemberAccount[]
     */
    public function findActiveMembersExcept(MemberAccount $member): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.status = :status')
            ->andWhere('m.id != :memberId')
            ->setParameter('status', 'active')
            ->setParameter('memberId', $member->getId())
            ->getQuery()
            ->getResult();
    }
}
