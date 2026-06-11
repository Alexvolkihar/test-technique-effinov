<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\RegistrationRequest;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RegistrationRequestTest extends KernelTestCase
{
    private $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get('validator');
    }

    public function testValidRegistrationRequest(): void
    {
        $request = new RegistrationRequest();
        $request->setFirstName('John')
            ->setLastName('Doe')
            ->setAddress('123 Fight Street')
            ->setBirthDate(new \DateTime('1990-01-01'))
            ->setSocialSecurityNumber('123456789012345')
            ->setFighterNickname('TheOne')
            ->setFighterCertificationNumber('CERFA-666-12345')
            ->setPokemonStarter('Salamèche')
            ->setEmailAddress('john.doe@example.com');

        $errors = $this->validator->validate($request);
        $this->assertCount(0, $errors);
    }

    public function testInvalidStarter(): void
    {
        $request = new RegistrationRequest();
        $request->setFirstName('John')
            ->setLastName('Doe')
            ->setAddress('123 Fight Street')
            ->setBirthDate(new \DateTime('1990-01-01'))
            ->setSocialSecurityNumber('123456789012345')
            ->setFighterNickname('TheOne')
            ->setFighterCertificationNumber('CERFA-666-12345')
            ->setPokemonStarter('Pikachu') // Invalid
            ->setEmailAddress('john.doe@example.com');

        $errors = $this->validator->validate($request);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testBlankFields(): void
    {
        $request = new RegistrationRequest();

        $errors = $this->validator->validate($request);
        $this->assertGreaterThan(0, count($errors));
    }
}
