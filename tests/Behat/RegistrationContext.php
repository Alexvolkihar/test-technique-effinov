<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Entity\RegistrationRequest;
use App\Repository\RegistrationRequestRepository;
use Behat\Behat\Context\Context;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class RegistrationContext implements Context
{
    private HttpKernelBrowser $client;
    private Crawler $crawler;
    private array $formData = [];

    public function __construct(
        HttpKernelInterface $httpKernel,
        private EntityManagerInterface $entityManager,
        private RegistrationRequestRepository $repository
    ) {
        $this->client = new HttpKernelBrowser($httpKernel);
        $this->client->followRedirects(true);
    }

    #[BeforeScenario]
    public function clearDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('TRUNCATE TABLE registration_requests RESTART IDENTITY CASCADE');
    }

    #[When('I go to the registration page')]
    public function iGoToTheRegistrationPage(): void
    {
        $this->crawler = $this->client->request('GET', '/inscription');
        $statusCode = $this->client->getResponse()->getStatusCode();
        if (200 !== $statusCode) {
            throw new \RuntimeException(sprintf('Expected status code 200, got %d', $statusCode));
        }
    }

    #[When('I fill in :field with :value')]
    public function iFillInWith(string $field, string $value): void
    {
        $name = $this->mapFieldToFormName($field);
        $this->formData[$name] = $value;
    }

    #[When('I select :value from :field')]
    public function iSelectFrom(string $value, string $field): void
    {
        $name = $this->mapFieldToFormName($field);
        $this->formData[$name] = $value;
    }

    #[When('I submit the registration form')]
    public function iSubmitTheRegistrationForm(): void
    {
        // Find form and submit with accumulated data
        $form = $this->crawler->selectButton('Soumettre')->form();
        $this->crawler = $this->client->submit($form, $this->formData);
    }

    #[Then('I should see a success message')]
    public function iShouldSeeASuccessMessage(): void
    {
        $content = $this->client->getResponse()->getContent();
        if (!str_contains($content, 'Demande enregistrée')) {
            throw new \RuntimeException('Expected response to contain "Demande enregistrée".');
        }
    }

    #[Then('a pending registration request should exist for :email')]
    public function aPendingRegistrationRequestShouldExistFor(string $email): void
    {
        // Clear EntityManager to avoid cache issues
        $this->entityManager->clear();
        $request = $this->repository->findOneBy(['emailAddress' => $email]);

        if (null === $request) {
            throw new \RuntimeException("Registration request not found for email $email");
        }
        if ('pending' !== $request->getStatus()) {
            throw new \RuntimeException(sprintf("Expected status 'pending', got '%s'", $request->getStatus()));
        }
    }

    #[Then('I should see validation errors')]
    public function iShouldSeeValidationErrors(): void
    {
        $crawler = new Crawler($this->client->getResponse()->getContent());
        $errorElements = $crawler->filter('.text-danger li');
        if ($errorElements->count() === 0) {
            throw new \RuntimeException('Expected to see validation errors (elements matching .text-danger li), but found none.');
        }
    }

    #[Given('a registration request exists with SSN :ssn')]
    public function aRegistrationRequestExistsWithSSN(string $ssn): void
    {
        $request = new RegistrationRequest();
        $request->setFirstName('Existing')
            ->setLastName('User')
            ->setAddress('123 Existing Street')
            ->setBirthDate(new \DateTime('1990-01-01'))
            ->setSocialSecurityNumber($ssn)
            ->setFighterNickname('existing')
            ->setFighterCertificationNumber('CERFA-666-888')
            ->setPokemonStarter('Bulbizarre')
            ->setEmailAddress('existing.user@example.com')
            ->setStatus('pending');

        $this->entityManager->persist($request);
        $this->entityManager->flush();
    }

    #[Then('I should see the duplicate error message :message')]
    public function iShouldSeeTheDuplicateErrorMessage(string $message): void
    {
        $content = html_entity_decode($this->client->getResponse()->getContent(), ENT_QUOTES, 'UTF-8');
        if (!str_contains($content, $message)) {
            throw new \RuntimeException(sprintf('Expected response to contain "%s".', $message));
        }
    }

    private function mapFieldToFormName(string $field): string
    {
        return match ($field) {
            'Nom' => 'registration[lastName]',
            'Prénom' => 'registration[firstName]',
            'Adresse' => 'registration[address]',
            'Date de naissance' => 'registration[birthDate]',
            'Numéro de sécurité sociale' => 'registration[socialSecurityNumber]',
            'Pseudo' => 'registration[fighterNickname]',
            'Numéro d\'accréditation' => 'registration[fighterCertificationNumber]',
            'Starter Pokémon' => 'registration[pokemonStarter]',
            'Adresse email' => 'registration[emailAddress]',
            default => throw new \InvalidArgumentException("Unknown field: $field"),
        };
    }
}
