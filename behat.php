<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\Config\Extension;

$suite = (new Suite('default'))
    ->withContexts(
        App\Tests\Behat\DemoContext::class,
        App\Tests\Behat\RegistrationContext::class,
        App\Tests\Behat\PasswordSetupContext::class
    );

$extension = new Extension('FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension', [
    'bootstrap' => 'tests/bootstrap.php',
]);

$profile = (new Profile('default'))
    ->withSuite($suite)
    ->withExtension($extension);

return (new Config())
    ->withProfile($profile);
