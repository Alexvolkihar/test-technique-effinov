<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\RegistrationRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Adresse',
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
            ])
            ->add('socialSecurityNumber', TextType::class, [
                'label' => 'Numéro de sécurité sociale',
            ])
            ->add('fighterNickname', TextType::class, [
                'label' => 'Pseudo de combattant',
            ])
            ->add('fighterCertificationNumber', TextType::class, [
                'label' => 'Numéro d\'accréditation',
            ])
            ->add('pokemonStarter', ChoiceType::class, [
                'label' => 'Starter Pokémon',
                'choices' => [
                    'Bulbizarre' => 'Bulbizarre',
                    'Salamèche' => 'Salamèche',
                    'Carapuce' => 'Carapuce',
                ],
                'placeholder' => 'Choisissez votre starter',
            ])
            ->add('emailAddress', EmailType::class, [
                'label' => 'Adresse email',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegistrationRequest::class,
        ]);
    }
}
