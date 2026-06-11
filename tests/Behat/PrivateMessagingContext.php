<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Entity\MemberAccount;
use App\Entity\Message;
use App\Entity\RegistrationRequest;
use App\Entity\ValidationToken;
use Behat\Behat\Context\Context;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class PrivateMessagingContext implements Context
{
    private HttpKernelBrowser $client;
    private Crawler $crawler;

    public function __construct(
        HttpKernelInterface $httpKernel,
        private EntityManagerInterface $entityManager
    ) {
        $this->client = new HttpKernelBrowser($httpKernel);
        $this->client->followRedirects(true);
    }

    #[BeforeScenario]
    public function clearDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('TRUNCATE TABLE messages RESTART IDENTITY CASCADE');
        $connection->executeStatement('TRUNCATE TABLE validation_tokens RESTART IDENTITY CASCADE');
        $connection->executeStatement('TRUNCATE TABLE member_accounts RESTART IDENTITY CASCADE');
        $connection->executeStatement('TRUNCATE TABLE registration_requests RESTART IDENTITY CASCADE');
    }

    #[Given('an active member account exists for :email with display number :displayNumber')]
    public function anActiveMemberAccountExistsForWithDisplayNumber(string $email, string $displayNumber): void
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
        $this->entityManager->persist($request);

        $member = new MemberAccount();
        $member->setRegistrationRequest($request)
            ->setEmailAddress($email)
            ->setStatus('active')
            ->setApprovedAt(new \DateTime())
            ->setPasswordSetAt(new \DateTime())
            ->setPasswordHash('hashed_password')
            ->setDisplayNumber($displayNumber);
        $this->entityManager->persist($member);
        $this->entityManager->flush();
    }

    #[Given('a private message :body exists from :senderEmail to :recipientEmail')]
    public function aPrivateMessageExistsFromTo(string $body, string $senderEmail, string $recipientEmail): void
    {
        $sender = $this->entityManager->getRepository(MemberAccount::class)->findOneBy(['emailAddress' => $senderEmail]);
        $recipient = $this->entityManager->getRepository(MemberAccount::class)->findOneBy(['emailAddress' => $recipientEmail]);

        if (!$sender || !$recipient) {
            throw new \RuntimeException("Sender or recipient not found.");
        }

        $message = new Message();
        $message->setSender($sender)
            ->setRecipient($recipient)
            ->setBody($body);

        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }

    #[When('I log in as :email')]
    public function iLogInAs(string $email): void
    {
        $member = $this->entityManager->getRepository(MemberAccount::class)->findOneBy(['emailAddress' => $email]);
        if (!$member) {
            throw new \RuntimeException("Member not found for $email");
        }

        // Delete existing tokens to ensure we create a fresh one with a known raw token
        $this->entityManager->getConnection()->executeStatement('DELETE FROM validation_tokens WHERE member_account_id = ?', [$member->getId()]);

        $rawToken = 'logintoken_' . random_int(1000, 9999);
        $tokenHash = hash('sha256', $rawToken);
        $token = new ValidationToken();
        $token->setMemberAccount($member)
            ->setTokenHash($tokenHash)
            ->setExpiresAt((new \DateTime())->modify('+24 hours'));
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        // Temporarily change status to awaiting_password so validation controller allows login
        $originalStatus = $member->getStatus();
        $member->setStatus('awaiting_password');
        $this->entityManager->flush();

        // Perform GET request to validation URL to authenticate client session
        $this->client->request('GET', '/validation/' . $rawToken);

        // Restore original status (active)
        $member->setStatus($originalStatus);
        $this->entityManager->flush();
    }

    #[When('I go to the messages page')]
    public function iGoToTheMessagesPage(): void
    {
        $this->crawler = $this->client->request('GET', '/messages');
    }

    #[Then('I should see the messaging page')]
    public function iShouldSeeTheMessagingPage(): void
    {
        $content = $this->client->getResponse()->getContent();
        if (!str_contains($content, 'Fil de discussion')) {
            throw new \RuntimeException('Expected to see the messaging page.');
        }
    }

    #[When('I send a message :body to member :displayNumber')]
    public function iSendMessageToMember(string $body, string $displayNumber): void
    {
        $recipient = $this->entityManager->getRepository(MemberAccount::class)->findOneBy(['displayNumber' => $displayNumber]);
        if (!$recipient) {
            throw new \RuntimeException("Recipient with display number $displayNumber not found.");
        }

        $form = $this->crawler->selectButton('Envoyer le message')->form();
        $this->crawler = $this->client->submit($form, [
            'recipient_id' => $recipient->getId(),
            'body' => $body,
        ]);
    }

    #[Then('I should see the message :body in the chat thread as sent to :displayNumber')]
    public function iShouldSeeTheMessageInTheChatThreadAsSentTo(string $body, string $displayNumber): void
    {
        $content = $this->client->getResponse()->getContent();
        if (!str_contains($content, 'À : ' . $displayNumber)) {
            throw new \RuntimeException("Expected message recipient marker 'À : $displayNumber' in thread.");
        }
        if (!str_contains($content, $body)) {
            throw new \RuntimeException("Expected message body '$body' in thread.");
        }
    }

    #[Then('I should not see the message :body')]
    public function iShouldNotSeeTheMessage(string $body): void
    {
        $content = $this->client->getResponse()->getContent();
        if (str_contains($content, $body)) {
            throw new \RuntimeException("Expected message body '$body' to NOT be visible in thread, but it was found.");
        }
    }

    #[Then('I should see the active conversation with :displayNumber in the sidebar')]
    public function iShouldSeeTheActiveConversationWithInTheSidebar(string $displayNumber): void
    {
        $content = $this->client->getResponse()->getContent();
        if (!str_contains($content, 'class="list-group-item') || !str_contains($content, $displayNumber)) {
            throw new \RuntimeException(sprintf('Expected conversation with %s in sidebar.', $displayNumber));
        }
    }

    #[When('I send a message :body to the active contact')]
    public function iSendMessageToTheActiveContact(string $body): void
    {
        $form = $this->crawler->selectButton('Envoyer')->form();
        $this->crawler = $this->client->submit($form, [
            'body' => $body,
        ]);
    }

    #[Then('the sidebar should not contain conversation with :displayNumber')]
    public function theSidebarShouldNotContainConversationWith(string $displayNumber): void
    {
        $content = $this->client->getResponse()->getContent();
        if (str_contains($content, 'class="list-group-item') && str_contains($content, $displayNumber)) {
            throw new \RuntimeException(sprintf('Did not expect conversation with %s in sidebar.', $displayNumber));
        }
    }

    #[When('I start a new conversation with :displayNumber and body :body')]
    public function iStartANewConversationWithAndBody(string $displayNumber, string $body): void
    {
        $recipient = $this->entityManager->getRepository(MemberAccount::class)->findOneBy(['displayNumber' => $displayNumber]);
        if (!$recipient) {
            throw new \RuntimeException("Recipient not found.");
        }

        $form = $this->crawler->selectButton('Démarrer la discussion')->form();
        $this->crawler = $this->client->submit($form, [
            'recipient_id' => $recipient->getId(),
            'body' => $body,
        ]);
    }
}
