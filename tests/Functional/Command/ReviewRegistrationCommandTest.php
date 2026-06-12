<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Entity\RegistrationRequest;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ReviewRegistrationCommandTest extends KernelTestCase
{
    public function testExecuteApproveNonInteractive(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        
        // Clean table
        $em->getConnection()->executeStatement('TRUNCATE TABLE registration_requests RESTART IDENTITY CASCADE');
        
        $request = new RegistrationRequest();
        $request->setFirstName('Tyler')
            ->setLastName('Durden')
            ->setAddress('Paper Street')
            ->setBirthDate(new \DateTime('1980-05-15'))
            ->setSocialSecurityNumber('180051512345695')
            ->setFighterNickname('soap')
            ->setFighterCertificationNumber('CERFA-666-999')
            ->setPokemonStarter('Salamèche')
            ->setEmailAddress('tyler.durden@example.com')
            ->setStatus('pending');
            
        $em->persist($request);
        $em->flush();
        
        $command = $application->find('app:review-registration');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'registrationId' => $request->getId(),
            '--decision' => 'approve'
        ]);
        
        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Candidature approuvée avec succès', $commandTester->getDisplay());
        
        $em->clear();
        $updatedRequest = $em->find(RegistrationRequest::class, $request->getId());
        $this->assertEquals('approved', $updatedRequest->getStatus());
    }

    public function testExecuteInteractiveApprove(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        
        // Clean table
        $em->getConnection()->executeStatement('TRUNCATE TABLE registration_requests RESTART IDENTITY CASCADE');
        
        $request = new RegistrationRequest();
        $request->setFirstName('Tyler')
            ->setLastName('Durden')
            ->setAddress('Paper Street')
            ->setBirthDate(new \DateTime('1980-05-15'))
            ->setSocialSecurityNumber('180051512345695')
            ->setFighterNickname('soap')
            ->setFighterCertificationNumber('CERFA-666-999')
            ->setPokemonStarter('Salamèche')
            ->setEmailAddress('tyler.durden@example.com')
            ->setStatus('pending');
            
        $em->persist($request);
        $em->flush();
        
        $command = $application->find('app:review-registration');
        $commandTester = new CommandTester($command);
        
        // Mock user inputs:
        // 1. Select the first choice (ID 1)
        // 2. Select "Approuver"
        $commandTester->setInputs(['1', 'Approuver']);
        $commandTester->execute([]);
        
        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Candidature approuvée avec succès', $commandTester->getDisplay());
        
        $em->clear();
        $updatedRequest = $em->find(RegistrationRequest::class, $request->getId());
        $this->assertEquals('approved', $updatedRequest->getStatus());
    }

    public function testExecuteInteractiveReject(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        
        // Clean table
        $em->getConnection()->executeStatement('TRUNCATE TABLE registration_requests RESTART IDENTITY CASCADE');
        
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
            ->setStatus('pending');
            
        $em->persist($request);
        $em->flush();
        
        $command = $application->find('app:review-registration');
        $commandTester = new CommandTester($command);
        
        // Mock user inputs:
        // 1. Select the first choice (ID 1)
        // 2. Select "Rejeter"
        // 3. Enter rejection reason
        $commandTester->setInputs(['1', 'Rejeter', 'Motif du refus']);
        $commandTester->execute([]);
        
        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Candidature rejetée', $commandTester->getDisplay());
        
        $em->clear();
        $updatedRequest = $em->find(RegistrationRequest::class, $request->getId());
        $this->assertEquals('rejected', $updatedRequest->getStatus());
        $this->assertEquals('Motif du refus', $updatedRequest->getReviewNote());
    }
}
