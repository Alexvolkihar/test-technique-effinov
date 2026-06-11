<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ValidationToken;
use App\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ValidationController extends AbstractController
{
    #[Route('/validation/{token}', name: 'app_validation_landing', methods: ['GET'])]
    public function validateToken(
        string $token,
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        $tokenHash = hash('sha256', $token);
        $validationToken = $entityManager->getRepository(ValidationToken::class)->findOneBy(['tokenHash' => $tokenHash]);

        if (null === $validationToken) {
            return new Response('Ce lien de validation est invalide.', Response::HTTP_BAD_REQUEST);
        }

        if (null !== $validationToken->getConsumedAt()) {
            return new Response('Ce lien de validation a déjà été utilisé.', Response::HTTP_BAD_REQUEST);
        }

        if ($validationToken->getExpiresAt() < new \DateTime()) {
            return new Response('Ce lien de validation a expiré.', Response::HTTP_BAD_REQUEST);
        }

        $memberAccount = $validationToken->getMemberAccount();
        if ('awaiting_password' !== $memberAccount->getStatus()) {
            return new Response('Ce compte a déjà été activé ou n\'est pas en attente de mot de passe.', Response::HTTP_BAD_REQUEST);
        }

        $user = new User(
            $memberAccount->getId(),
            $memberAccount->getEmailAddress(),
            $memberAccount->getPasswordHash(),
            $memberAccount->getStatus(),
            ['ROLE_USER']
        );

        // Programmatically login the user
        $security->login($user, 'form_login', 'main');

        // Store the token ID in session to retrieve it on password save
        $request->getSession()->set('password_setup_token_id', $validationToken->getId());

        return $this->redirectToRoute('app_password_setup');
    }
}
