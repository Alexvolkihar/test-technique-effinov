<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MemberAccount;
use App\Exception\InvalidMessageException;
use App\Repository\MemberAccountRepository;
use App\Repository\MessageRepository;
use App\Service\MessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MessageController extends AbstractController
{
    #[Route('/messages', name: 'app_messages', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        MessageService $messageService,
        MessageRepository $messageRepository,
        MemberAccountRepository $memberRepository
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $member = $memberRepository->find($user->getId());
        if (!$member || 'active' !== $member->getStatus()) {
            return $this->redirectToRoute('app_login');
        }

        $activeContacts = $messageService->getActiveContacts($member);

        $contactId = $request->query->get('contact') ? (int)$request->query->get('contact') : null;
        $activeContact = null;

        if ($contactId) {
            $activeContact = $memberRepository->find($contactId);
            if ($activeContact && 'active' !== $activeContact->getStatus()) {
                $activeContact = null;
            }
        } elseif (!empty($activeContacts)) {
            $activeContact = reset($activeContacts);
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $body = (string)$request->request->get('body');
            
            if ($request->request->has('recipient_id')) {
                $recipientId = (int)$request->request->get('recipient_id');
                $recipient = $memberRepository->find($recipientId);
                
                try {
                    $messageService->sendMessage($member, $recipient, $body);
                    return $this->redirectToRoute('app_messages', ['contact' => $recipient->getId()]);
                } catch (InvalidMessageException $e) {
                    $error = $e->getMessage();
                }
            } else {
                try {
                    $messageService->sendMessage($member, $activeContact, $body);
                    return $this->redirectToRoute('app_messages', ['contact' => $activeContact->getId()]);
                } catch (InvalidMessageException $e) {
                    $error = $e->getMessage();
                }
            }
        }

        $messages = [];
        if ($activeContact) {
            $messages = $messageRepository->findMessagesBetween($member, $activeContact);
            foreach ($messages as $message) {
                $this->denyAccessUnlessGranted('view', $message);
            }
        }

        $possibleRecipients = $messageService->getPossibleRecipients($member, $activeContacts);

        return $this->render('portal/messages.html.twig', [
            'member' => $member,
            'activeContacts' => $activeContacts,
            'activeContact' => $activeContact,
            'messages' => $messages,
            'recipients' => $possibleRecipients,
            'error' => $error,
            'success' => null,
        ]);
    }
}
