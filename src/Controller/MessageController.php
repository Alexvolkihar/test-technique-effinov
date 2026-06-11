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

        // 1. Fetch active contacts (ordered by latest message)
        $allMessages = $entityManager->getRepository(Message::class)->createQueryBuilder('m')
            ->where('m.sender = :user OR m.recipient = :user')
            ->setParameter('user', $member)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $activeContacts = [];
        foreach ($allMessages as $msg) {
            $otherUser = $msg->getSender()->getId() === $member->getId() ? $msg->getRecipient() : $msg->getSender();
            if ('active' !== $otherUser->getStatus()) {
                continue;
            }
            if (!isset($activeContacts[$otherUser->getId()])) {
                $activeContacts[$otherUser->getId()] = $otherUser;
            }
        }

        // 2. Select active contact
        $contactId = $request->query->get('contact') ? (int)$request->query->get('contact') : null;
        $activeContact = null;

        if ($contactId) {
            $activeContact = $entityManager->getRepository(MemberAccount::class)->find($contactId);
            if ($activeContact && 'active' !== $activeContact->getStatus()) {
                $activeContact = null;
            }
        } elseif (!empty($activeContacts)) {
            $activeContact = reset($activeContacts);
        }

        $error = null;
        $success = null;

        // 3. Handle sending messages
        if ($request->isMethod('POST')) {
            $body = trim((string)$request->request->get('body'));
            
            // Handle starting a new conversation (from dropdown selection)
            if ($request->request->has('recipient_id')) {
                $recipientId = (int)$request->request->get('recipient_id');
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

                    return $this->redirectToRoute('app_messages', ['contact' => $recipient->getId()]);
                }
            } else { // Handle sending message to selected active contact
                if (!$activeContact) {
                    $error = 'Aucune conversation active sélectionnée.';
                } elseif (empty($body)) {
                    $error = 'Le message ne peut pas être vide.';
                } else {
                    $message = new Message();
                    $message->setSender($member)
                        ->setRecipient($activeContact)
                        ->setBody($body);
                    $entityManager->persist($message);
                    $entityManager->flush();

                    return $this->redirectToRoute('app_messages', ['contact' => $activeContact->getId()]);
                }
            }
        }

        // 4. Fetch message history for selected contact
        $messages = [];
        if ($activeContact) {
            $messages = $entityManager->getRepository(Message::class)->createQueryBuilder('m')
                ->where('(m.sender = :user AND m.recipient = :contact) OR (m.sender = :contact AND m.recipient = :user)')
                ->setParameter('user', $member)
                ->setParameter('contact', $activeContact)
                ->orderBy('m.createdAt', 'ASC')
                ->getQuery()
                ->getResult();

            foreach ($messages as $message) {
                $this->denyAccessUnlessGranted('view', $message);
            }
        }

        // 5. Fetch possible recipients for starting new conversations
        $allActiveMembers = $entityManager->getRepository(MemberAccount::class)->findBy(['status' => 'active']);
        $possibleRecipients = array_filter($allActiveMembers, function($m) use ($member, $activeContacts) {
            return $m->getId() !== $member->getId() && !isset($activeContacts[$m->getId()]);
        });

        return $this->render('portal/messages.html.twig', [
            'member' => $member,
            'activeContacts' => $activeContacts,
            'activeContact' => $activeContact,
            'messages' => $messages,
            'recipients' => $possibleRecipients,
            'error' => $error,
            'success' => $success,
        ]);
    }
}
