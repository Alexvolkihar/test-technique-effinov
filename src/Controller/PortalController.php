<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PortalController extends AbstractController
{
    #[Route('/portail', name: 'app_portal_home', methods: ['GET'])]
    public function index(): Response
    {
        return new Response('Bienvenue sur le portail secret.');
    }
}
