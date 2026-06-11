<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\RegistrationRequest;
use App\Form\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('RegistrationForm')]
class RegistrationForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(fieldName: 'data')]
    public ?RegistrationRequest $registrationRequest = null;

    protected function instantiateForm(): FormInterface
    {
        $request = $this->registrationRequest ?? new RegistrationRequest();
        return $this->createForm(RegistrationType::class, $request);
    }
}
