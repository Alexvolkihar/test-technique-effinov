<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\DomainExceptionInterface;
use App\Security\User;
use App\Service\MemberActivationService;
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
        MemberActivationService $activationService,
        Security $security
    ): Response {
        try {
            $validationToken = $activationService->verifyToken($token);
        } catch (DomainExceptionInterface $e) {
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $memberAccount = $validationToken->getMemberAccount();
        $user = new User(
            $memberAccount->getId(),
            $memberAccount->getEmailAddress(),
            $memberAccount->getPasswordHash(),
            $memberAccount->getStatus(),
            ['ROLE_USER']
        );

        $security->login($user, 'form_login', 'main');
        $request->getSession()->set('password_setup_token_id', $validationToken->getId());

        return $this->redirectToRoute('app_password_setup');
    }
}
