<?php

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use App\Repository\ContactMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactMessageController extends AbstractController
{
    #[Route('/admin/contact-messages', name: 'app_admin_contact_message_index', methods: ['GET'])]
    public function index(ContactMessageRepository $contactMessageRepository): Response
    {
        $messages = $contactMessageRepository->findBy([], [
            'createdAt' => 'DESC',
        ]);

        return $this->render('pages/admin/contact_message/index.html.twig', [
            'messages' => $messages,
        ]);
    }

    #[Route('/admin/contact-messages/{id}', name: 'app_admin_contact_message_show', methods: ['GET'])]
    public function show(ContactMessage $contactMessage, EntityManagerInterface $entityManager): Response
    {
        if (!$contactMessage->isRead()) {
            $contactMessage->setIsRead(true);
            $entityManager->flush();
        }

        return $this->render('pages/admin/contact_message/show.html.twig', [
            'message' => $contactMessage,
        ]);
    }
}
