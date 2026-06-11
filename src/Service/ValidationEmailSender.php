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

        $htmlContent = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Activation de compte - Fight Club</title>
            </head>
            <body style="margin: 0; padding: 0; background-color: #09090b; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; color: #f4f4f5; -webkit-font-smoothing: antialiased;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #09090b; padding: 40px 20px;">
                    <tr>
                        <td align="center">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #18181b; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);">
                                <!-- Header -->
                                <tr>
                                    <td align="center" style="padding: 40px 40px 20px 40px; border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                        <span style="font-size: 11px; font-weight: bold; letter-spacing: 0.2em; color: #ef4444; text-transform: uppercase; font-family: monospace;">PROTOCOLE INTERNE // CLASSIFIÉ</span>
                                        <h1 style="margin: 10px 0 0 0; font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #ffffff; text-transform: uppercase;">PORTAIL SÉCURISÉ</h1>
                                    </td>
                                </tr>
                                <!-- Body -->
                                <tr>
                                    <td style="padding: 40px 40px 30px 40px;">
                                        <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; color: #d4d4d8;">
                                            Bonjour {{ nickname }},
                                        </p>
                                        <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; color: #d4d4d8;">
                                            Votre demande d\'admission a été traitée avec succès.
                                        </p>
                                        <div style="margin: 30px 0; padding: 20px; background-color: rgba(239, 68, 68, 0.05); border-left: 4px solid #ef4444; border-radius: 8px;">
                                            <p style="margin: 0 0 8px 0; font-size: 14px; color: #a1a1aa; font-family: monospace;">IDENTIFIANT UNIQUE</p>
                                            <p style="margin: 0; font-size: 20px; font-weight: bold; color: #ffffff; font-family: monospace; letter-spacing: 1px;">{{ display_number }}</p>
                                        </div>
                                        <p style="margin: 0 0 30px 0; font-size: 15px; line-height: 1.6; color: #a1a1aa;">
                                            Pour finaliser votre initiation et accéder aux communications chiffrées, veuillez définir votre mot de passe d\'accès unique en cliquant sur le bouton ci-dessous :
                                        </p>
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 30px;">
                                            <tr>
                                                <td align="center">
                                                    <a href="{{ validation_url }}" target="_blank" style="display: inline-block; padding: 14px 32px; font-size: 15px; font-weight: bold; color: #ffffff; text-decoration: none; background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); border-radius: 8px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); transition: all 0.2s ease;">Définir mon mot de passe</a>
                                                </td>
                                            </tr>
                                        </table>
                                        <p style="margin: 0 0 20px 0; font-size: 13px; line-height: 1.5; color: #71717a; font-style: italic; text-align: center;">
                                            Ce lien à usage unique expirera dans 24 heures.
                                        </p>
                                        <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #71717a; text-align: center;">
                                            Si le bouton ci-dessus ne fonctionne pas, copiez-collez l\'adresse suivante dans votre navigateur :<br>
                                            <a href="{{ validation_url }}" style="color: #ef4444; text-decoration: none; word-break: break-all;">{{ validation_url }}</a>
                                        </p>
                                    </td>
                                </tr>
                                <!-- Footer -->
                                <tr>
                                    <td align="center" style="padding: 30px 40px; background-color: #111113; border-top: 1px solid rgba(255, 255, 255, 0.03); color: #52525b; font-size: 12px; line-height: 1.5;">
                                        <p style="margin: 0 0 4px 0; font-weight: bold; color: #71717a; text-transform: uppercase; letter-spacing: 1px;">RÈGLE N°1</p>
                                        <p style="margin: 0 0 12px 0; font-style: italic;">"Il est interdit de parler du Fight Club."</p>
                                        <p style="margin: 0 0 4px 0; font-weight: bold; color: #71717a; text-transform: uppercase; letter-spacing: 1px;">RÈGLE N°2</p>
                                        <p style="margin: 0 0 16px 0; font-style: italic;">"Il est INTERDIT de parler du Fight Club."</p>
                                        <p style="margin: 0; font-family: monospace; letter-spacing: 1px;">FIGHT CLUB PORTAL // SÉCURITÉ DE L\'INFORMATION</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>';

        $textContent = "[CONFIDENTIEL] PROTOCOLE D'ACCÈS - FIGHT CLUB\n"
            . "--------------------------------------------------\n"
            . "Bonjour {{ nickname }},\n\n"
            . "Votre demande d'admission a été traitée avec succès.\n"
            . "Vous êtes désormais enregistré sous l'identifiant unique : {{ display_number }}\n\n"
            . "Pour finaliser votre initiation et accéder aux communications chiffrées, veuillez définir votre mot de passe d'accès unique en visitant le lien suivant :\n"
            . "{{ validation_url }}\n\n"
            . "Ce lien à usage unique expirera dans 24 heures.\n\n"
            . "---\n"
            . "RÈGLE N°1 : Il est interdit de parler du Fight Club.\n"
            . "RÈGLE N°2 : Il est INTERDIT de parler du Fight Club.\n"
            . "FIGHT CLUB PORTAL // SÉCURITÉ DE L'INFORMATION";

        $htmlContent = str_replace(
            ['{{ nickname }}', '{{ display_number }}', '{{ validation_url }}'],
            [$member->getNickname() ?? 'recrue', $member->getDisplayNumber(), $validationUrl],
            $htmlContent
        );

        $textContent = str_replace(
            ['{{ nickname }}', '{{ display_number }}', '{{ validation_url }}'],
            [$member->getNickname() ?? 'recrue', $member->getDisplayNumber(), $validationUrl],
            $textContent
        );

        $email = (new Email())
            ->from('security@fightclub.secret')
            ->to($member->getEmailAddress())
            ->subject('[CONFIDENTIEL] Protocole d\'accès - Fight Club')
            ->html($htmlContent)
            ->text($textContent);

        $this->mailer->send($email);
    }
}

