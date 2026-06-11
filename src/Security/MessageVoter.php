<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Message;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MessageVoter extends Voter
{
    public const VIEW = 'view';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::VIEW === $attribute && $subject instanceof Message;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?\Symfony\Component\Security\Core\Authorization\Voter\Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ('active' !== $user->getStatus()) {
            return false;
        }

        /** @var Message $message */
        $message = $subject;

        return $user->getId() === $message->getSender()->getId()
            || $user->getId() === $message->getRecipient()->getId();
    }
}
