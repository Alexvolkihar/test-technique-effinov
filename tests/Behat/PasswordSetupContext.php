<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Entity\MemberAccount;
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

final class PasswordSetupContext implements Context
{
    private HttpKernelBrowser $client;
    private Crawler $crawler;
    private array $formData = [];

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
        $connection->executeStatement('TRUNCATE TABLE validation_tokens RESTART IDENTITY CASCADE');
        $connection->executeStatement('TRUNCATE TABLE member_accounts RESTART IDENTITY CASCADE');
        $connection->executeStatement('TRUNCATE TABLE registration_requests RESTART IDENTITY CASCADE');
    }

    #[Given('an approved member account exists for :email')]
    public function anApprovedMemberAccountExistsFor(string $email): void
    {
        $request = new RegistrationRequest();
        $request->setFirstName('Test')
            ->setLastName('Member')
            ->setAddress('123 Member Street')
            ->setBirthDate(new \DateTime('1990-01-01'))
            ->setSocialSecurityNumber('190010112345678')
            ->setFighterNickname('testfighter')
            ->setFighterCertificationNumber('CERFA-666-111')
            ->setPokemonStarter('Bulbizarre')
            ->setEmailAddress($email)
            ->setStatus('approved');

        $this->entityManager->persist($request);

        $member = new MemberAccount();
        $member->setRegistrationRequest($request)
            ->setEmailAddress($email)
            ->setStatus('awaiting_password')
            ->setApprovedAt(new \DateTime())
            ->setDisplayNumber('FC-99999');

        $this->entityManager->persist($member);
        $this->entityManager->flush();
    }

    #[Given('a valid validation token exists for :email with raw token :rawToken')]
    public function aValidValidationTokenExistsForWithRawToken(string $email, string $rawToken): void
    {
        $member = $this->entityManager->getRepository(MemberAccount::class)->findOneBy(['emailAddress' => $email]);
        if (!$member) {
            throw new \RuntimeException("Member not found for $email");
        }

        $tokenHash = hash('sha256', $rawToken);
        $token = new ValidationToken();
        $token->setMemberAccount($member)
            ->setTokenHash($tokenHash)
            ->setExpiresAt((new \DateTime())->modify('+24 hours'));

        $this->entityManager->persist($token);
        $this->entityManager->flush();
    }

    #[When('I click the validation link with token :rawToken')]
    public function iClickTheValidationLinkWithToken(string $rawToken): void
    {
        $this->crawler = $this->client->request('GET', '/validation/' . $rawToken);
    }

    #[Then('I should be redirected to the password setup page')]
    public function iShouldBeRedirectedToThePasswordSetupPage(): void
    {
        $path = $this->client->getRequest()->getPathInfo();
        if ('/mot-de-passe' !== $path) {
            throw new \RuntimeException(sprintf('Expected path /mot-de-passe, got %s', $path));
        }
    }

    #[Then('I should see the password setup form')]
    public function iShouldSeeThePasswordSetupForm(): void
    {
        $content = $this->client->getResponse()->getContent();
        if (!str_contains($content, 'Finaliser l\'accès')) {
            throw new \RuntimeException('Expected to see the password setup form.');
        }
    }

    #[When('I fill in the password form with :password and confirmation :confirmPassword')]
    public function iFillInThePasswordFormWithAndConfirmation(string $password, string $confirmPassword): void
    {
        $this->formData['password'] = $password;
        $this->formData['confirm_password'] = $confirmPassword;
    }

    #[When('I submit the password form')]
    public function iSubmitThePasswordForm(): void
    {
        $form = $this->crawler->selectButton('Activer mon compte')->form();
        $this->crawler = $this->client->submit($form, $this->formData);
    }

    #[Then('I should be logged in and redirected to the portal page')]
    public function iShouldBeLoggedInAndRedirectedToThePortalPage(): void
    {
        $path = $this->client->getRequest()->getPathInfo();
        if ('/portail' !== $path) {
            throw new \RuntimeException(sprintf('Expected path /portail, got %s', $path));
        }
        $content = $this->client->getResponse()->getContent();
        if (!str_contains($content, 'Bienvenue au Fight Club')) {
            throw new \RuntimeException('Expected response to contain "Bienvenue au Fight Club"');
        }
    }

    #[When('I try to navigate to the portal page')]
    public function iTryToNavigateToThePortalPage(): void
    {
        $this->crawler = $this->client->request('GET', '/portail');
    }

    #[Then('I should be redirected back to the password setup page')]
    public function iShouldBeRedirectedBackToThePasswordSetupPage(): void
    {
        $path = $this->client->getRequest()->getPathInfo();
        if ('/mot-de-passe' !== $path) {
            throw new \RuntimeException(sprintf('Expected redirect back to /mot-de-passe, got %s', $path));
        }
    }

    #[Then('I should see a password setup error :message')]
    public function iShouldSeeAPasswordSetupError(string $message): void
    {
        $content = $this->client->getResponse()->getContent();
        if (!str_contains($content, $message)) {
            throw new \RuntimeException(sprintf('Expected to see error "%s" in response.', $message));
        }
    }
}
