<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\RegistrationRequest;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_registration', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager): Response
    {
        $registrationRequest = new RegistrationRequest();
        $form = $this->createForm(RegistrationType::class, $registrationRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registrationRequest->setStatus('pending');
            $entityManager->persist($registrationRequest);
            $entityManager->flush();

            $this->addFlash('success', 'Demande enregistrée avec succès. Votre candidature est en cours d\'examen.');

            return $this->redirectToRoute('app_registration');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
