<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\MemberAccount;
use App\Entity\Message;
use App\Entity\RegistrationRequest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MessageControllerTest extends WebTestCase
{
    private $em;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = self::getContainer()->get('doctrine.orm.entity_manager');

        $this->em->getConnection()->executeStatement('TRUNCATE TABLE messages RESTART IDENTITY CASCADE');
        $this->em->getConnection()->executeStatement('TRUNCATE TABLE validation_tokens RESTART IDENTITY CASCADE');
        $this->em->getConnection()->executeStatement('TRUNCATE TABLE member_accounts RESTART IDENTITY CASCADE');
        $this->em->getConnection()->executeStatement('TRUNCATE TABLE registration_requests RESTART IDENTITY CASCADE');
    }

    private function createActiveMember(string $email, string $displayNumber): MemberAccount
    {
        $request = new RegistrationRequest();
        $request->setFirstName('First')
            ->setLastName('Last')
            ->setAddress('Address')
            ->setBirthDate(new \DateTime('1990-01-01'))
            ->setSocialSecurityNumber(str_pad((string)random_int(100000, 999999), 15, '1'))
            ->setFighterNickname('nick_' . $displayNumber)
            ->setFighterCertificationNumber('CERFA-666-' . random_int(1000, 9999))
            ->setPokemonStarter('Salamèche')
            ->setEmailAddress($email)
            ->setStatus('approved');
        $this->em->persist($request);

        $member = new MemberAccount();
        $member->setRegistrationRequest($request)
            ->setEmailAddress($email)
            ->setStatus('active')
            ->setApprovedAt(new \DateTime())
            ->setPasswordSetAt(new \DateTime())
            ->setPasswordHash('hashed_password')
            ->setDisplayNumber($displayNumber);
        $this->em->persist($member);
        $this->em->flush();

        return $member;
    }

    public function testAnonymousAccessRedirectsToLogin(): void
    {
        $this->client->request('GET', '/messages');
        $this->assertResponseRedirects('/login');
    }

    public function testAwaitingPasswordAccessRedirectsToPasswordSetup(): void
    {
        $request = new RegistrationRequest();
        $request->setFirstName('Awaiting')
            ->setLastName('User')
            ->setAddress('Address')
            ->setBirthDate(new \DateTime('1990-01-01'))
            ->setSocialSecurityNumber('180051512345678')
            ->setFighterNickname('awaiting')
            ->setFighterCertificationNumber('CERFA-666-999')
            ->setPokemonStarter('Salamèche')
            ->setEmailAddress('awaiting@example.com')
            ->setStatus('approved');
        $this->em->persist($request);

        $member = new MemberAccount();
        $member->setRegistrationRequest($request)
            ->setEmailAddress('awaiting@example.com')
            ->setStatus('awaiting_password')
            ->setApprovedAt(new \DateTime())
            ->setDisplayNumber('FC-00099');
        $this->em->persist($member);
        $this->em->flush();

        // Login user
        $this->client->loginUser(new \App\Security\User(
            $member->getId(),
            $member->getEmailAddress(),
            $member->getPasswordHash(),
            $member->getStatus(),
            ['ROLE_USER']
        ));

        $this->client->request('GET', '/messages');
        $this->assertResponseRedirects('/mot-de-passe');
    }

    public function testSendMessageSuccessfully(): void
    {
        $sender = $this->createActiveMember('sender@example.com', 'FC-00001');
        $recipient = $this->createActiveMember('recipient@example.com', 'FC-00002');

        $this->client->loginUser(new \App\Security\User(
            $sender->getId(),
            $sender->getEmailAddress(),
            $sender->getPasswordHash(),
            $sender->getStatus(),
            ['ROLE_USER']
        ));

        // Go to messages page
        $this->client->request('GET', '/messages');
        $this->assertResponseIsSuccessful();

        // Submit message form
        $this->client->submitForm('Envoyer le message', [
            'recipient_id' => $recipient->getId(),
            'body' => 'Bonjour, ceci est un secret.',
        ]);

        $this->assertResponseRedirects('/messages');
        $this->client->followRedirect();

        $this->assertStringContainsString('À : FC-00002', $this->client->getResponse()->getContent());
        $this->assertStringContainsString('Bonjour, ceci est un secret.', $this->client->getResponse()->getContent());

        // Verify in DB
        $this->em->clear();
        $messages = $this->em->getRepository(Message::class)->findAll();
        $this->assertCount(1, $messages);
        $this->assertEquals('Bonjour, ceci est un secret.', $messages[0]->getBody());
        $this->assertEquals($sender->getId(), $messages[0]->getSender()->getId());
        $this->assertEquals($recipient->getId(), $messages[0]->getRecipient()->getId());
    }

    public function testMessageVoterEnforcesSecurity(): void
    {
        $member1 = $this->createActiveMember('member1@example.com', 'FC-00011');
        $member2 = $this->createActiveMember('member2@example.com', 'FC-00012');
        $outsider = $this->createActiveMember('outsider@example.com', 'FC-00013');

        // Create message between member 1 and member 2
        $message = new Message();
        $message->setSender($member1)
            ->setRecipient($member2)
            ->setBody('Secret conversation');
        $this->em->persist($message);
        $this->em->flush();

        $security = self::getContainer()->get('security.authorization_checker');

        // Login as outsider
        $this->client->loginUser(new \App\Security\User(
            $outsider->getId(),
            $outsider->getEmailAddress(),
            $outsider->getPasswordHash(),
            $outsider->getStatus(),
            ['ROLE_USER']
        ));

        // Attempting to access messages page as outsider must not display secret conversation
        $this->client->request('GET', '/messages');
        $this->assertResponseIsSuccessful();
        $this->assertStringNotContainsString('Secret conversation', $this->client->getResponse()->getContent());

        // Let's directly test the voter using AuthorizationChecker
        $tokenStorage = self::getContainer()->get('security.token_storage');
        $token = new \Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken(
            new \App\Security\User($outsider->getId(), $outsider->getEmailAddress(), $outsider->getPasswordHash(), $outsider->getStatus(), ['ROLE_USER']),
            'main',
            ['ROLE_USER']
        );
        $tokenStorage->setToken($token);

        $this->assertFalse($security->isGranted('view', $message));

        // Now test as member1
        $token = new \Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken(
            new \App\Security\User($member1->getId(), $member1->getEmailAddress(), $member1->getPasswordHash(), $member1->getStatus(), ['ROLE_USER']),
            'main',
            ['ROLE_USER']
        );
        $tokenStorage->setToken($token);

        $this->assertTrue($security->isGranted('view', $message));
    }
}
