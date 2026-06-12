<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\MemberAccount;
use App\Entity\RegistrationRequest;
use App\Entity\ValidationToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PasswordSetupControllerTest extends WebTestCase
{
    private $em;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = self::getContainer()->get('doctrine.orm.entity_manager');

        $this->em->getConnection()->executeStatement('TRUNCATE TABLE validation_tokens RESTART IDENTITY CASCADE');
        $this->em->getConnection()->executeStatement('TRUNCATE TABLE member_accounts RESTART IDENTITY CASCADE');
        $this->em->getConnection()->executeStatement('TRUNCATE TABLE registration_requests RESTART IDENTITY CASCADE');
    }

    public function testValidationWithInvalidToken(): void
    {
        $this->client->request('GET', '/validation/invalidtoken');
        $this->assertResponseStatusCodeSame(400);
        $this->assertStringContainsString('Ce lien de validation est invalide.', $this->client->getResponse()->getContent());
    }

    public function testFullPasswordSetupFlow(): void
    {
        // 1. Create a pending registration request
        $request = new RegistrationRequest();
        $request->setFirstName('Tyler')
            ->setLastName('Durden')
            ->setAddress('Paper Street')
            ->setBirthDate(new \DateTime('1980-05-15'))
            ->setSocialSecurityNumber('180051512345695')
            ->setFighterNickname('soap')
            ->setFighterCertificationNumber('CERFA-666-999')
            ->setPokemonStarter('Salamèche')
            ->setEmailAddress('tyler@example.com')
            ->setStatus('approved');
        $this->em->persist($request);

        // 2. Create the associated MemberAccount
        $member = new MemberAccount();
        $member->setRegistrationRequest($request)
            ->setEmailAddress('tyler@example.com')
            ->setStatus('awaiting_password')
            ->setApprovedAt(new \DateTime())
            ->setDisplayNumber('FC-00001');
        $this->em->persist($member);

        // 3. Create the ValidationToken
        $rawToken = 'correcttoken123';
        $tokenHash = hash('sha256', $rawToken);
        $token = new ValidationToken();
        $token->setMemberAccount($member)
            ->setTokenHash($tokenHash)
            ->setExpiresAt((new \DateTime())->modify('+24 hours'));
        $this->em->persist($token);

        $this->em->flush();

        // 4. Click the validation link
        $this->client->request('GET', '/validation/' . $rawToken);
        $this->assertResponseRedirects('/mot-de-passe');
        $this->client->followRedirect();

        // 5. Verify redirect to /mot-de-passe and setup page display
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Finaliser l\'accès', $this->client->getResponse()->getContent());

        // 6. Test Password setup - short password (length < 8)
        $this->client->submitForm('Activer mon compte', [
            'password' => 'short',
            'confirm_password' => 'short',
        ]);
        $this->assertStringContainsString('Le mot de passe doit contenir au moins 8 caractères.', $this->client->getResponse()->getContent());

        // 7. Test Password setup - mismatching passwords
        $this->client->submitForm('Activer mon compte', [
            'password' => 'securepassword123',
            'confirm_password' => 'mismatchpassword123',
        ]);
        $this->assertStringContainsString('Les mots de passe ne correspondent pas.', $this->client->getResponse()->getContent());

        // 8. Test Password setup - success
        $this->client->submitForm('Activer mon compte', [
            'password' => 'securepassword123',
            'confirm_password' => 'securepassword123',
        ]);

        $this->assertResponseRedirects('/portail');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Bienvenue au Fight Club', $this->client->getResponse()->getContent());

        // 9. Verify database changes
        $this->em->clear();
        $updatedMember = $this->em->find(MemberAccount::class, $member->getId());
        $this->assertEquals('active', $updatedMember->getStatus());
        $this->assertNotNull($updatedMember->getPasswordHash());
        $this->assertTrue(password_verify('securepassword123', $updatedMember->getPasswordHash()));

        $updatedToken = $this->em->find(ValidationToken::class, $token->getId());
        $this->assertNotNull($updatedToken->getConsumedAt());
    }

    public function testBlockPortalAccessForAwaitingPasswordUsers(): void
    {
        // 1. Create a pending registration request
        $request = new RegistrationRequest();
        $request->setFirstName('Marla')
            ->setLastName('Singer')
            ->setAddress('Wilmington')
            ->setBirthDate(new \DateTime('1985-08-20'))
            ->setSocialSecurityNumber('285081512345642')
            ->setFighterNickname('marla')
            ->setFighterCertificationNumber('CERFA-666-888')
            ->setPokemonStarter('Carapuce')
            ->setEmailAddress('marla@example.com')
            ->setStatus('approved');
        $this->em->persist($request);

        // 2. Create the associated MemberAccount
        $member = new MemberAccount();
        $member->setRegistrationRequest($request)
            ->setEmailAddress('marla@example.com')
            ->setStatus('awaiting_password')
            ->setApprovedAt(new \DateTime())
            ->setDisplayNumber('FC-00002');
        $this->em->persist($member);

        // 3. Create the ValidationToken
        $rawToken = 'token456';
        $tokenHash = hash('sha256', $rawToken);
        $token = new ValidationToken();
        $token->setMemberAccount($member)
            ->setTokenHash($tokenHash)
            ->setExpiresAt((new \DateTime())->modify('+24 hours'));
        $this->em->persist($token);

        $this->em->flush();

        // Log the user in programmatically by going to validation link
        $this->client->request('GET', '/validation/' . $rawToken);
        $this->assertResponseRedirects('/mot-de-passe');
        $this->client->followRedirect();

        // Tries to access the portal /portail
        $this->client->request('GET', '/portail');
        // Must be redirected back to /mot-de-passe
        $this->assertResponseRedirects('/mot-de-passe');
    }
}
