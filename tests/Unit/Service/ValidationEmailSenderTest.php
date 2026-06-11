<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\MemberAccount;
use App\Entity\RegistrationRequest;
use App\Service\ValidationEmailSender;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ValidationEmailSenderTest extends TestCase
{
    public function testSendValidationEmail(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $router = $this->createMock(UrlGeneratorInterface::class);

        $request = new RegistrationRequest();
        $request->setFighterNickname('Tyler');

        $member = new MemberAccount();
        $member->setEmailAddress('candidate@example.com');
        $member->setDisplayNumber('FC-12345');
        $member->setRegistrationRequest($request);

        $router->expects($this->once())
            ->method('generate')
            ->with(
                'app_validation_landing',
                ['token' => 'my-raw-token'],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://localhost/validation/my-raw-token');

        $mailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                $this->assertEquals('security@fightclub.secret', $email->getFrom()[0]->getAddress());
                $this->assertEquals('candidate@example.com', $email->getTo()[0]->getAddress());
                $this->assertEquals('[CONFIDENTIEL] Protocole d\'accès - Fight Club', $email->getSubject());

                $html = $email->getHtmlBody();
                $text = $email->getTextBody();

                // Check placeholders replacement
                $this->assertStringContainsString('Tyler', $html);
                $this->assertStringContainsString('FC-12345', $html);
                $this->assertStringContainsString('http://localhost/validation/my-raw-token', $html);
                
                $this->assertStringContainsString('Tyler', $text);
                $this->assertStringContainsString('FC-12345', $text);
                $this->assertStringContainsString('http://localhost/validation/my-raw-token', $text);

                // Check HTML structure and footers
                $this->assertStringContainsString('PROTOCOLE INTERNE // CLASSIFIÉ', $html);
                $this->assertStringContainsString('PORTAIL SÉCURISÉ', $html);
                $this->assertStringContainsString('RÈGLE N°1', $html);
                $this->assertStringContainsString('RÈGLE N°2', $html);
                $this->assertStringContainsString('FIGHT CLUB PORTAL // SÉCURITÉ DE L\'INFORMATION', $html);

                // Check Plain text structure
                $this->assertStringContainsString("[CONFIDENTIEL] PROTOCOLE D'ACCÈS - FIGHT CLUB", $text);
                $this->assertStringContainsString('RÈGLE N°1 : Il est interdit de parler du Fight Club.', $text);
                $this->assertStringContainsString('RÈGLE N°2 : Il est INTERDIT de parler du Fight Club.', $text);
                $this->assertStringContainsString('FIGHT CLUB PORTAL // SÉCURITÉ DE L\'INFORMATION', $text);

                return true;
            }));

        $sender = new ValidationEmailSender($mailer, $router);
        $sender->sendValidationEmail($member, 'my-raw-token');
    }
}
