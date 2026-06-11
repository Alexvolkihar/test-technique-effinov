<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MemberAccount;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MessageController extends AbstractController
{
    #[Route('/messages', name: 'app_messages', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $member = $entityManager->getRepository(MemberAccount::class)->find($user->getId());
        if (!$member || 'active' !== $member->getStatus()) {
            return $this->redirectToRoute('app_login');
        }

        $error = null;
        $success = null;

        if ($request->isMethod('POST')) {
            $recipientId = (int)$request->request->get('recipient_id');
            $body = trim((string)$request->request->get('body'));

            $recipient = $entityManager->getRepository(MemberAccount::class)->find($recipientId);

            if (!$recipient || 'active' !== $recipient->getStatus()) {
                $error = 'Le destinataire choisi est invalide ou inactif.';
            } elseif (empty($body)) {
                $error = 'Le message ne peut pas être vide.';
            } else {
                $message = new Message();
                $message->setSender($member)
                    ->setRecipient($recipient)
                    ->setBody($body);

                $entityManager->persist($message);
                $entityManager->flush();

                $success = 'Message envoyé avec succès.';
                
                // Clear POST request data to avoid form resubmission
                return $this->redirectToRoute('app_messages');
            }
        }

        // Fetch all active members for recipient choices (excluding current member)
        $allActiveMembers = $entityManager->getRepository(MemberAccount::class)->findBy(['status' => 'active']);
        $possibleRecipients = array_filter($allActiveMembers, fn($m) => $m->getId() !== $member->getId());

        // Fetch messages involving current member
        $messages = $entityManager->getRepository(Message::class)->createQueryBuilder('m')
            ->where('m.sender = :user OR m.recipient = :user')
            ->setParameter('user', $member)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        // Enforce voter check for safety
        foreach ($messages as $message) {
            $this->denyAccessUnlessGranted('view', $message);
        }

        return $this->render('portal/messages.html.twig', [
            'member' => $member,
            'messages' => $messages,
            'recipients' => $possibleRecipients,
            'error' => $error,
            'success' => $success,
        ]);
    }
}
