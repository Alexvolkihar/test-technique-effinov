<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use App\Repository\MemberAccountRepository;

class MemberProvider implements UserProviderInterface
{
    public function __construct(
        private MemberAccountRepository $memberAccountRepository
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $memberAccount = $this->memberAccountRepository->findOneBy(['emailAddress' => $identifier]);
        if (!$memberAccount) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        return new User(
            $memberAccount->getId(),
            $memberAccount->getEmailAddress(),
            $memberAccount->getPasswordHash(),
            $memberAccount->getStatus(),
            ['ROLE_USER']
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}
