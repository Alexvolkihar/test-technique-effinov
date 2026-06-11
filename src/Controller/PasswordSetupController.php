<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ValidationToken;
use App\Security\User;
use App\Service\MemberActivationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PasswordSetupController extends AbstractController
{
    #[Route('/mot-de-passe', name: 'app_password_setup', methods: ['GET', 'POST'])]
    public function setupPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        MemberActivationService $activationService,
        Security $security
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

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
            $csrfToken = $request->request->get('_token');

            if (!$this->isCsrfTokenValid('password_setup', $csrfToken)) {
                $error = 'Jeton CSRF invalide.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                try {
                    $updatedAccount = $activationService->setupPassword($validationToken, (string)$password);
                    $session->remove('password_setup_token_id');

                    $updatedUser = new User(
                        $updatedAccount->getId(),
                        $updatedAccount->getEmailAddress(),
                        $updatedAccount->getPasswordHash(),
                        $updatedAccount->getStatus(),
                        ['ROLE_USER']
                    );
                    $security->login($updatedUser, 'form_login', 'main');

                    return $this->redirectToRoute('app_portal_home');
                } catch (\InvalidArgumentException $e) {
                    $error = $e->getMessage();
                }
            }
        }

        return $this->render('auth/password_setup.html.twig', [
            'error' => $error,
        ]);
    }
}
