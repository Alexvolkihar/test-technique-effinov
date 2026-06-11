<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MemberAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PortalController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_portal_home');
        }

        return $this->redirectToRoute('app_registration');
    }

    #[Route('/portail', name: 'app_portal_home', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $member = $entityManager->getRepository(MemberAccount::class)->find($user->getId());
        if (!$member) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('portal/index.html.twig', [
            'member' => $member,
        ]);
    }
}
