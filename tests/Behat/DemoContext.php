<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;

final class DemoContext implements Context
{
    #[When('a demo scenario is run')]
    public function aDemoScenarioIsRun(): void
    {
        // Simple action
    }

    #[Then('it should succeed')]
    public function itShouldSucceed(): void
    {
        // Simple assertion
    }
}
