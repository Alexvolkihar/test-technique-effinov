<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\MemberAccount;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ValidationEmailSender
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $router
    ) {
    }

    public function sendValidationEmail(MemberAccount $member, string $rawToken): void
    {
        $validationUrl = $this->router->generate(
            'app_validation_landing',
            ['token' => $rawToken],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new Email())
            ->from('security@fightclub.secret')
            ->to($member->getEmailAddress())
            ->subject('Bienvenue au Fight Club - Finalisez votre inscription')
            ->html(sprintf(
                '<p>Bonjour,</p><p>Votre candidature a été validée. Veuillez cliquer sur le lien ci-dessous pour définir votre mot de passe et accéder au portail membre :</p><p><a href="%s">%s</a></p><p>Ce lien expirera dans 24 heures.</p>',
                $validationUrl,
                $validationUrl
            ));

        $this->mailer->send($email);
    }
}
