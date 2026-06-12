<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Entity\RegistrationRequest;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ReviewRegistrationCommandTest extends KernelTestCase
{
    public function testExecuteApprove(): void
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
}
