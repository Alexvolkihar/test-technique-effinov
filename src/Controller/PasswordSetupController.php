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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class PasswordSetupController extends AbstractController
{
    #[Route('/mot-de-passe', name: 'app_password_setup', methods: ['GET', 'POST'])]
    public function setupPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        Security $security
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // If the user is already active, redirect to portal
        if ('active' === $user->getStatus()) {
            return $this->redirectToRoute('app_portal_home');
        }

        $session = $request->getSession();
        $tokenId = $session->get('password_setup_token_id');

        if (null === $tokenId) {
            return new Response('Accès refusé. Veuillez utiliser le lien reçu par email.', Response::HTTP_ACCESS_DENIED);
        }

        $validationToken = $entityManager->getRepository(ValidationToken::class)->find($tokenId);
        if (!$validationToken || null !== $validationToken->getConsumedAt() || $validationToken->getExpiresAt() < new \DateTime()) {
            return new Response('Lien de validation expiré ou invalide.', Response::HTTP_BAD_REQUEST);
        }

        $memberAccount = $validationToken->getMemberAccount();
        if ($memberAccount->getId() !== $user->getId()) {
            return new Response('Accès refusé pour ce compte.', Response::HTTP_ACCESS_DENIED);
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            if (!$password || strlen($password) < 8) {
                $error = 'Le mot de passe doit contenir au moins 8 caractères.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                // Update member account
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $memberAccount->setPasswordHash($hashedPassword);
                $memberAccount->setStatus('active');
                $memberAccount->setPasswordSetAt(new \DateTime());

                // Consume token
                $validationToken->setConsumedAt(new \DateTime());

                $entityManager->flush();

                // Clear token from session
                $session->remove('password_setup_token_id');

                // Refresh user session state
                $updatedUser = new User(
                    $memberAccount->getId(),
                    $memberAccount->getEmailAddress(),
                    $memberAccount->getPasswordHash(),
                    $memberAccount->getStatus(),
                    ['ROLE_USER']
                );
                $security->login($updatedUser, 'form_login', 'main');

                return $this->redirectToRoute('app_portal_home');
            }
        }

        return $this->render('auth/password_setup.html.twig', [
            'error' => $error,
        ]);
    }
}
