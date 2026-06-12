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
            ->setSocialSecurityNumber('190011512345626')
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
            ->setSocialSecurityNumber('190011512345626')
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

    public function testInvalidSocialSecurityNumber(): void
    {
        $request = new RegistrationRequest();
        $request->setFirstName('John')
            ->setLastName('Doe')
            ->setAddress('123 Fight Street')
            ->setBirthDate(new \DateTime('1990-01-01'))
            ->setSocialSecurityNumber('12345') // Invalid (less than 15 digits)
            ->setFighterNickname('TheOne')
            ->setFighterCertificationNumber('CERFA-666-12345')
            ->setPokemonStarter('Salamèche')
            ->setEmailAddress('john.doe@example.com');

        $errors = $this->validator->validate($request);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testInvalidFighterCertificationNumber(): void
    {
        $request = new RegistrationRequest();
        $request->setFirstName('John')
            ->setLastName('Doe')
            ->setAddress('123 Fight Street')
            ->setBirthDate(new \DateTime('1990-01-01'))
            ->setSocialSecurityNumber('190011512345626')
            ->setFighterNickname('TheOne')
            ->setFighterCertificationNumber('CERFA-777-12345') // Invalid (not 666)
            ->setPokemonStarter('Salamèche')
            ->setEmailAddress('john.doe@example.com');

        $errors = $this->validator->validate($request);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testUnderageCandidate(): void
    {
        $birthDate = (new \DateTime())->modify('-10 years');
        $yy = $birthDate->format('y');
        $mm = $birthDate->format('m');
        $nir13 = '1' . $yy . $mm . '15123456';
        $key = 97 - ((int)$nir13 % 97);
        $nir = $nir13 . sprintf('%02d', $key);

        $request = new RegistrationRequest();
        $request->setFirstName('John')
            ->setLastName('Doe')
            ->setAddress('123 Fight Street')
            // Born 10 years ago (minor)
            ->setBirthDate($birthDate)
            ->setSocialSecurityNumber($nir)
            ->setFighterNickname('TheOne')
            ->setFighterCertificationNumber('CERFA-666-12345')
            ->setPokemonStarter('Salamèche')
            ->setEmailAddress('john.doe@example.com');

        $errors = $this->validator->validate($request);
        $this->assertGreaterThan(0, count($errors));
    }
}

